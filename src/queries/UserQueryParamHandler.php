<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/initiative/license
 * @link       https://www.flipboxfactory.com/software/initiative/
 */

namespace flipbox\craft\initiatives\queries;

use craft\elements\db\UserQuery;
use craft\helpers\Db;
use flipbox\craft\ember\queries\ElementAttributeTrait;
use flipbox\craft\initiatives\behaviors\InitiativeAttributesToUserQueryBehavior;
use flipbox\craft\initiatives\records\UserAssociation as InitiativeUsersRecord;
use yii\base\BaseObject;
use yii\db\Query;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UserQueryParamHandler extends BaseObject
{
    use ElementAttributeTrait;

    /**
     * @var InitiativeAttributesToUserQueryBehavior
     */
    private $owner;

    /**
     * The user category(s) that the resulting organizations’ users must be in.
     *
     * @var string|string[]|null
     */
    public $targetStatus;

    /**
     * @param string|string[]|null $value
     * @return UserQuery
     */
    public function setTargetStatus($value): UserQuery
    {
        $this->targetStatus = $value;
        return $this->owner->owner;
    }

    /**
     * @inheritdoc
     * @param InitiativeAttributesToUserQueryBehavior $owner
     */
    public function __construct(InitiativeAttributesToUserQueryBehavior $owner, array $config = [])
    {
        $this->owner = $owner;
        parent::__construct($config);
    }

    /**
     * @param UserQuery $query
     */
    public function applyParams(UserQuery $query)
    {
        if ($query->subQuery === null ||
            (
                $this->element === null &&
                $this->targetStatus === null
            )
        ) {
            return;
        }

        $alias = $this->joinInitiativeUserTable($query->subQuery);

        $this->applyInitiativeParam(
            $query->subQuery,
            $this->element,
            $alias
        );

        $this->applyTargetStatusParam(
            $query->subQuery,
            $this->targetStatus,
            $alias
        );
    }

    /************************************************************
     * JOIN TABLES
     ************************************************************/

    /**
     * @inheritdoc
     */
    protected function joinInitiativeUserTable(Query $query): string
    {
        $alias = InitiativeUsersRecord::tableAlias();

        $query->leftJoin(
            InitiativeUsersRecord::tableName() . ' ' . $alias,
            '[[' . $alias . '.userId]] = [[elements.id]]'
        );

        return $alias;
    }

    /************************************************************
     * ORGANIZATION
     ************************************************************/

    /**
     * @param Query $query
     * @param $initiative
     * @param string $alias
     */
    protected function applyInitiativeParam(Query $query, $initiative, string $alias)
    {
        if (empty($initiative)) {
            return;
        }

        $query->andWhere(
            Db::parseParam($alias . '.elementId', $this->parseElementValue($initiative))
        );
    }

    /************************************************************
     * USER CATEGORY
     ************************************************************/

    /**
     * @param Query $query
     * @param $value
     * @param string $alias
     */
    protected function applyTargetStatusParam(Query $query, $value, string $alias)
    {
        if (empty($value)) {
            return;
        }

        $query->andWhere(
            Db::parseParam($alias . '.status', $value)
        );
    }
}
