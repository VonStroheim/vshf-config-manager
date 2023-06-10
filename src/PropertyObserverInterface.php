<?php

namespace VSHF\Config;

/**
 * Interface PropertyObserverInterface
 *
 *  Defines the contract for a property observer in the context of configuration values associated with a specific
 *  resource.
 */
interface PropertyObserverInterface
{
    /**
     * Validates the provided value for a specific resource.
     *
     * @param mixed $value      The value to be validated.
     * @param mixed $resourceId The ID of the associated resource.
     *
     * @return bool Returns true if the value is valid, false otherwise.
     */
    public static function validate($value, $resourceId): bool;

    /**
     * Sanitizes the provided value for a specific resource.
     *
     * @param mixed $value      The value to be sanitized.
     * @param mixed $resourceId The ID of the associated resource.
     *
     * @return mixed The sanitized value.
     */
    public static function sanitize($value, $resourceId);

    /**
     * Returns the default value for the observer associated with a specific resource.
     *
     * @param mixed $resourceId The ID of the associated resource.
     *
     * @return mixed The default value.
     */
    public static function default($resourceId);

    /**
     * Handles actions to be performed when the value associated with a specific resource is being saved.
     *
     * @param mixed $value      The value being saved.
     * @param mixed $resourceId The ID of the associated resource.
     *
     * @return void
     */
    public static function onSave($value, $resourceId): void;

    /**
     * Handles actions to be performed when the value associated with a specific resource is being retrieved.
     *
     * @param mixed $value      The value being retrieved.
     * @param mixed $resourceId The ID of the associated resource.
     *
     * @return void
     */
    public static function onGet($value, $resourceId): void;

    /**
     * Handles actions to be performed before retrieving the value associated with a specific resource.
     *
     * @param mixed $resourceId The ID of the associated resource.
     *
     * @return void
     */
    public static function onBeforeGet($resourceId): void;

    /**
     * Handles filters to be applied to the return value.
     *
     * @param mixed $resourceId The ID of the associated resource.
     * @param mixed $value      The value being retrieved.
     *
     * @return mixed The filtered value.
     */
    public static function onGetFilter($resourceId, $value);

    /**
     * Retrieves the dependency associated with the observer for a specific resource, if any.
     *
     * @param mixed $resourceId The ID of the associated resource.
     *
     * @return Dependency|null The dependency associated with the observer, or null if there is no dependency.
     */
    public static function dependencies($resourceId): ?Dependency;
}