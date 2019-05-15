<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/initiatives/blob/master/LICENSE.md
 * @link       https://github.com/flipboxfactory/initiatives
 */

namespace flipbox\craft\initiatives\initiatives;

use craft\elements\User;
use flipbox\craft\initiatives\elements\Initiative;

/**
 * Interface InitiativeInterface
 * @package flipbox\craft\initiatives\initiatives
 */
interface ActionInterface
{
    /**
     * @return Initiative
     */
    public function getInitiative(): Initiative;

    /**
     * @return User
     */
    public function getUser(): User;

    /**
     * @return bool
     */
    public function complete(): bool;

    /**
     * @param null $attributeNames
     * @param bool $clearErrors
     * @return mixed
     */
    public function validate($attributeNames = null, $clearErrors = true);

    /**
     * @param string $paramNamespace
     * @return mixed
     */
    public function populateFromRequest(string $paramNamespace);

    /**
     * @return string
     */
    public function __toString(): string;

}