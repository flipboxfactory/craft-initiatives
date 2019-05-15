<?php

namespace flipbox\craft\initiatives\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\elements\db\UserQuery;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use craft\records\Element as ElementRecord;
use flipbox\craft\ember\elements\ExplicitElementTrait;
use flipbox\craft\ember\helpers\ObjectHelper;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\craft\ember\validators\ModelValidator;
use flipbox\craft\initiatives\behaviors\InitiativeAttributesToUserQueryBehavior;
use flipbox\craft\initiatives\Initiatives;
use flipbox\craft\initiatives\Initiatives as InitiativesPlugin;
use flipbox\craft\initiatives\initiatives\ActionInterface;
use flipbox\craft\initiatives\initiatives\InitiativeSettings;
use flipbox\craft\initiatives\initiatives\InitiativeSettingsInterface;
use flipbox\craft\initiatives\queries\InitiativeQuery;
use flipbox\craft\initiatives\records\Initiative as InitiativeRecord;
use flipbox\craft\initiatives\records\UserAssociation;
use yii\base\Exception;

class Initiative extends Element
{
    use ExplicitElementTrait,
        InitiativeSettingsTrait,
        UsersAttributeTrait;

    /**
     * @var int|null New parent ID
     */
    public $newParentId;

    /**
     * @var bool|null
     */
    private $hasNewParent = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->structureId = InitiativesPlugin::getInstance()->getSettings()->structureId;
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('initiatives', 'Initiative');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'initiative';
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasUris(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout()
    {
        if (null !== ($fieldLayout = parent::getFieldLayout())) {
            return $fieldLayout;
        }

        return new FieldLayout([
            'type' => get_class($this)
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl('initiatives/' . $this->id);
    }

    /**
     * @inheritdoc
     */
    public function getUriFormat()
    {
        return Initiatives::getInstance()->getSettings()->uriFormat;
    }

    /**
     * @inheritdoc
     */
    protected function route()
    {
        return $this->getSettings()->getRoute($this);
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function renderHtml(): string
    {
        $template = $this->getSettings()->getTemplate();

        if (empty($template)) {
            return '';
        }

        return Craft::$app->getView()->renderTemplate(
            $template,
            $this->getSettings()->getTemplateVariables($this)
        );
    }


    /************************************************************
     * ACTION
     ************************************************************/

    /**
     * @param User $user
     * @return mixed
     */
    public function createAction(User $user): ActionInterface
    {
        return $this->getSettings()->createAction($this, $user);
    }


    /************************************************************
     * FIND
     ************************************************************/

    /**
     * @inheritdoc
     * @return InitiativeQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new InitiativeQuery(static::class);
    }

    /**
     * @inheritdoc
     * @return static|null
     */
    public static function findOne($criteria = null)
    {
        if ($criteria instanceof self) {
            return $criteria;
        }

        return parent::findOne($criteria);
    }

    /**
     * @inheritDoc
     * @return static|null
     */
    protected static function findByCondition($criteria, bool $one)
    {
        if (is_numeric($criteria)) {
            $criteria = ['id' => $criteria];
        }

        if (is_string($criteria)) {
            $criteria = ['slug' => $criteria];
        }

        return parent::findByCondition($criteria, $one);
    }


    /************************************************************
     * TARGETS
     ************************************************************/

    /**
     * @inheritdoc
     */
    public function hasCompleted(User $user): bool
    {
        return UserAssociation::find()
            ->elementId($this->id ?: 'x')
            ->userId($user->id ?: 'x')
            ->status(UserAssociation::STATUS_COMPLETE)
            ->exists();
    }

    /**
     * @inheritdoc
     */
    public function isTarget(User $user): bool
    {
        return UserAssociation::find()
            ->elementId($this->id ?: 'x')
            ->userId($user->id ?: 'x')
            ->exists();
    }


    /**
     * Associate an array of users
     *
     * @param $users
     * @return $this
     */
    public function addTargets(array $users)
    {
        // In case a config is directly passed
        if (ArrayHelper::isAssociative($users)) {
            $users = [$users];
        }

        foreach ($users as $key => $user) {
            if (!$user instanceof User) {
                $user = $this->resolve($user);
            }

            $this->addTarget($user);
        }

        return $this;
    }

    /**
     * @param mixed $user
     * @return User
     */
    protected function resolve($user)
    {
        if (is_array($user) &&
            null !== ($id = ArrayHelper::getValue($user, 'id'))
        ) {
            return Craft::$app->getUsers()->getUserById($id);
        }

        if (!is_numeric($user)) {
            return Craft::$app->getUsers()->getUserById($user);
        }

        return Craft::$app->getUsers()->getUserByUsernameOrEmail($user);
    }

    /**
     * Associate a user
     *
     * @param User $user
     * @return $this
     */
    public function addTarget(User $user)
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
     * Dissociate a user
     *
     * @param array $users
     * @return $this
     */
    public function removeTargets(array $users)
    {
        // In case a config is directly passed
        if (ArrayHelper::isAssociative($users)) {
            $users = [$users];
        }

        foreach ($users as $key => $user) {
            if (!$user instanceof User) {
                $user = $this->resolve($user);
            }

            $this->removeTarget($user);
        }

        return $this;
    }

    /**
     * Dissociate a user
     *
     * @param User $user
     * @return $this
     */
    public function removeTarget(User $user)
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
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('app', 'Title'),
            'uri' => Craft::t('app', 'URI'),
            'elements.dateCreated' => Craft::t('app', 'Date Created'),
            'elements.dateUpdated' => Craft::t('app', 'Date Updated'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('app', 'Title')],
            'id' => ['label' => Craft::t('app', 'Id')],
            'uri' => ['label' => Craft::t('app', 'URI')],
            'dateCreated' => ['label' => Craft::t('app', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('app', 'Date Updated')],
        ];
    }


    /************************************************************
     * SOURCES
     ************************************************************/

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        return [
            [
                'key' => '*',
                'label' => Craft::t('initiatives', 'All initiatives'),
                'criteria' => ['status' => null],
                'hasThumbs' => false,
                'defaultSort' => ['structure', 'asc'],
                'structureId' => InitiativesPlugin::getInstance()->getSettings()->structureId,
                'structureEditable' => true
            ]
        ];
    }


    /**
     * Returns whether the entry has been assigned a new parent entry.
     *
     * @return bool
     * @see beforeSave()
     * @see afterSave()
     */
    public function hasNewParent(): bool
    {
        if ($this->hasNewParent === null) {
            $this->hasNewParent = $this->checkForNewParent();
        }

        return $this->hasNewParent;
    }

    /**
     * Checks if the entry has been assigned a new parent entry.
     *
     * @return bool
     * @see _hasNewParent()
     */
    private function checkForNewParent(): bool
    {
        // Is it a brand new entry?
        if ($this->id === null) {
            return true;
        }

        // Was a new parent ID actually submitted?
        if ($this->newParentId === null) {
            return false;
        }

        // Is it set to the top level now, but it hadn't been before?
        if (!$this->newParentId && $this->level != 1) {
            return true;
        }

        // Is it set to be under a parent now, but didn't have one before?
        if ($this->newParentId && $this->level == 1) {
            return true;
        }

        // Is the parentId set to a different entry ID than its previous parent?
        $oldParentQuery = self::find();
        $oldParentQuery->ancestorOf($this);
        $oldParentQuery->ancestorDist(1);
        $oldParentQuery->status(null);
        $oldParentQuery->siteId($this->siteId);
        $oldParentQuery->enabledForSite(false);
        $oldParentQuery->select('elements.id');
        $oldParentId = $oldParentQuery->scalar();

        return $this->newParentId != $oldParentId;
    }


    /************************************************************
     * EVENTS
     ************************************************************/

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        if ($this->hasNewParent()) {
            if ($this->newParentId) {
                $parent = Initiative::getOne(
                    $this->newParentId
                );
            } else {
                $parent = null;
            }

            $this->setParent($parent);
        }

        return parent::beforeSave($isNew);
    }

    /**
     * @param bool $isNew
     * @throws Exception
     */
    public function afterSave(bool $isNew)
    {
        // Users
        if (!$this->associateUsers()) {
            throw new Exception("Unable to save users.");
        }

        // Has the parent changed?
        if ($this->hasNewParent()) {
            if (!$this->newParentId) {
                Craft::$app->getStructures()->appendToRoot($this->structureId, $this);
            } else {
                Craft::$app->getStructures()->append($this->structureId, $this, $this->getParent());
            }
        }

        // Update the category's descendants, who may be using this category's URI in their own URIs
        Craft::$app->getElements()->updateDescendantSlugsAndUris($this, true, true);

        parent::afterSave($isNew);
    }


    /************************************************************
     * ATTRIBUTES
     ************************************************************/

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            [
                'settings'
            ]
        );
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'settings'
                    ],
                    ModelValidator::class
                ],
                [
                    [
                        'settings'
                    ],
                    'safe',
                    'on' => [
                        self::SCENARIO_DEFAULT,
                        self::SCENARIO_LIVE
                    ]
                ]
            ]
        );
    }


    /*******************************************
     * USERS - ASSOCIATE and/or DISASSOCIATE
     *******************************************/

    /**
     * @param Initiative $this
     * @return bool
     * @throws \Exception
     */
    private function associateUsers()
    {
        $users = $this->getUsers()->all();

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
                    ->setElement($this);
            }

            if (!$association->save()) {
                $success = false;
            }
        }

        if (!$success) {
            $this->addError('targets', 'Unable to associate users.');
        }

        return $success;
    }
}
