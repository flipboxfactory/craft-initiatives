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
use flipbox\organizations\records\UserAssociation;
use yii\base\Action;
use yii\web\HttpException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since  1.0.0
 */
abstract class AbstractUserAssociation extends Action
{
    use ManageTrait;

    /**
     * @inheritdoc
     * @param UserAssociation $record
     * @return bool
     */
    abstract protected function performAction(User $user, Initiative $initiative, int $sortOrder = null): bool;

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

    /**
     * @param string $user
     * @param string $initiative
     * @param int|null $sortOrder
     * @return mixed|null
     * @throws HttpException
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function run(
        string $user,
        string $initiative,
        int $sortOrder = null
    ) {
        if (null === ($user = $this->findUser($user))) {
            return $this->handleNotFoundResponse();
        }

        if (null === ($initiative = Initiative::findOne($initiative))) {
            return $this->handleNotFoundResponse();
        }

        return $this->runInternal($user, $initiative, $sortOrder);
    }

    /**
     * @param User $user
     * @param Initiative $initiative
     * @param int|null $sortOrder
     * @return mixed
     * @throws \yii\web\UnauthorizedHttpException
     */
    protected function runInternal(User $user, Initiative $initiative, int $sortOrder = null)
    {
        // Check access
        if (($access = $this->checkAccess($user, $initiative, $sortOrder)) !== true) {
            return $access;
        }

        if (!$this->performAction($user, $initiative, $sortOrder)) {
            return $this->handleFailResponse($user);
        }

        return $this->handleSuccessResponse($user);
    }
}
