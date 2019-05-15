<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/organization/license
 * @link       https://www.flipboxfactory.com/software/organization/
 */

namespace flipbox\craft\initiatives\cp\controllers;

use Craft;
use craft\controllers\ElementIndexesController;
use craft\elements\User;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterElementHtmlAttributesEvent;
use craft\events\RegisterElementSourcesEvent;
use craft\helpers\StringHelper;
use flipbox\craft\initiatives\elements\actions\RemoveTargets;
use flipbox\craft\initiatives\records\UserAssociation;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UserIndexesController extends ElementIndexesController
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        Event::on(
            User::class,
            User::EVENT_REGISTER_ACTIONS,
            function (RegisterElementActionsEvent $event) {
                $event->actions = [
                    [
                        'type' => RemoveTargets::class,
                        'initiative' => $event->data['initiative'] ?? null
                    ]
                ];
            },
            [
                'initiative' => $this->getInitiativeIdFromRequest()
            ]
        );

        // Custom sources
        Event::on(
            User::class,
            User::EVENT_REGISTER_SOURCES,
            function (RegisterElementSourcesEvent $event) {
                if ($event->context === 'index') {
                    $event->sources = array_merge(
                        $event->sources,
                        $this->getCustomSources()
                    );
                }
            }
        );

        // Add 'initiative' on the user html element
        Event::on(
            User::class,
            User::EVENT_REGISTER_HTML_ATTRIBUTES,
            function (RegisterElementHtmlAttributesEvent $event) {
                $event->htmlAttributes['data-initiative'] = $event->data['initiative'] ?? null;
            },
            [
                'initiative' => $this->getInitiativeIdFromRequest()
            ]
        );

        parent::init();
    }

    /**
     * @param string $context
     * @return array
     */
    protected function getCustomSources(string $context = 'index'): array
    {
        $sources = [];

        if ($context === 'index') {
            $sources[] = [
                'heading' => "Initiative Status"
            ];

            $statuses = [
                UserAssociation::STATUS_COMPLETE,
                UserAssociation::STATUS_ERROR,
                UserAssociation::STATUS_ACTIVE
            ];

            foreach ($statuses as $status) {
                $sources[] = [
                    'key' => 'targetStatus:' . $status,
                    'label' => Craft::t('initiatives', StringHelper::toTitleCase($status)),
                    'criteria' => [
                        'initiative' => [
                            'targetStatus' => $status
                        ]
                    ]
                ];
            }
        }

        return $sources;
    }

    /**
     * @return mixed
     */
    private function getInitiativeIdFromRequest()
    {
        return Craft::$app->getRequest()->getParam('initiative');
    }
}
