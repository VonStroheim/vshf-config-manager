<?php

namespace VSHF\Config\Tests\dummy;

use VSHF\Config\Config;
use VSHF\Config\Dependency;
use VSHF\Config\ObserverInterface;

/**
 * Example of boolean setting
 */
class TestSetting implements ObserverInterface
{
    public const ID = 'testSetting';

    /**
     * For testing reasons
     *
     * @var Config|string
     */
    public static $cfgObj = NULL;

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
        if (!(static::$cfgObj instanceof Config)) {
            throw new \RuntimeException('onGet');
        }
    }

    public static function dependencies(): ?Dependency
    {
        return NULL;
    }

    public static function onBeforeGet(): void
    {
        if (static::$cfgObj instanceof Config) {
            static::$cfgObj->hydrate(['onBeforeGet' => 'hello']);
        } elseif (static::$cfgObj === 'onGet') {
            // nothing
        } else {
            throw new \RuntimeException('onBeforeGet');
        }
    }
}