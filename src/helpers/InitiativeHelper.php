<?php

namespace flipbox\craft\initiatives\helpers;

use craft\helpers\StringHelper;
use ReflectionClass;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class InitiativeHelper
{
    /**
     * @param $class
     * @return string
     * @throws \ReflectionException
     */
    public static function displayName($class): string
    {
        $shortName = static::shortName($class);

        // Split capital letters
        $parts = preg_split("/(?<=[a-z])(?![a-z])/", $shortName, -1, PREG_SPLIT_NO_EMPTY);

        // Assemble
        return StringHelper::toString($parts, ' ');
    }


    /**
     * @param $class
     * @return string
     * @throws \ReflectionException
     */
    public static function shortName($class): string
    {
        $reflect = new ReflectionClass(
            $class
        );

        return $reflect->getShortName();
    }
}
