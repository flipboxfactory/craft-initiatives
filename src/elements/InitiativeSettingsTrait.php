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

trait InitiativeSettingsTrait
{
    /**
     * @var InitiativeSettings
     */
    private $settings;

    /**
     * @param array $settings
     * @return $this
     */
    public function setSettings($settings = [])
    {
        $this->settings = $this->resolveSettings($settings);
        return $this;
    }

    /**
     * @param $settings
     * @return InitiativeSettingsInterface
     */
    protected function resolveSettings($settings): InitiativeSettingsInterface
    {
        if ($settings instanceof InitiativeSettingsInterface) {
            return $settings;
        }

        if (is_string($settings)) {
            $settings = Json::decodeIfJson($settings);
        };

        if (!is_array($settings)) {
            $settings = ArrayHelper::toArray($settings, [], false);
        }

        try {
            /** @var InitiativeSettingsInterface $object */
            $object = ObjectHelper::create(array_merge(
                [
                    'class' => InitiativeSettings::class,
                ],
                $settings
            ), InitiativeSettingsInterface::class);
        } catch (\Throwable $e) {
            $object = new InitiativeSettings();
        }

        return $object;
    }

    /**
     * @return InitiativeSettings
     */
    public function getSettings(): InitiativeSettings
    {
        if (!$this->settings instanceof InitiativeSettings) {
            $this->settings = $this->resolveSettings($this->settings);
        }

        return $this->settings;
    }
}
