<?php

namespace VSHF\Config\Tests;

use PHPUnit\Framework\TestCase;
use VSHF\Config\Config;
use VSHF\Config\Tests\dummy\TestSetting;

class ObserverTest extends TestCase
{
    /**
     * @return void
     * @covers
     */
    public function testObserverOnSaveCallback(): void
    {
        $cfg = new Config([TestSetting::ID => TestSetting::default()]);
        $cfg->registerObserver(TestSetting::ID, TestSetting::class);
        $this->expectExceptionMessage('onSave');
        $cfg->save(TestSetting::ID, FALSE);
    }

    /**
     * @return void
     * @covers
     */
    public function testObserverOnGetCallback(): void
    {
        $cfg = new Config([TestSetting::ID => TestSetting::default()]);
        $cfg->registerObserver(TestSetting::ID, TestSetting::class);
        $this->expectExceptionMessage('onGet');
        $cfg->get(TestSetting::ID);
    }

}
