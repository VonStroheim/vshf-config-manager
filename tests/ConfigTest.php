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
     */
    public function testHydrate(): void
    {
        $cfg = new Config();

        $cfg->hydrate(['settingId_1' => 'settingValue_1']);
        $this->assertArrayHasKey('settingId_1', $cfg->getAllByContext());
        $this->assertArrayNotHasKey('settingId_2', $cfg->getAllByContext());
        $this->assertCount(1, $cfg->getAllByContext());

        $cfg->hydrate(['settingId_2' => 'settingValue_2'], 'newContext');
        $this->assertArrayHasKey('settingId_2', $cfg->getAllByContext('newContext'));
        $this->assertArrayNotHasKey('settingId_1', $cfg->getAllByContext('newContext'));
        $this->assertCount(1, $cfg->getAllByContext('newContext'));
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
     */
    public function test__construct(): void
    {
        $cfg = new Config();
        $this->assertIsArray($cfg->getAll());
        $this->assertIsArray($cfg->getAllByContext());
        $this->assertIsArray($cfg->getAllByContext('unknownContext'));

        $cfg = new Config(['settingId' => 'settingValue']);
        $this->assertIsArray($cfg->getAll());
        $this->assertIsArray($cfg->getAllByContext());
        $this->assertIsArray($cfg->getAllByContext('unknownContext'));

        $this->assertArrayHasKey('settingId', $cfg->getAllByContext());
        $this->assertArrayNotHasKey('settingId', $cfg->getAllByContext('unknownContext'));
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
