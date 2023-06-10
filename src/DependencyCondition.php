<?php

namespace VSHF\Config;

/**
 *  Represents a condition within a dependency. It defines the setting ID, context, operator, and value to be used in
 *  the condition evaluation.
 */
class DependencyCondition
{
    public const EQUAL     = "=";
    public const NOT_EQUAL = "!=";
    public const TRUTHY    = "TRUTHY";
    public const FALSY     = "FALSY";
    public const IS_EMPTY  = "EMPTY";
    public const NOT_EMPTY = "!EMPTY";
    public const IN        = "IN";
    public const NOT_IN    = "!IN";

    /**
     * @var string
     */
    private $settingId;

    /**
     * @var string
     */
    private $context;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var Dependency
     */
    private $parentDependency;

    /**
     * DependencyCondition constructor.
     *
     * @param Dependency $parentDependency The parent dependency to which this condition belongs.
     * @param string     $settingId        The setting ID for the condition.
     * @param string     $context          The context in which the setting ID is defined.
     */
    public function __construct(Dependency $parentDependency, string $settingId, string $context = Config::CONTEXT_APP)
    {
        $this->settingId        = $settingId;
        $this->context          = $context;
        $this->parentDependency = $parentDependency;
    }

    /**
     * Verifies whether the provided value satisfies the condition.
     *
     * @param mixed $value The value to be verified against the condition.
     *
     * @return bool Returns true if the value satisfies the condition, false otherwise.
     */
    public function verified($value): bool
    {
        switch ($this->operator) {
            case self::EQUAL:
                return $value === $this->value;
            case self::NOT_EQUAL:
                return $value !== $this->value;
            case self::TRUTHY:
                return (bool)$value;
            case self::FALSY:
                return !$value;
            case self::IS_EMPTY:
                return empty($value);
            case self::NOT_EMPTY:
                return !empty($value);
            case self::IN:
                return in_array($value, $this->value, TRUE);
            case self::NOT_IN:
                return !in_array($value, $this->value, TRUE);
            default:
                return TRUE;
        }
    }

    /**
     * Returns the setting ID associated with this condition.
     *
     * @return string The setting ID.
     */
    public function getSettingId(): string
    {
        return $this->settingId;
    }

    /**
     * Returns the context associated with this condition.
     *
     * @return string The context.
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * Sets the condition to check for equality with the provided value.
     *
     * @param mixed $value The value to compare against.
     *
     * @return Dependency The parent dependency with the condition added.
     */
    public function beingEqualTo($value): Dependency
    {
        $this->operator = self::EQUAL;
        $this->value    = $value;

        return $this->addSelfToDependencyAndReturnIt();
    }

    /**
     * Sets the condition to check for inequality with the provided value.
     *
     * @param mixed $value The value to compare against.
     *
     * @return Dependency The parent dependency with the condition added.
     */
    public function beingNotEqualTo($value): Dependency
    {
        $this->operator = self::NOT_EQUAL;
        $this->value    = $value;

        return $this->addSelfToDependencyAndReturnIt();
    }

    /**
     * Sets the condition to check for truthiness.
     *
     * @return Dependency The parent dependency with the condition added.
     */
    public function beingTruthy(): Dependency
    {
        $this->operator = self::TRUTHY;

        return $this->addSelfToDependencyAndReturnIt();
    }

    /**
     * Sets the condition to check for falsiness.
     *
     * @return Dependency The parent dependency with the condition added.
     */
    public function beingFalsy(): Dependency
    {
        $this->operator = self::FALSY;

        return $this->addSelfToDependencyAndReturnIt();
    }

    /**
     * Sets the condition to check if the value is in the provided array.
     *
     * @param array $values The array of values to check against.
     *
     * @return Dependency The parent dependency with the condition added.
     */
    public function beingIn(array $values): Dependency
    {
        $this->operator = self::IN;
        $this->value    = $values;

        return $this->addSelfToDependencyAndReturnIt();
    }

    /**
     * Sets the condition to check if the value is not in the provided array.
     *
     * @param array $values The array of values to check against.
     *
     * @return Dependency The parent dependency with the condition added.
     */
    public function beingNotIn(array $values): Dependency
    {
        $this->operator = self::NOT_IN;
        $this->value    = $values;

        return $this->addSelfToDependencyAndReturnIt();
    }

    /**
     * Sets the condition to check if the value is empty.
     *
     * @return Dependency The parent dependency with the condition added.
     */
    public function beingEmpty(): Dependency
    {
        $this->operator = self::IS_EMPTY;

        return $this->addSelfToDependencyAndReturnIt();
    }

    /**
     * Sets the condition to check if the value is not empty.
     *
     * @return Dependency The parent dependency with the condition added.
     */
    public function beingNotEmpty(): Dependency
    {
        $this->operator = self::NOT_EMPTY;

        return $this->addSelfToDependencyAndReturnIt();
    }

    /**
     * Adds the current condition to the parent dependency and returns the parent dependency.
     *
     * @return Dependency The parent dependency.
     */
    private function addSelfToDependencyAndReturnIt(): Dependency
    {
        $this->parentDependency->addCondition($this);

        return $this->parentDependency;
    }

}