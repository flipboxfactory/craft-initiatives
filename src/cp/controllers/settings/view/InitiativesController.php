<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\craft\initiatives\cp\controllers\settings\view;

use Craft;
use craft\helpers\UrlHelper as UrlHelper;
use flipbox\craft\initiatives\elements\Initiative;
use yii\web\Response;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class InitiativesController extends AbstractController
{
    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = parent::TEMPLATE_BASE . DIRECTORY_SEPARATOR . 'initiatives';

    /**
     * The insert/update view template path
     */
    const TEMPLATE_UPSERT = self::TEMPLATE_INDEX . DIRECTORY_SEPARATOR . 'upsert';

    /**
     * @return Response
     */
    public function actionIndex()
    {
        $variables = [];
        $this->baseVariables($variables);

        $variables['initiatives'] = Initiative::find()->all();

        return $this->renderTemplate(static::TEMPLATE_INDEX, $variables);
    }

    /**
     * @param null $identifier
     * @param Initiative|null $initiative
     * @return Response
     * @throws \craft\errors\ElementNotFoundException
     */
    public function actionUpsert($identifier = null, Initiative $initiative = null)
    {
        if (null === $initiative) {
            if (null === $identifier) {
                $initiative = new Initiative;
            } else {
                $initiative = Initiative::getOne($identifier);
            }
        }

        $variables = [];
        if (null === $initiative->getId()) {
            $this->insertVariables($variables);
        } else {
            $this->updateVariables($variables, $initiative);
        }

        $variables['initiative'] = $initiative;
        $variables['fullPageForm'] = true;
        $variables['tabs'] = $this->getTabs();

        return $this->renderTemplate(static::TEMPLATE_UPSERT, $variables);
    }

    /*******************************************
     * TABS
     *******************************************/

    /**
     * @return array|null
     */
    protected function getTabs(): array
    {
        return [
            'layout' => [
                'label' => Craft::t('initiatives', 'Layout'),
                'url' => '#layout'
            ]
        ];
    }

    /*******************************************
     * BASE PATHS
     *******************************************/

    /**
     * @return string
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/initiatives';
    }

    /**
     * @return string
     */
    protected function getBaseCpPath(): string
    {
        return parent::getBaseCpPath() . '/initiatives';
    }

    /*******************************************
     * INSERT VARIABLES
     *******************************************/

    /**
     * @param array $variables
     */
    protected function insertVariables(array &$variables)
    {
        parent::insertVariables($variables);
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/{id}');
    }

    /*******************************************
     * UPDATE VARIABLES
     *******************************************/

    /**
     * @param array $variables
     * @param Initiative $initiative
     */
    protected function updateVariables(array &$variables, Initiative $initiative)
    {
        $this->baseVariables($variables);
        $variables['title'] .= ' - ' . Craft::t('initiatives', 'Edit') . ' ' . $initiative->title;
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/' . $initiative->getId());
        $variables['crumbs'][] = [
            'label' => $initiative->title,
            'url' => UrlHelper::url($variables['continueEditingUrl'])
        ];
    }

    /*******************************************
     * VARIABLES
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function baseVariables(array &$variables = [])
    {
        parent::baseVariables($variables);
        $variables['title'] .= ': Initiatives';
        $variables['crumbs'][] = [
            'label' => Craft::t('initiatives', 'Initiatives'),
            'url' => UrlHelper::url($variables['baseCpPath'])
        ];
    }
}
