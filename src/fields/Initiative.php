<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\craft\initiatives\fields;

use Craft;
use craft\fields\BaseRelationField;
use flipbox\craft\initiatives\elements\Initiative as InitiativeElement;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Initiative extends BaseRelationField
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('initiatives', 'Initiatives');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return InitiativeElement::class;
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('initiatives', 'Add an initiative');
    }
}
