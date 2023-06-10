<?php

namespace VSHF\Config\Tests\dummy;

use VSHF\Config\Dependency;
use VSHF\Config\PropertyObserverInterface;

/**
 * Example of boolean setting
 */
class TestProperty implements PropertyObserverInterface
{
    public const ID = 'testProperty';

    public static function validate($value, $resourceId): bool
    {
        return TRUE;
    }

    public static function sanitize($value, $resourceId): bool
    {
        return (bool)$value;
    }

    public static function default($resourceId): bool
    {
        return TRUE;
    }

    public static function onSave($value, $resourceId): void
    {
        throw new \RuntimeException('onSave');
    }

    public static function onGet($value, $resourceId): void
    {
        throw new \RuntimeException('onGet');
    }

    public static function dependencies($resourceId): ?Dependency
    {
        return NULL;
    }

    public static function onBeforeGet($resourceId): void
    {
        // TODO: Implement onBeforeGet() method.
    }

    public static function onGetFilter($resourceId, $value)
    {
        return $value;
    }
}