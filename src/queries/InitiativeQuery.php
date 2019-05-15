<?php

namespace flipbox\craft\initiatives\queries;

use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use flipbox\craft\ember\queries\UserAttributeTrait;
use flipbox\craft\initiatives\Initiatives;
use flipbox\craft\initiatives\records\Initiative;
use flipbox\craft\initiatives\records\UserAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class InitiativeQuery extends ElementQuery
{
    use UserAttributeTrait;

    /**
     * @var string|string[]|null
     */
    public $targetStatus;

    /**
     * @param $value
     * @return InitiativeQuery
     */
    public function target($value)
    {
        return $this->user($value);
    }

    /**
     * @param $value
     * @return InitiativeQuery
     */
    public function setTarget($value)
    {
        return $this->setUser($value);
    }

    /**
     * @param $value
     * @return $this|static
     */
    public function targetStatus($value)
    {
        $this->targetStatus = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $alias = Initiative::tableAlias();
        $this->joinElementTable($alias);

        $this->query->select([
            $alias . '.settings'
        ]);

        $this->prepareRelationsParams();

        $this->withStructure(true)
            ->structureId(Initiatives::getInstance()->getSettings()->structureId);

        return parent::beforePrepare();
    }

    /**
     * Prepares relation params
     */
    protected function prepareRelationsParams()
    {
        if ($this->user === null && $this->targetStatus === null) {
            return;
        }

        $alias = $this->joinInitiativeUserTable();

        $this->applyUserParam($alias);
        $this->applyTargetStatusParam($alias);
    }

    /************************************************************
     * JOIN TABLES
     ************************************************************/

    /**
     * @return string
     */
    protected function joinInitiativeUserTable(): string
    {
        $alias = UserAssociation::tableAlias();

        $this->subQuery->leftJoin(
            UserAssociation::tableName() . ' ' . $alias,
            '[[' . $alias . '.elementId]] = [[elements.id]]'
        );

        return $alias;
    }

    /************************************************************
     * USER
     ************************************************************/

    /**
     * @param string $alias
     *
     * @return void
     */
    protected function applyUserParam(string $alias)
    {
        if ($this->user === null) {
            return;
        }

        $this->subQuery->andWhere(
            Db::parseParam($alias . '.userId', $this->parseUserValue($this->user))
        );
        $this->subQuery->distinct(true);
    }

    /************************************************************
     * USER
     ************************************************************/

    /**
     * @param string $alias
     *
     * @return void
     */
    protected function applyTargetStatusParam(string $alias)
    {
        if ($this->targetStatus === null) {
            return;
        }

        $this->subQuery->andWhere(
            Db::parseParam($alias . '.status', $this->targetStatus)
        );
    }
}