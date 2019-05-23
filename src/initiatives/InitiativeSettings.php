<?php

namespace flipbox\craft\initiatives\initiatives;

use Craft;
use craft\elements\db\UserQuery;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\records\Element as ElementRecord;
use flipbox\craft\ember\helpers\ObjectHelper;
use flipbox\craft\ember\helpers\QueryHelper;
use flipbox\craft\initiatives\elements\Initiative;
use flipbox\craft\initiatives\Initiatives as InitiativesPlugin;
use flipbox\craft\initiatives\records\Initiative as InitiativeRecord;
use yii\base\Model;

class InitiativeSettings extends Model implements InitiativeSettingsInterface
{
    /**
     * @var string
     */
    public $template = 'initiatives';

    /**
     * @var string
     */
    public $route = [];

    /**
     * @var string
     */
    public $action = '';

    /**
     * @var string
     */
    public $targets = [];

    /**
     * @param Initiative $initiative
     * @return array|null
     */
    public function getRoute(Initiative $initiative)
    {
        if (null === ($override = $this->resolveRoute($this->route, $initiative))) {
            return null;
        }

        $route = [
            'templates/render',
            [
                'template' => $this->getTemplate(),
                'variables' => $this->getTemplateVariables($initiative)
            ]
        ];

        return $this->mergeRoutes($route, $override);
    }

    /**
     * @param array $route
     * @param array $override
     * @return array
     */
    private function mergeRoutes(array $route, array $override)
    {
        foreach ($override as $key => $row) {
            if (array_key_exists($key, $route)) {
                $value = $route[$key];
                if (is_array($row)) {
                    if (!is_array($value)) {
                        $value = [$value];
                    }

                    $row = ArrayHelper::merge($value, $row);
                }
            }

            $route[$key] = $row;
        }

        return $route;
    }

    /**
     * @param $route
     * @param Initiative $initiative
     * @return array|null
     */
    protected function resolveRoute($route, Initiative $initiative)
    {
        if ($route === null || $route === false) {
            return null;
        }

        if (is_callable($route)) {
            $route = $this->resolveRoute(
                call_user_func($route, $initiative),
                $initiative
            );
        }

        if (!is_array($route)) {
            $route = ArrayHelper::toArray($route);
        }

        return $route;
    }

    /**
     * @param Initiative $initiative
     * @return array
     */
    public function getTemplateVariables(Initiative $initiative): array
    {
        return [
            'initiative' => $initiative,
            'initiativeAction' => 'initiatives/initiatives/complete'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @inheritdoc
     */
    public function createAction(Initiative $initiative, User $user): ActionInterface
    {
        $config = $this->action;

        try {
            $class = ObjectHelper::getClassFromConfig($config, true);
            $action = new $class($initiative, $user, $config);
        } catch (\Throwable $e) {
            $action = $this->baseAction($initiative, $user);
        }

        if (!$action instanceof InitiativeAction) {
            return $this->baseAction($initiative, $user);
        }

        return $action;
    }

    /**
     * @param Initiative $initiative
     * @return UserQuery
     */
    public function getTargetQuery(Initiative $initiative): UserQuery
    {
        $query = $this->baseQuery($initiative);

        QueryHelper::configure(
            $query,
            $this->targets
        );

        return $query;
    }

    /**
     * @param Initiative $initiative
     * @return UserQuery
     */
    protected function baseQuery(Initiative $initiative): UserQuery
    {
        $query = User::find()
            ->andWhere(
                ['NOT IN', 'elements.id', $initiative->getUsers()->ids()]
            );

        return $query;
    }

    /**
     * @param Initiative $initiative
     * @param User $user
     * @return InitiativeAction
     */
    protected function baseAction(Initiative $initiative, User $user): InitiativeAction
    {
        return new InitiativeAction($initiative, $user);
    }

    /**
     * @param Initiative $initiative
     * @return bool
     * @throws \yii\db\Exception
     */
    public function save(Initiative $initiative): bool
    {
        if (!$record = InitiativeRecord::findOne([
            'id' => $initiative->getId()
        ])) {
            $record = new InitiativeRecord([
                'id' => $initiative->getId()
            ]);
        }

        $record->settings = array_merge(
            ['class' => get_class($this)],
            $this->serialize()
        );

        if (!$record->save()) {
            $this->addErrors($record->getErrors());

            InitiativesPlugin::error(
                Json::encode($this->getErrors()),
                __METHOD__
            );

            return false;
        }

        if (false !== ($dateUpdated = DateTimeHelper::toDateTime($record->dateUpdated))) {
            $initiative->dateUpdated = $dateUpdated;
        }

        if (false !== ($dateCreated = DateTimeHelper::toDateTime($record->dateCreated))) {
            $initiative->dateCreated = $dateCreated;
        }

        Craft::$app->getDb()->createCommand()
            ->update(
                ElementRecord::tableName(),
                ['fieldLayoutId' => $initiative->fieldLayoutId],
                ['id' => $initiative->id]
            )
            ->execute();

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function serialize(): array
    {
        return $this->toArray();
    }
}
