<?php

namespace flipbox\craft\initiatives\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\ArrayHelper;
use flipbox\craft\initiatives\elements\Initiative;
use flipbox\craft\initiatives\queue\AddTargetsToInitiativeJob;

/**
 * Class AbstractInitiativeMigration
 * @package flipbox\craft\initiatives\migrations
 */
abstract class AbstractInitiativeMigration extends Migration
{
    /**
     * @var int
     */
    public $batchSize = 500;

    /**
     * @return Initiative
     */
    abstract protected function getInitiative(): Initiative;

    /**
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $initiative = $this->getInitiative();

        if (!Craft::$app->getElements()->saveElement($initiative)) {
            return false;
        }

        if ($initiative->getSettings()->save($initiative)) {
            $this->addTargetJobs($initiative);
            return true;
        }

        return false;
    }

    /**
     * @param Initiative $initiative
     */
    protected function addTargetJobs(Initiative $initiative)
    {
        $batch = $initiative->userQuery()
            ->asArray(true)
            ->select(['elements.id'])
            ->batch($this->batchSize);

        foreach ($batch as $rows) {
            $userIds = ArrayHelper::getColumn($rows, 'id');

            Craft::$app->getQueue()->push(new AddTargetsToInitiativeJob([
                'initiativeId' => $initiative->getId(),
                'userIds' => $userIds,
            ]));
        }
    }
}
