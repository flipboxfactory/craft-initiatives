<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\craft\initiatives\cp\controllers;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\craft\initiatives\actions\users\AssociateUserToInitiative;
use flipbox\craft\initiatives\actions\users\DissociateUserFromInitiative;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UsersController extends AbstractController
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
                    'only' => ['associate', 'dissociate'],
                    'actions' => [
                        'associate' => [204],
                        'dissociate' => [204],
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
            'associate' => ['post']
        ];
    }

    /**
     * @param int|string|null $user
     * @param int|string|null $initiative
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAssociate($user = null, $initiative = null)
    {
        if (null === $initiative) {
            $initiative = Craft::$app->getRequest()->getBodyParam('initiative');
        }

        if (null === $user) {
            $user = Craft::$app->getRequest()->getBodyParam('user');
        }

        /** @var AssociateUserToInitiative $action */
        $action = Craft::createObject([
            'class' => AssociateUserToInitiative::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'associate',
            $this
        ]);

        return $action->runWithParams([
            'initiative' => $initiative,
            'user' => $user
        ]);
    }

    /**
     * @param int|string|null $user
     * @param int|string|null $initiative
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionDissociate($user = null, $initiative = null)
    {
        if (null === $initiative) {
            $initiative = Craft::$app->getRequest()->getBodyParam('initiative');
        }

        if (null === $user) {
            $user = Craft::$app->getRequest()->getBodyParam('user');
        }

        /** @var DissociateUserFromInitiative $action */
        $action = Craft::createObject([
            'class' => DissociateUserFromInitiative::class,
            'checkAccess' => [$this, 'checkAdminAccess']
        ], [
            'dissociate',
            $this
        ]);

        return $action->runWithParams([
            'initiative' => $initiative,
            'user' => $user
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
