<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\craft\initiatives\queries;

use craft\helpers\Db;
use flipbox\craft\ember\queries\AuditAttributesTrait;
use flipbox\craft\ember\queries\CacheableActiveQuery;
use flipbox\craft\ember\queries\ElementAttributeTrait;
use flipbox\craft\ember\queries\UserAttributeTrait;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class InitiativeActiveQuery extends CacheableActiveQuery
{
    use AuditAttributesTrait,
        UserAttributeTrait,
        ElementAttributeTrait;

    /**
     * @var string|string[]|false|null The status(es). Prefix with "not " to exclude them.
     */
    public $status;

    /**
     * @inheritdoc
     * return static
     */
    public function status($value)
    {
        $this->status = $value;
        return $this;
    }

    /**
     * @inheritdoc
     * return static
     */
    public function setStatus($value)
    {
        return $this->status($value);
    }

    /**
     * @inheritdoc
     */
    public function prepare($builder)
    {
        $this->applyAuditAttributeConditions();
        $this->applyConditions();
        return parent::prepare($builder);
    }

    /**
     * Apply conditions
     */
    protected function applyConditions()
    {
        if ($this->status !== null) {
            $this->andWhere(Db::parseParam('status', $this->status));
        }

        if ($this->user !== null) {
            $this->andWhere(Db::parseParam('userId', $this->parseUserValue($this->user)));
        }

        if ($this->element !== null) {
            $this->andWhere(Db::parseParam('elementId', $this->parseElementValue($this->element)));
        }
    }
}
