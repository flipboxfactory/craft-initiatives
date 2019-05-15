<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/initiatives/license
 * @link       https://www.flipboxfactory.com/software/initiatives/
 */

namespace flipbox\craft\initiatives\actions\users;

use craft\elements\User;
use flipbox\craft\initiatives\elements\Initiative;
use flipbox\craft\initiatives\records\UserAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class AssociateUserToInitiative extends AbstractUserAssociation
{
    /**
     * @inheritdoc
     * @throws \Throwable
     */
    protected function performAction(User $user, Initiative $initiative, int $sortOrder = null): bool
    {
        if (!$record = UserAssociation::findOne([
            'user' => $user,
            'element' => $initiative
        ])) {
            $record = new UserAssociation([
                'user' => $user,
                'element' => $initiative
            ]);
        }
        return $record->save();
    }
}
