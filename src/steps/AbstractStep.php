<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/initiatives/blob/master/LICENSE.md
 * @link       https://github.com/flipboxfactory/initiatives
 */

namespace flipbox\craft\initiatives\steps;

use craft\base\Model;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use flipbox\craft\initiatives\Initiatives;
use flipbox\craft\initiatives\initiatives\MultiStepInitiativeInterface;
use yii\base\Exception;

abstract class AbstractStep extends Model implements StepInterface
{
    /**
     * @var MultiStepInitiativeInterface
     */
    private $initiative;

    /**
     * @var string
     */
    protected static $name;

    /**
     * @var string
     */
    protected static $identifier;

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        if (!is_array($config)) {
            $config = ArrayHelper::toArray($config, [], false);
        }

        $this->initiative = ArrayHelper::remove($config, 'initiative');

        parent::__construct($config);
    }

    /**
     * @return bool
     */
    public function hasUri(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->initiative === null) {
            throw new Exception("Initiative not set.");
        }
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return (string)static::$identifier;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return (string)static::$name;
    }

    /**
     * @inheritdoc
     */
    public function getInitiative(): MultiStepInitiativeInterface
    {
        return $this->initiative;
    }

    /**
     * @inheritdoc
     */
    public function setInitiative(MultiStepInitiativeInterface $initiative)
    {
        $this->initiative = $initiative;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function complete(User $user, $runValidation = true): bool
    {
        return $this->advanceToNextStep($user);
    }

    /**
     * @param User $user
     * @return bool
     * @throws \Exception
     * @throws \flipbox\ember\exceptions\ObjectNotFoundException
     */
    protected function advanceToNextStep(User $user)
    {
        $nextKey = Initiatives::getInstance()->getStep()->resolveNextKey(
            $this->getInitiative(),
            $user,
            $this->getIdentifier()
        );

        $target = Initiatives::getInstance()->getTarget()->get(
            $this->getInitiative(),
            $user
        );

        $target->step = $nextKey;

        return Initiatives::getInstance()->getTarget()->save($target);
    }

    /**
     * @inheritdoc
     */
    public function renderHtml(User $user): string
    {
        return '';
    }
}
