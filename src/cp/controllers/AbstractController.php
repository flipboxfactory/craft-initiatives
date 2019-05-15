<?php

namespace flipbox\craft\initiatives\cp\controllers;

use craft\helpers\ArrayHelper;
use flipbox\craft\ember\filters\FlashMessageFilter;
use flipbox\craft\ember\filters\ModelErrorFilter;
use flipbox\craft\ember\filters\RedirectFilter;
use flipbox\craft\initiatives\cp\Cp;

/**
 * @property Cp $module
 */
abstract class AbstractController extends \flipbox\craft\ember\controllers\AbstractController
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'redirect' => [
                    'class' => RedirectFilter::class
                ],
                'flash' => [
                    'class' => FlashMessageFilter::class
                ],
                'error' => [
                    'class' => ModelErrorFilter::class
                ]
            ]
        );
    }
}
