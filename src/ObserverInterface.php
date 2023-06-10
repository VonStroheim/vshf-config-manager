<?php

namespace VSHF\Config;

/**
 * Interface ObserverInterface
 *
 *  Defines the contract for an observer in the context of configuration values.
 */
interface ObserverInterface
{
    /**
     * Validates the provided value.
     *
     * @param mixed $value The value to be validated.
     *
     * @return bool Returns true if the value is valid, false otherwise.
     */
    public static function validate($value): bool;

    /**
     * Sanitizes the provided value.
     *
     * @param mixed $value The value to be sanitized.
     *
     * @return mixed The sanitized value.
     */
    public static function sanitize($value);

    /**
     * Returns the default value for the observer.
     *
     * @return mixed The default value.
     */
    public static function default();

    /**
     * Handles actions to be performed when the value is being saved.
     *
     * @param mixed $value The value being saved.
     *
     * @return void
     */
    public static function onSave($value): void;

    /**
     * Handles actions to be performed when the value is being retrieved.
     *
     * @param mixed $value The value being retrieved.
     *
     * @return void
     */
    public static function onGet($value): void;

    /**
     * Handles actions to be performed before retrieving the value.
     *
     * @return void
     */
    public static function onBeforeGet(): void;

    /**
     * Handles filters to be applied to the return value.
     *
     * @param mixed $value The value being retrieved.
     *
     * @return mixed The filtered value.
     */
    public static function onGetFilter($value);

    /**
     * Retrieves the dependency associated with the observer, if any.
     *
     * @return Dependency|null The dependency associated with the observer, or null if there is no dependency.
     */
    public static function dependencies(): ?Dependency;
}