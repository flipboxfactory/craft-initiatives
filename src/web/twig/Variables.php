<?php

namespace flipbox\craft\initiatives\web\twig;

use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\craft\initiatives\elements\Initiative;
use flipbox\craft\initiatives\Initiatives;
use flipbox\craft\initiatives\models\Settings;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Variables
{
    /**
     * Plugins settings which are accessed via 'craft.initiatives.settings'
     *
     * @return Settings
     */
    public function getSettings()
    {
        return Initiatives::getInstance()->getSettings();
    }


    /*******************************************
     * ALIASES
     *******************************************/

    /**
     * @param array $criteria
     * @return \flipbox\craft\initiatives\queries\InitiativeQuery
     */
    public function getQuery($criteria = [])
    {
        $query = Initiative::find();

        QueryHelper::configure(
            $query,
            $criteria
        );

        return $query;
    }

    /**
     * @param $identifier
     * @return \flipbox\craft\initiatives\elements\Initiative
     */
    public function find($identifier)
    {
        return Initiative::findOne($identifier);
    }

    /**
     * @param $identifier
     * @return \flipbox\craft\initiatives\elements\Initiative
     */
    public function get($identifier)
    {
        return Initiative::getOne($identifier);
    }
}
