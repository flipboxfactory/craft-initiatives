<?php

namespace flipbox\craft\initiatives\actions\initiatives;

use Craft;
use flipbox\craft\initiatives\elements\Initiative;

trait PopulateInitiativeTrait
{
    /**
     * @return array
     */
    public function validBodyParams(): array
    {
        return [
            'slug',
            'title',
            'enabled'
        ];
    }

    /**
     * @param Initiative $initiative
     * @return Initiative
     */
    protected function populateFields(Initiative $initiative): Initiative
    {
        $initiative->setFieldValuesFromRequest(
            Craft::$app->getRequest()->getParam('fieldsLocation', 'fields')
        );
        return $initiative;
    }

    /**
     * @param Initiative $initiative
     * @return Initiative
     */
    protected function populateParent(Initiative $initiative): Initiative
    {
        $initiative->newParentId = $this->parentIdParam();
        return $initiative;
    }

    /**
     * @return string
     */
    private function parentIdParam()
    {
        if (($parentId = Craft::$app->getRequest()->getBodyParam('parentId')) !== null) {
            if (is_array($parentId)) {
                $parentId = reset($parentId) ?: '';
            }
        }

        return $parentId ?: '';
    }
}
