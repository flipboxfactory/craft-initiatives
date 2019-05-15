<?php

namespace flipbox\craft\initiatives\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use craft\elements\db\UserQuery;
use craft\helpers\ArrayHelper;
use flipbox\craft\initiatives\elements\Initiative;
use flipbox\craft\initiatives\records\UserAssociation;
use yii\base\Exception;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class RemoveTargets extends ElementAction
{
    /**
     * @var string|int|array|Initiative
     */
    public $initiative;

    /**
     * @return array
     */
    public function settingsAttributes(): array
    {
        return array_merge(
            parent::settingsAttributes(),
            [
                'initiative'
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public static function isDestructive(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return 'Remove';
    }

    /**
     * @inheritdoc
     * @param UserQuery $query
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        if (empty($this->initiative)) {
            throw new Exception("Initiative does not exist with the identifier '{$this->initiative}'");
        }

        $initiative = Initiative::getOne($this->initiative);

        // Prep for dissociation
        $query->setCachedResult(
            $query->all()
        );

        $success = true;
        foreach ($query->all() as $user) {
            if (null !== ($record = UserAssociation::find()
                    ->userId($user->getId() ?: false)
                    ->elementId($initiative->getId() ?: false)
                    ->one()
                )
            ) {
                $record->delete();
            }
        }

        if (!$success) {
            $this->setMessage(
                Craft::t(
                    'initiatives',
                    $this->assembleFailMessage($query)
                )
            );

            return false;
        }

        $this->setMessage($this->assembleSuccessMessage($query));
        return true;
    }

    /**
     * @param ElementQueryInterface|UserQuery $query
     * @return string
     */
    private function assembleFailMessage(ElementQueryInterface $query): string
    {
        $message = 'Failed to remove user: ';

        $users = $query->getCachedResult();
        $badEmails = ArrayHelper::index($users, 'email');

        $message .= implode(", ", $badEmails);

        return Craft::t('initiatives', $message);
    }

    /**
     * @param ElementQueryInterface|UserQuery $query
     * @return string
     */
    private function assembleSuccessMessage(ElementQueryInterface $query): string
    {
        $message = 'User';

        if ($query->count() != 1) {
            $message = $query->count() . ' ' . $message . 's';
        }

        $message .= ' removed.';

        return Craft::t('initiatives', $message);
    }
}
