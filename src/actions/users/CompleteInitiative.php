<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/initiatives/license
 * @link       https://www.flipboxfactory.com/software/initiatives/
 */

namespace flipbox\craft\initiatives\actions\users;

use Craft;
use craft\elements\User;
use flipbox\craft\ember\actions\ManageTrait;
use flipbox\craft\initiatives\elements\Initiative;
use flipbox\craft\initiatives\initiatives\ActionInterface;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class CompleteInitiative
{
    use ManageTrait;

    /**
     * @param string $user
     * @param string $initiative
     * @return null|\yii\base\Model|\yii\web\Response
     * @throws HttpException
     */
    public function run(
        string $user,
        string $initiative
    ) {
        if (null === ($user = $this->findUser($user))) {
            return $this->handleNotFoundResponse();
        }

        if (null === ($initiative = Initiative::findOne($initiative))) {
            return $this->handleNotFoundResponse();
        }

        return $this->runInternal($user, $initiative);
    }

    /**
     * @inheritdoc
     * @param ActionInterface $action
     * @return bool
     */
    protected function performAction(ActionInterface $action): bool
    {
        return $action->complete();
    }

    /**
     * HTTP not found response code
     *
     * @return int
     */
    protected function statusCodeNotFound(): int
    {
        return $this->statusCodeNotFound ?? 404;
    }

    /**
     * @return string
     */
    protected function messageNotFound(): string
    {
        return $this->messageNotFound ?? 'Unable to find object.';
    }

    /**
     * @return null
     * @throws HttpException
     */
    protected function handleNotFoundResponse()
    {
        throw new HttpException(
            $this->statusCodeNotFound(),
            $this->messageNotFound()
        );
    }

    /**
     * @param User $user
     * @param Initiative $initiative
     * @return mixed
     * @throws \yii\web\UnauthorizedHttpException
     */
    protected function runInternal(User $user, Initiative $initiative)
    {
        $action = $initiative->createAction($user);

        // Check access
        if (($access = $this->checkAccess($action)) !== true) {
            return $access;
        }

        if (!$this->performAction($action)) {
            return $this->handleFailResponse($action);
        }

        return $this->handleSuccessResponse($action);
    }

    /**
     * @param string|int $identifier
     * @return User|null
     */
    protected function findUser($identifier)
    {
        if (is_numeric($identifier)) {
            return Craft::$app->getUsers()->getUserById($identifier);
        }

        return Craft::$app->getUsers()->getUserByUsernameOrEmail($identifier);
    }
}
