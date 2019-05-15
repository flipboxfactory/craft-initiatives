<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/initiatives/blob/master/LICENSE.md
 * @link       https://github.com/flipboxfactory/initiatives
 */

namespace flipbox\craft\initiatives\records;

use Craft;
use flipbox\craft\ember\records\ActiveRecord;
use flipbox\craft\ember\records\ElementAttributeTrait;
use flipbox\craft\ember\records\SortableTrait;
use flipbox\craft\ember\records\UserAttributeTrait;
use flipbox\craft\initiatives\queries\UserAssociationQuery;

/**
 * Class UserAssociation
 * @package flipbox\craft\initiatives\records
 *
 * @property int $initiativeId
 * @property int $sortOrder
 * @property Initiative $initiative
 *
 * @property string $status
 */
class UserAssociation extends ActiveRecord
{
    use UserAttributeTrait,
        ElementAttributeTrait,
        SortableTrait;


    /**
     * The table alias
     */
    const TABLE_ALIAS = 'initiative_targets';

    /**
     * The active status
     */
    const STATUS_ACTIVE = 'active';

    /**
     * The error status
     */
    const STATUS_ERROR = 'error';

    /**
     * The completed status
     */
    const STATUS_COMPLETE = 'complete';

    /**
     * @inheritdoc
     */
    const TARGET_ATTRIBUTE = 'elementId';

    /**
     * @inheritdoc
     */
    const SOURCE_ATTRIBUTE = 'userId';

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = ['elementId', 'userId'];

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return UserAssociationQuery
     */
    public static function find(): UserAssociationQuery
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        return Craft::createObject(UserAssociationQuery::class, [get_called_class()]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->userRules(),
            $this->elementRules()
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->ensureSortOrder(
            [
                'elementId' => $this->elementId
            ]
        );

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     * @throws \yii\db\Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->autoReOrder(
            'userId',
            [
                'elementId' => $this->elementId
            ]
        );

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     * @throws \yii\db\Exception
     */
    public function afterDelete()
    {
        $this->sequentialOrder(
            'userId',
            [
                'elementId' => $this->elementId
            ]
        );

        parent::afterDelete();
    }
}
