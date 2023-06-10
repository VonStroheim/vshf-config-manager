<?php

namespace VSHF\Config;

/**
 * Represents a dependency configuration for settings. Dependencies define conditions that
 * must be met in order for a setting to be valid or resolved.
 */
class Dependency
{

    /**
     * @var array Holds the dependency conditions.
     */
    private $conditions = [];

    /**
     * @var string Indicates the group logic for the conditions ('AND' or 'OR').
     */
    private $groupLogic = '';

    /**
     * @var Dependency|null The parent dependency, used for grouping conditions.
     */
    private $parentDependency;

    /**
     * Sets the parent dependency for grouping conditions.
     *
     * @param Dependency $parentDependency The parent dependency instance.
     *
     * @return void
     */
    private function setParentDependency(Dependency $parentDependency): void
    {
        $this->parentDependency = $parentDependency;
    }

    /**
     * Creates a new DependencyCondition instance for the specified setting and context.
     *
     * @param string $settingId The ID of the setting.
     * @param string $context   The context of the setting.
     *
     * @return DependencyCondition The DependencyCondition instance.
     */
    public function on(string $settingId, string $context = Config::CONTEXT_APP): DependencyCondition
    {
        return new DependencyCondition($this, $settingId, $context);
    }

    /**
     * Creates a new DependencyCondition instance with 'AND' logic for the specified setting and context.
     *
     * @param string $settingId The ID of the setting.
     * @param string $context   The context of the setting.
     *
     * @return DependencyCondition The DependencyCondition instance.
     *
     * @throws \BadMethodCallException If the group logic is not 'AND'.
     */
    public function and(string $settingId, string $context = Config::CONTEXT_APP): DependencyCondition
    {
        if ($this->groupLogic === '') {
            $this->groupLogic = 'AND';
        }
        if ($this->groupLogic !== 'AND') {
            throw new \BadMethodCallException('Called and() when the group logic is OR');
        }

        return new DependencyCondition($this, $settingId, $context);
    }

    /**
     * Creates a new DependencyCondition instance with 'OR' logic for the specified setting and context.
     *
     * @param string $settingId The ID of the setting.
     * @param string $context   The context of the setting.
     *
     * @return DependencyCondition The DependencyCondition instance.
     *
     * @throws \BadMethodCallException If the group logic is not 'OR'.
     */
    public function or(string $settingId, string $context = Config::CONTEXT_APP): DependencyCondition
    {
        if ($this->groupLogic === '') {
            $this->groupLogic = 'OR';
        }
        if ($this->groupLogic !== 'OR') {
            throw new \BadMethodCallException('Called or() when the group logic is AND');
        }

        return new DependencyCondition($this, $settingId, $context);
    }

    /**
     * Creates a new group dependency with 'AND' logic.
     *
     * @return Dependency The created group dependency instance.
     *
     * @throws \BadMethodCallException If the group logic is not 'AND'.
     */
    public function andGroup(): Dependency
    {
        if ($this->groupLogic === '') {
            $this->groupLogic = 'AND';
        }
        if ($this->groupLogic !== 'AND') {
            throw new \BadMethodCallException('Called andGroup() when the group logic is OR');
        }

        $groupDependency = new self();
        $groupDependency->setParentDependency($this);

        return $groupDependency;
    }

    /**
     * Creates a new group dependency with 'OR' logic.
     *
     * @return Dependency The created group dependency instance.
     *
     * @throws \BadMethodCallException If the group logic is not 'OR'.
     */
    public function orGroup(): Dependency
    {
        if ($this->groupLogic === '') {
            $this->groupLogic = 'OR';
        }
        if ($this->groupLogic !== 'OR') {
            throw new \BadMethodCallException('Called orGroup() when the group logic is AND');
        }

        $groupDependency = new self();
        $groupDependency->setParentDependency($this);

        return $groupDependency;
    }

    /**
     * Ends the current group dependency and returns to the parent dependency.
     *
     * @return Dependency The parent dependency instance.
     *
     * @throws \BadMethodCallException If group() was not called before endGroup().
     */
    public function endGroup(): Dependency
    {
        if (NULL === $this->parentDependency) {
            throw new \BadMethodCallException('Called endGroup() without calling group() first');
        }

        $this->parentDependency->addCondition($this);

        return $this->parentDependency;
    }

    /**
     * Adds a condition to the dependency.
     *
     * @param self | DependencyCondition $condition The condition to add.
     *
     * @return void
     */
    public function addCondition($condition): void
    {
        $this->conditions[] = $condition;
    }

    /**
     * Returns the conditions of the dependency.
     *
     * @return array The array of conditions.
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * Returns the group logic of the dependency.
     *
     * @return string The group logic ('AND' or 'OR').
     */
    public function getGroupLogic(): string
    {
        if ($this->groupLogic === '') {
            return 'AND';
        }

        return $this->groupLogic;
    }

}