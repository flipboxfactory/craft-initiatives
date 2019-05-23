<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\craft\initiatives\cp\controllers;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\craft\initiatives\actions\initiatives\CreateInitiative;
use flipbox\craft\initiatives\actions\initiatives\UpdateInitiative;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class InitiativesController extends AbstractController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'error' => [
                    'default' => 'initiative'
                ],
                'redirect' => [
                    'only' => ['create', 'update'],
                    'actions' => [
                        'create' => [201],
                        'update' => [200],
                    ]
                ],
                'flash' => [
                    'actions' => [
                        'associate' => [
                            204 => Craft::t('initiatives', "Successfully associated user."),
                            401 => Craft::t('initiatives', "Failed to associate user.")
                        ],
                        'dissociate' => [
                            204 => Craft::t('initiatives', "Successfully dissociated user."),
                            401 => Craft::t('initiatives', "Failed to dissociate user.")
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * @return array
     */
    protected function verbs(): array
    {
        return [
            'render' => ['get'],
            'create' => ['post'],
            'update' => ['post']
        ];
    }

    /**
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        /** @var CreateInitiative $action */
        $action = Craft::createObject([
            'class' => CreateInitiative::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'create',
            $this
        ]);

        return $action->runWithParams([]);
    }


    /**
     * @param null $initiative
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpdate($initiative = null)
    {
        if (null === $initiative) {
            $initiative = Craft::$app->getRequest()->getRequiredBodyParam('initiative');
        }

        /** @var UpdateInitiative $action */
        $action = Craft::createObject([
            'class' => UpdateInitiative::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'update',
            $this
        ]);

        return $action->runWithParams([
            'initiative' => $initiative
        ]);
    }

    /**
     * @return bool
     */
    public function checkAdminAccess()
    {
        $this->requireLogin();
        return Craft::$app->getUser()->getIsAdmin();
    }
}
