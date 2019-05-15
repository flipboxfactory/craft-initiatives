<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/initiatives/blob/master/LICENSE.md
 * @link       https://github.com/flipboxfactory/initiatives
 */

namespace flipbox\craft\initiatives\models;

use craft\base\Model;

class Settings extends Model
{
    /**
     * @var int
     */
    public $structureId;

    /**
     * @var string|null URI format
     */
    public $uriFormat = 'initiative/{slug}';
}
