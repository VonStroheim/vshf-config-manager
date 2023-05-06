<?php

namespace VSHF\Config\Tests;

use VSHF\Config\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private $observer;

    /**
     * @return void
     * @covers \VSHF\Config\Config::hydrate
     * @covers \VSHF\Config\Config::getAllByContextRaw
     */
    public function testHydrate(): void
    {
        $cfg = new Config();

        $cfg->hydrate(['settingId_1' => 'settingValue_1']);
        $this->assertArrayHasKey('settingId_1', $cfg->getAllByContextRaw());
        $this->assertArrayNotHasKey('settingId_2', $cfg->getAllByContextRaw());
        $this->assertCount(1, $cfg->getAllByContextRaw());

        $cfg->hydrate(['settingId_2' => 'settingValue_2'], 'newContext');
        $this->assertArrayHasKey('settingId_2', $cfg->getAllByContextRaw('newContext'));
        $this->assertArrayNotHasKey('settingId_1', $cfg->getAllByContextRaw('newContext'));
        $this->assertCount(1, $cfg->getAllByContextRaw('newContext'));
    }

    /**
     * @return void
     * @covers \VSHF\Config\Config::registerObserver
     * @covers \VSHF\Config\Config::get
     * @covers \VSHF\Config\Config::save
     */
    public function testSave(): void
    {
        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn(NULL);

        $cfg = new Config(['settingId' => 'settingValue']);
        $cfg->registerObserver('settingId', get_class($this->observer));
        $this->assertEquals('settingValue', $cfg->get('settingId'));

        $cfg->save('settingId', 'newSettingValue');
        $this->assertEquals('newSettingValue', $cfg->get('settingId'));
    }

    /**
     * @return void
     * @covers \VSHF\Config\Config::registerObserver
     * @covers \VSHF\Config\Config::get
     */
    public function testObserver(): void
    {
        $cfg = new Config(['settingId' => 'settingValue']);
        $cfg->registerObserver('settingId', get_class($this->observer));

        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn(NULL);

        $value = $cfg->get('settingId');
        $this->assertEquals('settingValue', $value);
    }

    /**
     * @return void
     * @covers \VSHF\Config\Config::registerObserver
     * @covers \VSHF\Config\Config::get
     */
    public function testObserverNonValidating(): void
    {
        $cfg = new Config(['settingId' => 'settingValue']);
        $cfg->registerObserver('settingId', get_class($this->observer));

        $this->observer->allows('validate')->andReturnFalse();
        $this->observer->allows('dependencies')->andReturn(NULL);

        $this->expectException(\UnexpectedValueException::class);

        $cfg->get('settingId');
    }

    /**
     * @return void
     * @covers \VSHF\Config\Config::registerObserver
     * @covers \VSHF\Config\Config::get
     */
    public function testObserverDefault(): void
    {
        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn(NULL);

        $cfg = new Config(['settingId' => 'settingValue']);
        $cfg->registerObserver('settingId', get_class($this->observer));
        $this->assertEquals('settingValue', $cfg->get('settingId'));

        $cfg->hydrate([]);
        $this->assertEquals('defaultValue', $cfg->get('settingId'));
    }

    /**
     * @return void
     * @covers \VSHF\Config\Config::__construct
     * @covers \VSHF\Config\Config::getAllRaw
     * @covers \VSHF\Config\Config::getAllByContextRaw
     */
    public function test__construct(): void
    {
        $cfg = new Config();
        $this->assertIsArray($cfg->getAllRaw());
        $this->assertIsArray($cfg->getAllByContextRaw());
        $this->assertIsArray($cfg->getAllByContextRaw('unknownContext'));

        $cfg = new Config(['settingId' => 'settingValue']);
        $this->assertIsArray($cfg->getAllRaw());
        $this->assertIsArray($cfg->getAllByContextRaw());
        $this->assertIsArray($cfg->getAllByContextRaw('unknownContext'));

        $this->assertArrayHasKey('settingId', $cfg->getAllByContextRaw());
        $this->assertArrayNotHasKey('settingId', $cfg->getAllByContextRaw('unknownContext'));
    }

    public function setUp(): void
    {
        $this->observer = \Mockery::mock('overload:VSHF\Config\ObserverInterface');
        $this->observer->allows('default')->andReturn('defaultValue');
        $this->observer->allows('onGet');
        $this->observer->allows('onSave');
        $this->observer
            ->allows('sanitize')
            ->with(\Mockery::type('string'))
            ->andReturnArg(0);
    }

    public function tearDown(): void
    {
        \Mockery::close();
    }
}
