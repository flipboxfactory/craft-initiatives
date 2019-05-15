<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/initiatives/blob/master/LICENSE.md
 * @link       https://github.com/flipboxfactory/initiatives
 */

namespace flipbox\craft\initiatives\initiatives;

use craft\elements\db\UserQuery;
use craft\elements\User;
use flipbox\craft\initiatives\elements\Initiative;

interface InitiativeSettingsInterface
{
    /**
     * @return string
     */
    public function getTemplate(): string;

    /**
     * @param Initiative $initiative
     * @return array|null
     */
    public function getRoute(Initiative $initiative);

    /**
     * @param Initiative $initiative
     * @return UserQuery
     */
    public function getTargetQuery(Initiative $initiative): UserQuery;

    /**
     * @param Initiative $initiative
     * @param User $user
     * @return ActionInterface
     */
    public function createAction(Initiative $initiative, User $user): ActionInterface;

    /**
     * @param Initiative $initiative
     * @return bool
     */
    public function save(Initiative $initiative): bool;
}
