<?php

namespace flipbox\craft\initiatives\behaviors;

use craft\elements\db\UserQuery;
use craft\helpers\ArrayHelper;
use flipbox\craft\initiatives\elements\Initiative;
use flipbox\craft\initiatives\queries\UserQueryParamHandler;
use yii\base\Behavior;
use yii\base\Exception;

/**
 *
 * @property UserQuery $owner
 */
class InitiativeAttributesToUserQueryBehavior extends Behavior
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->handler = new UserQueryParamHandler($this);

        if ($this->owner instanceof UserQuery) {
            throw new Exception(sprintf(
                "Behavior owner must be an instance of '%s', '%s' given" .
                (string)UserQuery::class,
                (string)get_class($this->owner)
            ));
        }
    }

    /**
     * @var UserQueryParamHandler
     */
    private $handler;

    /**
     * @param UserQuery $query
     */
    public function applyInitiativeParams(UserQuery $query)
    {
        $this->handler->applyParams($query);
    }

    /**
     * @return UserQueryParamHandler
     */
    public function getInitiative(): UserQueryParamHandler
    {
        return $this->handler;
    }

    /**
     * @param string|string[]|int|int[]|Initiative|Initiative[]|null $value
     * @return UserQuery
     */
    public function setInitiative($value): UserQuery
    {
        if (is_array($value)) {
            $this->findSubNodes($value);

            // If we removed everything, we're all done here
            if (empty($value)) {
                return $this->owner;
            }
        }

        $this->handler->setElement($value);
        return $this->owner;
    }

    /**
     * @param string|string[]|int|int[]|Initiative|Initiative[]|null $value
     * @return UserQuery
     */
    public function initiative($value): UserQuery
    {
        return $this->setInitiative($value);
    }

    /**
     * @param string|string[]|int|int[]|Initiative|Initiative[]|null $value
     * @return UserQuery
     */
    public function setInitiativeId($value): UserQuery
    {
        return $this->setInitiative($value);
    }

    /**
     * @param string|string[]|int|int[]|Initiative|Initiative[]|null $value
     * @return UserQuery
     */
    public function initiativeId($value): UserQuery
    {
        return $this->setInitiative($value);
    }

    /**
     * Extract the sub nodes (userCategory and type) from a criteria array
     *
     * @param array $value
     */
    private function findSubNodes(array &$value)
    {
        if (null !== ($subValue = ArrayHelper::remove($value, 'targetStatus'))) {
            $this->handler->setTargetStatus($subValue);
        }
    }
}
