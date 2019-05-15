<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/initiative/license
 * @link       https://www.flipboxfactory.com/software/initiative/
 */

namespace flipbox\craft\initiatives\validators;

use Craft;
use craft\helpers\Json;
use flipbox\craft\initiatives\Initiatives as InitiativesPlugin;
use flipbox\craft\initiatives\queries\InitiativeQuery;
use yii\base\Exception;
use yii\validators\Validator;

/**
 * Validates all associated
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class InitiativesValidator extends Validator
{
    const DEFAULT_MESSAGE = 'Invalid initiatives.';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Craft::t('initiatives', static::DEFAULT_MESSAGE);
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (!$value instanceof InitiativeQuery) {
            throw new Exception("Validation value must be an 'InitiativeQuery'.");
        }

        return $this->validateInitiativeQuery($value);
    }

    /**
     * @inheritdoc
     * @param InitiativeQuery $query
     */
    private function validateInitiativeQuery(InitiativeQuery $query)
    {
        if (null === ($initiatives = $query->getCachedResult())) {
            return null;
        }

        $hasError = false;

        foreach ($initiatives as $initiative) {
            if (null === $initiative->id && !$initiative->validate()) {
                $hasError = true;

                InitiativesPlugin::warning(
                    sprintf(
                        "Invalid initiative: '%s'",
                        Json::encode($initiative->getFirstErrors())
                    ),
                    __METHOD__
                );
            }
        }

        if ($hasError) {
            return [$this->message, []];
        }

        return null;
    }
}
