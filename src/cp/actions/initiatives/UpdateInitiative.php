<?php

namespace flipbox\craft\initiatives\cp\actions\initiatives;


use craft\base\ElementInterface;
use flipbox\craft\ember\actions\elements\UpdateElement;
use flipbox\craft\initiatives\elements\Initiative;

class UpdateInitiative extends UpdateElement
{
    use SaveSettingsTrait;

    /**
     * @param $initiative
     * @return mixed|null|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function run($initiative)
    {
        return parent::run($initiative);
    }

    /**
     * @param string|int $identifier
     * @return mixed|null
     */
    protected function find($identifier)
    {
        return $this->findById($identifier);
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
        return $this->saveSettings($element);
    }
}
