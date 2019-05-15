<?php

namespace flipbox\craft\initiatives\elements;

use Craft;
use craft\db\Query;
use craft\elements\db\UserQuery;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\craft\initiatives\records\UserAssociation;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait UsersAttributeTrait
{
    /**
     * @var UserQuery
     */
    private $users;

    /**
     * @param array $sourceElements
     * @return array
     */
    protected static function eagerLoadingUsersMap(array $sourceElements)
    {
        // Get the source element IDs
        $sourceElementIds = ArrayHelper::getColumn($sourceElements, 'id');

        $map = (new Query())
            ->select(['elementId as source', 'userId as target'])
            ->from(UserAssociation::tableName())
            ->where(['elementId' => $sourceElementIds])
            ->all();

        return [
            'elementType' => User::class,
            'map' => $map
        ];
    }

    /************************************************************
     * REQUEST
     ************************************************************/

    /**
     * AssociateUserToOrganization an array of users from request input
     *
     * @param string $identifier
     * @return $this
     */
    public function setUsersFromRequest(string $identifier = 'users')
    {
        if (null !== ($users = Craft::$app->getRequest()->getBodyParam($identifier))) {
            $this->setUsers((array) $users);
        }

        return $this;
    }

    
    /************************************************************
     * USERS QUERY
     ************************************************************/

    /**
     * @param array $criteria
     * @return UserQuery
     */
    public function userQuery($criteria = []): UserQuery
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $query = User::find()
            ->organization($this)
            ->orderBy([
                'userOrder' => SORT_ASC,
                'username' => SORT_ASC,
            ]);

        if (!empty($criteria)) {
            QueryHelper::configure(
                $query,
                $criteria
            );
        }

        return $query;
    }

    /**
     * Get an array of users associated to an organization
     *
     * @param array $criteria
     * @return UserQuery
     */
    public function getUsers($criteria = [])
    {
        if (null === $this->users) {
            $this->users = $this->userQuery();
        }

        if (!empty($criteria)) {
            QueryHelper::configure(
                $this->users,
                $criteria
            );
        }

        return $this->users;
    }

    /**
     * AssociateUserToOrganization users to an organization
     *
     * @param $users
     * @return $this
     */
    public function setUsers($users)
    {
        if ($users instanceof UserQuery) {
            $this->users = $users;
            return $this;
        }

        // Reset the query
        $this->users = $this->userQuery();

        // Remove all users
        $this->users->setCachedResult([]);

        if (!empty($users)) {
            if (!is_array($users)) {
                $users = [$users];
            }

            $this->addUsers($users);
        }

        return $this;
    }

    /**
     * AssociateUserToOrganization an array of users to an organization
     *
     * @param $users
     * @return $this
     */
    public function addUsers(array $users)
    {
        // In case a config is directly passed
        if (ArrayHelper::isAssociative($users)) {
            $users = [$users];
        }

        foreach ($users as $key => $user) {
            // Ensure we have a model
            if (!$user instanceof User) {
                $user = $this->resolveUser($user);
            }

            $this->addUser($user);
        }

        return $this;
    }

    /**
     * @param $user
     * @return User
     */
    protected function resolveUser($user)
    {
        if (is_array($user) &&
            null !== ($id = ArrayHelper::getValue($user, 'id'))
        ) {
            $user = ['id' => $id];
        }

        $object = null;
        if (is_array($user)) {
            $object = User::findOne($user);
        } elseif (is_numeric($user)) {
            $object = Craft::$app->getUsers()->getUserById($user);
        } elseif (is_string($user)) {
            $object = Craft::$app->getUsers()->getUserByUsernameOrEmail($user);
        }

        if (null !== $object) {
            return $object;
        }

        return new User($user);
    }

    /**
     * AssociateUserToOrganization a user to an organization
     *
     * @param User $user
     * @return $this
     */
    public function addUser(User $user)
    {
        $currentUsers = $this->getUsers()->all();

        $userElementsByEmail = ArrayHelper::index(
            $currentUsers,
            'email'
        );

        // Does the user already exist?
        if (!array_key_exists($user->email, $userElementsByEmail)) {
            $currentUsers[] = $user;
            $this->getUsers()->setCachedResult($currentUsers);
        }

        return $this;
    }

    /**
     * DissociateUserFromOrganization a user from an organization
     *
     * @param array $users
     * @return $this
     */
    public function removeUsers(array $users)
    {
        // In case a config is directly passed
        if (ArrayHelper::isAssociative($users)) {
            $users = [$users];
        }

        foreach ($users as $key => $user) {
            // Ensure we have a model
            if (!$user instanceof User) {
                $user = $this->resolveUser($user);
            }

            $this->removeUser($user);
        }

        return $this;
    }

    /**
     * DissociateUserFromOrganization a user from an organization
     *
     * @param User $user
     * @return $this
     */
    public function removeUser(User $user)
    {
        $userElementsByEmail = ArrayHelper::index(
            $this->getUsers()->all(),
            'email'
        );

        // Does the user already exist?
        if (array_key_exists($user->email, $userElementsByEmail)) {
            unset($userElementsByEmail[$user->email]);

            $this->getUsers()->setCachedResult(
                array_values($userElementsByEmail)
            );
        }

        return $this;
    }

    /**
     * Reset users
     *
     * @return $this
     */
    public function resetUsers()
    {
        $this->users = null;
        return $this;
    }


    /*******************************************
     * ASSOCIATE and/or DISASSOCIATE
     *******************************************/

    /**
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveUsers()
    {

        // No changes?
        if (null === ($users = $this->getUsers()->getCachedResult())) {
            return true;
        }

        $currentAssociations = UserAssociation::find()
            ->elementId($this->getId() ?: false)
            ->indexBy('userId')
            ->orderBy(['userOrder' => SORT_ASC])
            ->all();

        $success = true;
        $associations = [];
        $order = 1;
        foreach ($users as $user) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $user->getId()))) {
                $association = (new UserAssociation())
                    ->setUser($user)
                    ->setOrganization($this);
            }

            $association->userOrder = $order++;

            $associations[] = $association;
        }

        // Delete those removed
        foreach ($currentAssociations as $currentAssociation) {
            if (!$currentAssociation->delete()) {
                $success = false;
            }
        }

        foreach ($associations as $association) {
            if (!$association->save()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->addError('users', 'Unable to associate users.');
        }

        return $success;
    }

    /**
     * @param User $user
     * @param int|null $sortOrder
     * @return bool
     */
    public function associateUser(User $user, int $sortOrder = null): bool
    {
        if (null === ($association = UserAssociation::find()
                ->elementId($this->getId() ?: false)
                ->userId($user->getId() ?: false)
                ->one())
        ) {
            $association = new UserAssociation([
                'organization' => $this,
                'user' => $user
            ]);
        }

        if (null !== $sortOrder) {
            $association->userOrder = $sortOrder;
        }

        if (!$association->save()) {
            $this->addError('organizations', 'Unable to associate user.');

            return false;
        }

        $this->resetUsers();

        return true;
    }

    /**
     * @param UserQuery $query
     * @return bool
     * @throws \Throwable
     */
    public function associateUsers(UserQuery $query)
    {
        $users = $query->all();

        if (empty($users)) {
            return true;
        }

        $success = true;
        $currentAssociations = UserAssociation::find()
            ->elementId($this->getId() ?: false)
            ->indexBy('userId')
            ->all();

        foreach ($users as $user) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $user->getId()))) {
                $association = (new UserAssociation())
                    ->setUser($user)
                    ->setOrganization($this);
            }

            if (!$association->save()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->addError('users', 'Unable to associate users.');
        }

        $this->resetUsers();

        return $success;
    }

    /**
     * @param User $user
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function dissociateUser(User $user): bool
    {
        if (null === ($association = UserAssociation::find()
                ->elementId($this->getId() ?: false)
                ->userId($user->getId() ?: false)
                ->one())
        ) {
            return true;
        }

        if (!$association->delete()) {
            $this->addError('organizations', 'Unable to dissociate user.');

            return false;
        }

        $this->resetUsers();

        return true;
    }

    /**
     * @param UserQuery $query
     * @return bool
     * @throws \Throwable
     */
    public function dissociateUsers(UserQuery $query)
    {
        $users = $query->all();

        if (empty($users)) {
            return true;
        }

        $currentAssociations = UserAssociation::find()
            ->elementId($this->getId() ?: false)
            ->indexBy('userId')
            ->all();

        $success = true;

        foreach ($users as $user) {
            if (null === ($association = ArrayHelper::remove($currentAssociations, $user->getId()))) {
                continue;
            }

            if (!$association->delete()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->addError('users', 'Unable to associate users.');
        }

        $this->resetUsers();

        return $success;
    }
}
