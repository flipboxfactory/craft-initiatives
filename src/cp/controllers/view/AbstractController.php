<?php

namespace flipbox\craft\initiatives\cp\controllers\view;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use flipbox\craft\initiatives\cp\Cp;
use flipbox\craft\initiatives\Initiatives;

/**
 * @property Cp $module
 */
abstract class AbstractController extends Controller
{
    /**
     * The index view template path
     */
    const TEMPLATE_BASE = 'initiatives' . DIRECTORY_SEPARATOR . '_cp';

    /**
     * @return string
     */
    protected function getBaseActionPath(): string
    {
        return Initiatives::getInstance()->getUniqueId() . '/cp';
    }

    /**
     * @return string
     */
    protected function getBaseCpPath(): string
    {
        return Initiatives::getInstance()->getUniqueId();
    }

    /**
     * @param string $endpoint
     * @return string
     */
    protected function getBaseContinueEditingUrl(string $endpoint = ''): string
    {
        return $this->getBaseCpPath() . $endpoint;
    }


    /*******************************************
     * VARIABLES
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function baseVariables(array &$variables = [])
    {
        /** @var Initiatives $module */
        $module = Initiatives::getInstance();

        // Patron settings
        $variables['settings'] = $module->getSettings();

        // Page title
        $variables['title'] = Craft::t('initiatives', "Initiatives");

        // Selected tab
        $variables['selectedTab'] = '';

        // Path to controller actions
        $variables['baseActionPath'] = $this->getBaseActionPath();

        // Path to CP
        $variables['baseCpPath'] = $this->getBaseCpPath();

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $this->getBaseCpPath();

        // Select our sub-nav
        if (!$activeSubNav = Craft::$app->getRequest()->getSegment(2)) {
            $activeSubNav = 'elements';
        }
        $variables['selectedSubnavItem'] = 'initiatives.' . $activeSubNav;

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => $variables['title'],
            'url' => UrlHelper::url($variables['baseCpPath'])
        ];
    }


    /*******************************************
     * INSERT VARIABLES
     *******************************************/

    /**
     * @param array $variables
     */
    protected function insertVariables(array &$variables)
    {
        // apply base view variables
        $this->baseVariables($variables);

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/{id}');

        // Append title
        $variables['title'] .= ' - ' . Craft::t('initiatives', 'New');

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t('initiatives', 'New'),
            'url' => UrlHelper::url($variables['baseCpPath'] . '/new')
        ];
    }
}
