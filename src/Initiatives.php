<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/initiatives/blob/master/LICENSE.md
 * @link       https://github.com/flipboxfactory/initiatives
 */

namespace flipbox\craft\initiatives;

use Craft;
use craft\base\Plugin;
use craft\elements\db\UserQuery;
use craft\elements\User;
use craft\events\CancelableEvent;
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\models\Structure;
use craft\services\Elements;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use flipbox\craft\ember\modules\LoggerTrait;
use flipbox\craft\initiatives\behaviors\InitiativeAttributesToUserQueryBehavior;
use flipbox\craft\initiatives\behaviors\UserInitiativesBehavior;
use flipbox\craft\initiatives\elements\Initiative;
use flipbox\craft\initiatives\fields\Initiative as InitiativeField;
use flipbox\craft\initiatives\web\twig\Variables;
use yii\base\Event;
use yii\db\Exception;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method models\Settings getSettings()
 */
class Initiatives extends Plugin
{
    use LoggerTrait;

    public static $category = 'initiatives';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Modules
        $this->setModules([
            'cp' => cp\Cp::class
        ]);

        // Fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = InitiativeField::class;
            }
        );

        // Element
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = Initiative::class;
            }
        );

        // User Query (attach behavior)
        Event::on(
            UserQuery::class,
            UserQuery::EVENT_INIT,
            function (Event $e) {
                /** @var UserQuery $query */
                $query = $e->sender;
                $query->attachBehaviors([
                    'initiative' => InitiativeAttributesToUserQueryBehavior::class
                ]);
            }
        );

        // User Query (prepare)
        Event::on(
            UserQuery::class,
            UserQuery::EVENT_AFTER_PREPARE,
            function (CancelableEvent $e) {
                /** @var UserQuery $query */
                $query = $e->sender;

                /** @var InitiativeAttributesToUserQueryBehavior $behavior */
                if (null !== ($behavior = $query->getBehavior('initiative'))) {
                    $behavior->applyInitiativeParams($query);
                }
            }
        );

        // User Query (attach behavior)
        Event::on(
            User::class,
            User::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $e) {
                $e->behaviors['initiatives'] = UserInitiativesBehavior::class;
            }
        );

        // Twig variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('initiatives', Variables::class);
            }
        );

        // Site templates
        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $e) {
                $e->roots['initiatives'] = Craft::$app->getPath()->getVendorPath() .
                    '/flipboxfactory/craft-initiatives/src/templates/_site';
            }
        );

        // CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            [self::class, 'onRegisterCpUrlRules']
        );
    }


    /*******************************************
     * SETTINGS
     *******************************************/

    /**
     * @inheritdoc
     * @return models\Settings
     */
    protected function createSettingsModel()
    {
        return new models\Settings();
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse()
    {
        Craft::$app->getResponse()->redirect(
            UrlHelper::cpUrl('initiatives/settings')
        );

        Craft::$app->end();
    }

    /*******************************************
     * INSTALL / UNINSTALL
     *******************************************/

    /**
     * @inheritdoc
     */
    public function afterInstall()
    {
        // Create default field layout
        $structure = new Structure();

        if (false === Craft::$app->getStructures()->saveStructure($structure)) {
            throw new Exception("Unable to save structure");
        }

        Craft::$app->getPlugins()->savePluginSettings(
            $this,
            [
                'structureId' => $structure->id
            ]
        );

        // Do parent
        parent::afterInstall();
    }

    /**
     * @inheritdoc
     */
    public function beforeUninstall(): bool
    {
        if (null !== ($structureId = $this->getSettings()->structureId)) {
            Craft::$app->getStructures()->deleteStructureById($structureId);
        }

        // Do parent
        return parent::beforeUninstall();
    }


    /*******************************************
     * NAV
     *******************************************/

    /**
     * @inheritdoc
     */
    public function getCpNavItem()
    {
        return array_merge(
            parent::getCpNavItem(),
            [
                'subnav' => [
                    'initiatives.elements' => [
                        'label' => static::t('Initiatives'),
                        'url' => 'initiatives'
                    ],
                    'initiatives.settings' => [
                        'label' => static::t('Settings'),
                        'url' => 'initiatives/settings'
                    ]
                ]
            ]
        );
    }


    /*******************************************
     * EVENTS
     *******************************************/

    /**
     * @param RegisterUrlRulesEvent $event
     */
    public static function onRegisterCpUrlRules(RegisterUrlRulesEvent $event)
    {
        $event->rules = array_merge(
            $event->rules,
            [
                'initiatives/settings' => 'initiatives/cp/settings/view/initiatives/index',
                'initiatives/settings/initiatives' => 'initiatives/cp/settings/view/initiatives/index',
                'initiatives/settings/initiatives/new' => 'initiatives/cp/settings/view/initiatives/upsert',
                'initiatives/settings/initiatives/<identifier:\d+>' => 'initiatives/cp/settings/view/initiatives/upsert',

                'initiatives' => 'initiatives/cp/view/initiatives/index',
                'initiatives/<identifier:\d+>' => 'initiatives/cp/view/initiatives/upsert'
            ]
        );
    }

    /**
     * Translates a message to the specified language.
     *
     * This is a shortcut method of [[\Craft::t()]].
     *
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding
     * placeholders in...craft-link/src/web/assets/settings/dist/LinkConfiguration.min.js
     * the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     * @return string the translated message.
     */
    public static function t($message, $params = [], $language = null)
    {
        return Craft::t(static::$category, $message, $params, $language);
    }
}
