<?php

namespace flipbox\craft\initiatives\cp\controllers\view;

use Craft;
use craft\base\Field;
use craft\elements\User;
use craft\helpers\UrlHelper;
use craft\models\FieldLayoutTab;
use flipbox\craft\elements\nestedIndex\web\assets\index\NestedElementIndex;
use flipbox\craft\ember\helpers\SiteHelper;
use flipbox\craft\initiatives\elements\Initiative;

class InitiativesController extends AbstractController
{
    /**
     * The index view template path
     */
    const TEMPLATE_INDEX = AbstractController::TEMPLATE_BASE . DIRECTORY_SEPARATOR . 'initiatives';

    /**
     * The upsert view template path
     */
    const TEMPLATE_UPSERT = self::TEMPLATE_INDEX . DIRECTORY_SEPARATOR . 'upsert';

    /**
     * The users view template path
     */
    const TEMPLATE_USERS = self::TEMPLATE_INDEX . DIRECTORY_SEPARATOR . 'users';

    /**
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        // Empty variables for template
        $variables = [];

        $variables['elementType'] = Initiative::class;

        // apply base view variables
        $this->baseVariables($variables);

        return $this->renderTemplate(static::TEMPLATE_INDEX, $variables);
    }

    /**
     * @param null $identifier
     * @param Initiative|null $initiative
     * @return \yii\web\Response
     * @throws \flipbox\ember\exceptions\ObjectNotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpsert($identifier = null, Initiative $initiative = null)
    {
        // Empty variables for template
        $variables = [];

        if (null === $initiative) {
            if (null === $identifier) {
                $initiative = new Initiative;
            } else {
                $initiative = Initiative::getOne($identifier);
            }
        }

        // Template variables
        if ($initiative->getId() === null) {
            $this->insertVariables($variables);
        } else {
            $this->updateVariables($variables, $initiative);
        }

        // Full page form in the CP
        $variables['fullPageForm'] = true;

        // Initiative
        $variables['initiative'] = $initiative;

        Craft::$app->getView()->registerAssetBundle(NestedElementIndex::class);

        $variables['elementType'] = User::class;

        $variables['usersInputJsClass'] = 'Craft.NestedElementIndexSelectInput';
        $variables['usersInputJs'] = $this->getUserInputJs($initiative);
        $variables['usersIndexJsClass'] = 'Craft.NestedElementIndex';
        $variables['usersIndexJs'] = $this->getUserIndexJs($initiative);

        // Tabs
        $variables['tabs'] = $this->getTabs($initiative);
        $variables['selectedTab'] = 'general';

        return $this->renderTemplate(static::TEMPLATE_UPSERT, $variables);
    }

    /*******************************************
     * TABS
     *******************************************/

    /**
     * @param Initiative $initiative
     * @param bool $includeUsers
     * @return array
     */
    protected function getTabs(Initiative $initiative, bool $includeUsers = true): array
    {
        $tabs = [];

        $count = 1;
        foreach ($initiative->getFieldLayout()->getTabs() as $tab) {
            $tabs[] = $this->getTab($initiative, $tab, $count++);
        }

        if (null !== $initiative->getId() &&
            true === $includeUsers
        ) {
            $tabs['users'] = [
                'label' => Craft::t('initiatives', 'Users'),
                'url' => '#user-index'
            ];
        }

        return $tabs;
    }

    /**
     * @param Initiative $initiative
     * @param FieldLayoutTab $tab
     * @param int $count
     * @return array
     */
    protected function getTab(Initiative $initiative, FieldLayoutTab $tab, int $count): array
    {
        $hasErrors = false;
        if ($initiative->hasErrors()) {
            foreach ($tab->getFields() as $field) {
                /** @var Field $field */
                $hasErrors = $initiative->getErrors($field->handle) ? true : $hasErrors;
            }
        }

        return [
            'label' => $tab->name,
            'url' => '#tab' . $count,
            'class' => $hasErrors ? 'error' : null
        ];
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
        // apply base view variables
        $this->baseVariables($variables);

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/' . $initiative->getId());

        // Append title
        $variables['title'] .= ' - ' . Craft::t('initiatives', 'Edit') . ' ' . $initiative->title;

        // Define the parent options criteria
        $variables['parentOptionCriteria'] = [
            'siteId' => $initiative->getSite()->id,
            'status' => null,
            'enabledForSite' => false,
        ];

        if ($initiative->id !== null) {
            // Prevent the current entry, or any of its descendants, from being options
            $excludeIds = Initiative::find()
                ->descendantOf($initiative)
                ->status(null)
                ->enabledForSite(false)
                ->ids();

            $excludeIds[] = $initiative->id;
            $variables['parentOptionCriteria']['where'] = [
                'not in',
                'elements.id',
                $excludeIds
            ];
        }

        // Get the initially selected parent
        $parentId = Craft::$app->getRequest()->getParam('parentId');

        if ($parentId === null && $initiative->id !== null) {
            $parentId = $initiative->getAncestors(1)->status(null)->enabledForSite(false)->ids();
        }

        if (is_array($parentId)) {
            $parentId = reset($parentId) ?: null;
        }

        if ($parentId) {
            $variables['parent'] = Initiative::getOne($parentId);
        }

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t(
                    'initiatives',
                    "Edit"
                ) . ": " . $initiative->title,
            'url' => UrlHelper::url(
                $variables['baseCpPath'] . '/' . $initiative->getId()
            )
        ];
    }

    /*******************************************
     * TOKEN VARIABLES
     *******************************************/

    /**
     * @param array $variables
     * @param Initiative $initiative
     */
    protected function userVariables(array &$variables, Initiative $initiative)
    {
        // apply base view variables
        $this->baseVariables($variables);

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $this->getBaseContinueEditingUrl('/' . $initiative->getId() . '/users');
        $variables['baseActionPath'] = $this->module->uniqueId . ('/token');

        // Append title
        $variables['title'] .= ' - ' . $initiative->title . ' ' . Craft::t('initiatives', 'Users');

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t(
                    'initiatives',
                    "Edit"
                ) . ": " . $initiative->title,
            'url' => UrlHelper::url(
                $variables['baseCpPath'] . '/' . $initiative->getId()
            )
        ];
    }

    /**
     * @return string
     */
    protected function getBaseActionPath(): string
    {
        return parent::getBaseActionPath() . '/initiatives';
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
    }


    /*******************************************
     * JS CONFIGS
     *******************************************/

    /**
     * @param Initiative $element
     * @return array
     */
    private function getUserIndexJs(Initiative $element): array
    {
        return [
            'source' => 'nested',
            'context' => 'index',
            'showStatusMenu' => true,
            'showSiteMenu' => true,
            'hideSidebar' => false,
            'toolbarFixed' => false,
            'storageKey' => 'nested.index.initiative.users',
            'updateElementsAction' => 'initiatives/cp/user-indexes/get-elements',
            'submitActionsAction' => 'initiatives/cp/user-indexes/perform-action',
            'criteria' => [
                'enabledForSite' => null,
                'siteId' => SiteHelper::ensureSiteId($element->siteId),
                'initiative' => $element->getId()
            ],
            'viewParams' => [
                'initiative' => $element->getId()
            ],
            'viewSettings' => [
                'loadMoreAction' => 'initiatives/cp/user-indexes/get-more-elements'
            ]
        ];
    }

    /**
     * @param Initiative $element
     * @return array
     */
    private function getUserInputJs(Initiative $element): array
    {
        return [
            'elementType' => User::class,
            'sources' => '*',
            'criteria' => [
                'enabledForSite' => null,
                'siteId' => SiteHelper::ensureSiteId($element->siteId),
            ],
            'sourceElementId' => $element->getId() ?: null,
            'viewMode' => 'list',
            'limit' => null,
            'selectionLabel' => Craft::t('initiatives', "Add a user"),
            'storageKey' => 'nested.index.input.initiative.users',
            'elements' => $element->getUsers()->ids(),
            'addAction' => 'initiatives/cp/users/associate',
            'selectTargetAttribute' => 'user',
            'selectParams' => [
                'initiative' => $element->getId() ?: null
            ]
        ];
    }
}
