<?php

namespace flipbox\craft\initiatives\cp;

use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\View;
use flipbox\craft\initiatives\Initiatives;
use yii\base\Event;
use yii\base\Module;
use yii\web\NotFoundHttpException;

/**
 * @property Initiatives $module
 */
class Cp extends Module
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Base template directory
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $e) {
                $e->roots['nested-element-index'] = Craft::$app->getPath()->getVendorPath() .
                    '/flipboxfactory/craft-elements-nested-index/src/templates';
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!Craft::$app->request->getIsCpRequest()) {
            throw new NotFoundHttpException();
        }

        return parent::beforeAction($action);
    }
}
