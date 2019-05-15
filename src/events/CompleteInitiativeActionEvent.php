<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/initiatives/license
 * @link       https://www.flipboxfactory.com/software/initiatives/
 */

namespace flipbox\craft\initiatives\events;

use craft\events\CancelableEvent;
use flipbox\craft\initiatives\initiatives\ActionInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class CompleteInitiativeActionEvent extends CancelableEvent
{
    /**
     * @var ActionInterface
     */
    public $action;
}
