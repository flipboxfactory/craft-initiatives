<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/initiatives/blob/master/LICENSE.md
 * @link       https://github.com/flipboxfactory/initiatives
 */

namespace flipbox\craft\initiatives\records;

use Craft;
use flipbox\craft\ember\records\ActiveRecordWithId;
use flipbox\craft\initiatives\queries\InitiativeActiveQuery;

/**
 * @property array|string|null $settings
 */
class Initiative extends ActiveRecordWithId
{

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'initiatives';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'settings'
                    ],
                    'safe',
                    'on' => [
                        self::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return InitiativeActiveQuery
     */
    public static function find(): InitiativeActiveQuery
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        return Craft::createObject(
            InitiativeActiveQuery::class,
            [
                get_called_class()
            ]
        );
    }
}
