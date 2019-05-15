<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/initiatives/blob/master/LICENSE.md
 * @link       https://github.com/flipboxfactory/initiatives
 */

namespace flipbox\craft\initiatives\controllers;

use Craft;
use craft\web\Controller;
use flipbox\craft\initiatives\actions\users\CompleteInitiative;
use flipbox\craft\initiatives\initiatives\ActionInterface;

class InitiativesController extends Controller
{
    /**
     * @param null $initiative
     * @param null $user
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionComplete($initiative = null, $user = null)
    {
        if ($initiative === null) {
            $initiative = Craft::$app->getRequest()->getRequiredParam('initiative');
        }

        if ($user === null) {
            $user = Craft::$app->getRequest()->getRequiredParam('user');
        }

        $action = Craft::createObject([
            'class' => CompleteInitiative::class,
            'checkAccess' => [$this, 'checkCompleteActionAccess']
        ], [
            'complete',
            $this
        ]);

        return $action->runWithParams([
            'initiative' => $initiative,
            'user' => $user
        ]);
    }

    /**
     * @param ActionInterface $action
     * @return bool
     */
    public function checkCompleteActionAccess(ActionInterface $action): bool
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        return $currentUser->admin || $action->getInitiative()->isTarget($currentUser);
    }
}
