<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/initiative/license
 * @link       https://www.flipboxfactory.com/software/initiative/
 */

namespace flipbox\craft\initiatives\cp\controllers\settings;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\craft\initiatives\cp\actions\initiatives\CreateInitiative;
use flipbox\craft\initiatives\cp\actions\initiatives\UpdateInitiative;
use flipbox\craft\initiatives\cp\controllers\AbstractController;

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
                        'delete' => [204],
                    ]
                ],
                'flash' => [
                    'actions' => [
                        'create' => [
                            201 => Craft::t('initiatives', "Type successfully created."),
                            401 => Craft::t('initiatives', "Failed to create type.")
                        ],
                        'update' => [
                            200 => Craft::t('initiatives', "Type successfully updated."),
                            401 => Craft::t('initiatives', "Failed to update type.")
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
            'create' => ['post'],
            'update' => ['post', 'put']
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
            'checkAccess' => [$this, 'checkCreateAccess']
        ], [
            'create',
            $this
        ]);

        $response = $action->runWithParams([]);

        return $response;
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
            'checkAccess' => [$this, 'checkUpdateAccess']
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
    public function checkCreateAccess(): bool
    {
        return $this->checkAdminAccess();
    }

    /**
     * @return bool
     */
    public function checkUpdateAccess(): bool
    {
        return $this->checkAdminAccess();
    }

    /**
     * @return bool
     */
    protected function checkAdminAccess()
    {
        $this->requireLogin();
        return Craft::$app->getUser()->getIsAdmin();
    }
}
