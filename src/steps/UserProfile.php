<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/initiatives/blob/master/LICENSE.md
 * @link       https://github.com/flipboxfactory/initiatives
 */

namespace flipbox\craft\initiatives\steps;

use craft\elements\User;

class UserProfile extends AbstractStep
{
    /**
     * @var array
     */
    public $requiredAttributes = ['firstName', 'lastName', 'userBiography'];

    /**
     * @var array
     */
    public $queryAttributes = ['userDepartmentAndFunction'];

    /**
     * @inheritdoc
     */
    public function complete(User $user, $runValidation = true): bool
    {
        if ($runValidation) {
            if (!$this->validateAttributes($user)) {
                return false;
            }
        }

        return parent::complete($user, $runValidation);
    }

    /**
     * @return bool
     */
    protected function validateAttributes(User $user): bool
    {
        $successful = true;

        foreach ($this->requiredAttributes as $attribute) {
            if (empty((string)$user->{$attribute})) {
                $successful = false;
                $this->addError(
                    $attribute,
                    sprintf(
                        '%s has not been completed.',
                        $attribute
                    )
                );
            }
        }

        foreach ($this->queryAttributes as $queryAttribute) {
            if (!$query = $user->{$queryAttribute}) {
                $successful = false;
                $this->addError(
                    $queryAttribute,
                    sprintf(
                        '%s has not been completed.',
                        $queryAttribute
                    )
                );
            }
        }

        return $successful;
    }
}
