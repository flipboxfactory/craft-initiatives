<?php

namespace flipbox\craft\initiatives\actions\initiatives;

use craft\base\ElementInterface;
use flipbox\craft\ember\actions\elements\CreateElement;
use flipbox\craft\initiatives\elements\Initiative;
use yii\base\BaseObject;

class CreateInitiative extends CreateElement
{
    use PopulateInitiativeTrait;

    /**
     * @inheritdoc
     * @return Initiative
     */
    protected function newElement(array $config = []): ElementInterface
    {
        return new Initiative($config);
    }

    /**
     * @inheritdoc
     */
    protected function populate(BaseObject $object): BaseObject
    {
        /** @var Initiative $object */
        parent::populate($object);
        $this->populateParent($object);
        $this->populateFields($object);

        return $object;
    }

    /**
     * @param ElementInterface $element
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    protected function performAction(ElementInterface $element): bool
    {
        return parent::performAction($element);
    }
}
