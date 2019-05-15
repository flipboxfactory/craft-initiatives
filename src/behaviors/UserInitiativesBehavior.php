<?php

namespace flipbox\craft\initiatives\behaviors;

use Craft;
use craft\elements\User;
use craft\events\ModelEvent;
use craft\helpers\ArrayHelper;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\craft\initiatives\elements\Initiative;
use flipbox\craft\initiatives\Initiatives as InitiativePlugin;
use flipbox\craft\initiatives\queries\InitiativeQuery;
use flipbox\craft\initiatives\records\UserAssociation;
use flipbox\craft\initiatives\validators\InitiativesValidator;
use yii\base\Behavior;
use yii\base\Event;
use yii\base\Exception;
use yii\helpers\Json;

/**
 * Class UserInitiativesBehavior
 * @package flipbox\craft\initiatives\behaviors
 *
 * @property User $owner;
 */
class UserInitiativesBehavior extends Behavior
{
    /**
     * @var InitiativeQuery|null
     */
    private $initiatives;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Validate initiatives
        Event::on(
            User::class,
            User::EVENT_AFTER_VALIDATE,
            function (Event $e) {
                /** @var User $user */
                $user = $e->sender;
                $this->validate($user);
            }
        );

        // Associate
        Event::on(
            User::class,
            User::EVENT_AFTER_SAVE,
            function (ModelEvent $e) {
                /** @var User $user */
                $user = $e->sender;
                $this->save($user);
            }
        );

        // Dissociate
        Event::on(
            User::class,
            User::EVENT_AFTER_DELETE,
            function (Event $e) {
                /** @var User $user */
                $user = $e->sender;
                $this->delete($user);
            }
        );
    }

    /**
     * @param User $user
     * @return void
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    private function delete(User $user)
    {
        $this->dissociateInitiatives($user);
    }

    /**
     * @param User|self $user
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function dissociateInitiatives(User $user)
    {
        foreach ($user->getInitiatives()->all() as $initiative) {

            if (null !== ($record = UserAssociation::findOne([
                    'userId' => $user->getId(),
                    'elementId' => $initiative->getId()
                ]))) {
                $record->delete();
            }
        }
    }

    /**
     * @param User|self $user
     * @throws Exception
     * @throws \Exception
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     */
    private function save(User $user)
    {
        $this->saveInitiatives($user);
        $this->associateInitiatives($user);
    }

    /**
     * @param User|self $user
     * @throws \Exception
     */
    private function associateInitiatives(User $user)
    {
        foreach ($user->getInitiatives()->all() as $initiative) {
            if (null === ($record = UserAssociation::findOne([
                    'userId' => $user->getId(),
                    'elementId' => $initiative->getId()
                ]))) {
                $record = new UserAssociation([
                    'userId' => $user->getId(),
                    'elementId' => $initiative->getId()
                ]);
            }

            $record->save();
        }
    }

    /**
     * @param User|self $user
     * @throws Exception
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     */
    private function saveInitiatives(User $user)
    {
        // Check cache for explicitly set (and possibly not saved) initiatives
        if (null !== ($initiatives = $user->getInitiatives()->getCachedResult())) {

            /** @var Initiative $initiative */
            foreach ($initiatives as $initiative) {
                if (!$initiative->id) {
                    if (!Craft::$app->getElements()->saveElement($initiative)) {
                        $user->addError(
                            'initiatives',
                            Craft::t('initiatives', 'Unable to save initiative.')
                        );

                        throw new Exception('Unable to save initiative.');
                    }
                }
            }
        }
    }

    /**
     * @param User|self $user
     * @return void
     */
    private function validate(User $user)
    {
        $error = null;

        if (!(new InitiativesValidator())->validate($user->getInitiatives(), $error)) {
            $user->addError('initiatives', $error);
        }
    }

    /**
     * @return InitiativeQuery
     */
    private function createQuery(): InitiativeQuery
    {
        return Initiative::find()
            ->user($this->owner);
    }

    /**
     * Get a query with associated initiatives
     *
     * @param array $criteria
     * @return InitiativeQuery
     */
    public function getInitiatives($criteria = []): InitiativeQuery
    {
        if (null === $this->initiatives) {
            $this->initiatives = $this->createQuery();
        }

        if (!empty($criteria)) {
            QueryHelper::configure(
                $this->initiatives,
                $criteria
            );
        }

        return $this->initiatives;
    }

    /**
     * Associate users to an initiative
     *
     * @param $initiatives
     * @return $this
     */
    public function setInitiatives($initiatives)
    {
        if ($initiatives instanceof InitiativeQuery) {
            $this->initiatives = $initiatives;
            return $this;
        }

        // Reset the query
        $this->initiatives = $this->createQuery();
        $this->initiatives->setCachedResult([]);
        $this->addInitiatives($initiatives);
        return $this;
    }

    /**
     * Associate an array of users to an initiative
     *
     * @param $initiatives
     * @return $this
     */
    protected function addInitiatives(array $initiatives)
    {
        // In case a config is directly passed
        if (ArrayHelper::isAssociative($initiatives)) {
            $initiatives = [$initiatives];
        }

        foreach ($initiatives as $key => $initiative) {
            if (!$initiative = $this->resolve($initiative)) {
                InitiativePlugin::info(sprintf(
                    "Unable to resolve initiative: %s",
                    (string)Json::encode($initiative)
                ));
                continue;
            }

            $this->addInitiative($initiative);
        }

        return $this;
    }

    /**
     * @param $initiative
     * @return Initiative|null
     * @throws \craft\errors\ElementNotFoundException
     */
    protected function resolve($initiative)
    {
        if (is_array($initiative) &&
            null !== ($id = ArrayHelper::getValue($initiative, 'id'))
        ) {
            return Initiative::getOne($id);
        }

        if ($object = Initiative::findOne($initiative)) {
            return $object;
        }

        return new Initiative($initiative);
    }


    /**
     * Associate a user to an initiative
     *
     * @param Initiative $initiative
     * @param bool $addToInitiative
     * @return $this
     */
    public function addInitiative(Initiative $initiative, bool $addToInitiative = true)
    {
        // Current associated initiatives
        $allInitiatives = $this->getInitiatives()->all();
        $allInitiatives[] = $initiative;

        $this->getInitiatives()->setCachedResult($allInitiatives);

        // Add user to initiative as well?
        if ($addToInitiative && $initiative->id !== null) {
            $user = $this->owner;
            if ($user instanceof User) {
                $initiative->addTarget($user);
            };
        }

        return $this;
    }
}
