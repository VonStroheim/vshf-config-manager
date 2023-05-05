<?php

namespace VSHF\Config\Tests\dummy;

use VSHF\Config\Dependency;
use VSHF\Config\ObserverInterface;

/**
 * Example of boolean setting
 */
class TestSetting implements ObserverInterface
{
    public const ID = 'testSetting';

    public static function validate($value): bool
    {
        return TRUE;
    }

    public static function sanitize($value): bool
    {
        return (bool)$value;
    }

    public static function default(): bool
    {
        return TRUE;
    }

    public static function onSave($value): void
    {
        throw new \RuntimeException('onSave');
    }

    public static function onGet($value): void
    {
        throw new \RuntimeException('onGet');
    }

    public static function dependencies(): ?Dependency
    {
        return NULL;
    }
}