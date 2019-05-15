<?php

namespace flipbox\craft\initiatives\actions\initiatives;

use flipbox\craft\ember\actions\elements\ViewElement;
use flipbox\craft\initiatives\elements\Initiative;

class RenderInitiative extends ViewElement
{
    /**
     * @param Initiative $initiative
     * @return \craft\base\ElementInterface|mixed|string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    protected function runInternal(Initiative $initiative)
    {
        // Check access
        if (($access = $this->checkAccess($initiative)) !== true) {
            return $access;
        }

        return $initiative->renderHtml();
    }
}
