<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/initiatives/blob/master/LICENSE.md
 * @link       https://github.com/flipboxfactory/initiatives
 */

namespace flipbox\craft\initiatives\steps;

use craft\elements\User;
use flipbox\craft\initiatives\initiatives\MultiStepInitiativeInterface;

interface StepInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @param User $user
     * @return string
     */
    public function renderHtml(User $user): string;

    /**
     * @return MultiStepInitiativeInterface
     */
    public function getInitiative(): MultiStepInitiativeInterface;

    /**
     * @param User $user
     * @param bool $runValidation
     * @return bool
     */
    public function complete(User $user, $runValidation = true): bool;

    /**
     * @return bool
     */
    public function hasUri(): bool;

    /**
     * @return string
     */
    public function getUri(): string;

    /**
     * Returns the errors for all attributes or a single attribute.
     * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
     * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
     * Note that when returning errors for all attributes, the result is a two-dimensional array, like the following:
     *
     * ```php
     * [
     *     'username' => [
     *         'Username is required.',
     *         'Username must contain only word characters.',
     *     ],
     *     'email' => [
     *         'Email address is invalid.',
     *     ]
     * ]
     * ```
     *
     * @property array An array of errors for all attributes. Empty array is returned if no error.
     * The result is a two-dimensional array. See [[getErrors()]] for detailed description.
     * @see getFirstErrors()
     * @see getFirstError()
     */
    public function getErrors($attribute = null);
}
