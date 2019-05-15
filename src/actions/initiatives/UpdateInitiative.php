<?php

namespace flipbox\craft\initiatives\actions\initiatives;

use flipbox\craft\ember\actions\elements\UpdateElement;
use flipbox\craft\initiatives\elements\Initiative;
use yii\base\BaseObject;

class UpdateInitiative extends UpdateElement
{
    use PopulateInitiativeTrait;

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
}