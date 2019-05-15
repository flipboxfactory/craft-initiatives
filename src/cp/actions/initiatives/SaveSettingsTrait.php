<?php

namespace flipbox\craft\initiatives\cp\actions\initiatives;

use Craft;
use flipbox\craft\initiatives\elements\Initiative;

trait SaveSettingsTrait
{
    /**
     * @param Initiative $element
     * @return bool
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    protected function saveSettings(Initiative $element): bool
    {
        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = get_class($element);
        $fieldLayout->id = $element->fieldLayoutId ?: null;

        Craft::$app->getFields()->saveLayout($fieldLayout);

        $element->fieldLayoutId = $fieldLayout->id;

        return $element->getSettings()->save($element);
    }
}
