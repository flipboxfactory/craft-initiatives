<?php

namespace flipbox\craft\initiatives\queue;

use Craft;
use craft\elements\db\UserQuery;
use craft\elements\User;
use craft\queue\BaseJob;
use flipbox\craft\initiatives\elements\Initiative;
use flipbox\craft\initiatives\records\UserAssociation;

/**
 * Class AddTargetsToInitiativeJob
 * @package flipbox\craft\initiatives\queue\jobs
 */
class AddTargetsToInitiativeJob extends BaseJob
{
    /**
     * @var array
     */
    public $userIds;

    /**
     * @var
     */
    public $initiativeId;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        foreach ($this->getUserQuery() as $user) {
            if (null === ($record = UserAssociation::find()
                    ->userId($user->getId() ?: false)
                    ->elementId($this->initiativeId)
                    ->one()
                )
            ) {

                $record = (new UserAssociation)
                    ->setUserId($user->getId() ?: false)
                    ->setElementId($this->initiativeId);
            }

            $record->save();
        }
    }

    /**
     * @return UserQuery
     */
    protected function getUserQuery(): UserQuery
    {
        return User::find()
            ->status(null)
            ->id($this->getUserIds());
    }

    /**
     * @return array
     */
    protected function getUserIds(): array
    {
        $userIds = $this->userIds;

        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        return $userIds;
    }

    /**
     * @return Initiative
     * @throws \craft\errors\ElementNotFoundException
     */
    protected function getInitiative(): Initiative
    {
        return Initiative::getOne($this->initiativeId);
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('initiatives', 'Associating targets to initiative: {initiative}', [
            'initiative' => $this->initiativeId
        ]);
    }
}
