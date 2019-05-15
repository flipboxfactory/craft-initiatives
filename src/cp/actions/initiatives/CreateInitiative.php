<?php

namespace flipbox\craft\initiatives\cp\actions\initiatives;

use Craft;
use craft\base\ElementInterface;
use flipbox\craft\ember\actions\elements\CreateElement;
use flipbox\craft\initiatives\elements\Initiative;

class CreateInitiative extends CreateElement
{
    use SaveSettingsTrait;

    /**
     * @inheritdoc
     * @return Initiative
     */
    protected function newElement(array $config = []): ElementInterface
    {
        return new Initiative($config);
    }

    /**
     * @return array
     */
    public function validBodyParams(): array
    {
        return [
            'slug',
            'title',
            'enabled',
            'settings'
        ];
    }

    /**
     * @inheritdoc
     * @param Initiative $element
     */
    protected function performAction(ElementInterface $element): bool
    {
        $element->setScenario(
            $element::SCENARIO_ESSENTIALS
        );

        if (!Craft::$app->getElements()->saveElement($element)) {
            return false;
        }

        return $this->saveSettings($element);
    }
}