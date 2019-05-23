<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/initiatives/blob/master/LICENSE.md
 * @link       https://github.com/flipboxfactory/initiatives
 */

namespace flipbox\craft\initiatives\initiatives;

use Craft;
use craft\elements\User;
use craft\helpers\Json;
use flipbox\craft\initiatives\elements\Initiative;
use flipbox\craft\initiatives\events\CompleteInitiativeActionEvent;
use flipbox\craft\initiatives\records\UserAssociation;
use yii\base\Model;

/**
 * Interface InitiativeInterface
 * @package flipbox\craft\initiatives\initiatives
 */
class InitiativeAction extends Model implements ActionInterface
{
    /**
     * @event CompleteInitiativeActionEvent an event. You may set
     * [[CompleteInitiativeActionEvent::isValid]] to be false to prevent completion.
     */
    const EVENT_BEFORE_COMPLETE = 'beforeComplete';

    /**
     * @event CompleteInitiativeActionEvent an event. You may set
     * [[CompleteInitiativeActionEvent::isValid]] to be false to prevent completion.
     */
    const EVENT_AFTER_COMPLETE = 'beforeComplete';

    /**
     * @var User
     */
    private $user;

    /**
     * @var Initiative
     */
    private $initiative;

    /**
     * @param Initiative $initiative
     * @param User $user
     * @param array $config
     */
    public function __construct(Initiative $initiative, User $user, $config = [])
    {
        $this->user = $user;
        $this->initiative = $initiative;
        parent::__construct($config);
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return Initiative
     */
    public function getInitiative(): Initiative
    {
        return $this->initiative;
    }

    /**
     * @inheritdoc
     */
    public function beforeComplete(CompleteInitiativeActionEvent $event): bool
    {
        $this->trigger(self::EVENT_BEFORE_COMPLETE, $event);
        return $event->isValid;
    }

    /**
     * @inheritdoc
     */
    public function afterComplete(CompleteInitiativeActionEvent $event): bool
    {
        $this->trigger(self::EVENT_AFTER_COMPLETE, $event);
        return $event->isValid;
    }

    /**
     * @inheritdoc
     */
    public function complete(bool $runValidation = true): bool
    {
        $event = new CompleteInitiativeActionEvent([
            'action' => $this
        ]);

        if (!$this->beforeComplete($event)) {
            return false;
        }

        // Validate
        if ($runValidation && !$this->validate()) {
            Craft::info(
                'Element not saved due to validation error: ' . print_r($action->getErrors(), true),
                __METHOD__
            );

            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            /** @var UserAssociation $record */
            $record = UserAssociation::getOne([
                'userId' => $this->getUser()->getId(),
                'elementId' => $this->getInitiative()->getId(),
            ]);

            if (!$this->completeInternal()) {
                $transaction->rollBack();

                $record->status = $record::STATUS_ERROR;
                $record->save(true, ['status', 'dateUpdated']);
                return false;
            }

            $record->status = $record::STATUS_COMPLETE;

            if (!$record->save(true, ['status', 'dateUpdated']) ||
                !$this->afterComplete($event)
            ) {
                $transaction->rollBack();
                return false;
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $record->status = $record::STATUS_ERROR;
            $record->save(true, ['status', 'dateUpdated']);

            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function completeInternal(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function populateFromRequest(string $paramNamespace)
    {
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return Json::encode([
            'initiative' => $this->getInitiative()->getId(),
            'user' => $this->getUser()->getId()
        ]);
    }
}
