<?php

namespace VSHF\Config\Tests;

use VSHF\Config\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private $observer;

    private $propertyObserver;

    /**
     * @return void
     * @covers \VSHF\Config\Config::hydrate
     * @covers \VSHF\Config\Config::getAllSettingsByContextRaw
     */
    public function testHydrate(): void
    {
        $cfg = new Config();

        $cfg->hydrate(['settingId_1' => 'settingValue_1']);
        $this->assertArrayHasKey('settingId_1', $cfg->getAllSettingsByContextRaw());
        $this->assertArrayNotHasKey('settingId_2', $cfg->getAllSettingsByContextRaw());
        $this->assertCount(1, $cfg->getAllSettingsByContextRaw());

        $cfg->hydrate(['settingId_2' => 'settingValue_2'], 'newContext');
        $this->assertArrayHasKey('settingId_2', $cfg->getAllSettingsByContextRaw('newContext'));
        $this->assertArrayNotHasKey('settingId_1', $cfg->getAllSettingsByContextRaw('newContext'));
        $this->assertCount(1, $cfg->getAllSettingsByContextRaw('newContext'));
    }

    /**
     * @return void
     * @covers \VSHF\Config\Config::hydrateResource
     * @covers \VSHF\Config\Config::getAllPropertiesByContextRaw
     */
    public function testHydrateResource(): void
    {
        $cfg = new Config();

        $cfg->hydrateResource(['property_1' => 'value_1'], 'services', 'srv_1');

        $resourceProps = $cfg->getAllPropertiesByContextRaw('services');

        $this->assertArrayHasKey('srv_1', $resourceProps);
        $this->assertArrayNotHasKey('srv_2', $resourceProps);
        $this->assertCount(1, $resourceProps);

        $this->assertArrayHasKey('property_1', $resourceProps['srv_1']);
        $this->assertArrayNotHasKey('property_2', $resourceProps['srv_1']);
        $this->assertCount(1, $resourceProps['srv_1']);
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
     * @covers \VSHF\Config\Config::registerPropertyObserver
     * @covers \VSHF\Config\Config::getProperty
     * @covers \VSHF\Config\Config::saveProperty
     */
    public function testSaveProperty(): void
    {
        $this->propertyObserver->allows('validate')->andReturnTrue();
        $this->propertyObserver->allows('dependencies')->andReturn(NULL);

        $cfg = new Config();
        $cfg->hydrateResource(['property_1' => 'value_1'], 'services', 'srv_1');
        $cfg->registerPropertyObserver('property_1', get_class($this->propertyObserver), 'services');

        $this->assertEquals('value_1', $cfg->getProperty('property_1', 'services', 'srv_1'));

        $cfg->saveProperty('property_1', 'newSettingValue', 'services', 'srv_1');
        $this->assertEquals('newSettingValue', $cfg->getProperty('property_1', 'services', 'srv_1'));
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
     * @covers \VSHF\Config\Config::registerPropertyObserver
     * @covers \VSHF\Config\Config::getProperty
     */
    public function testPropertyObserver(): void
    {
        $cfg = new Config();
        $cfg->hydrateResource(['property_1' => 'value_1'], 'services', 'srv_1');

        $cfg->registerPropertyObserver('property_1', get_class($this->propertyObserver), 'services');

        $this->propertyObserver->allows('validate')->andReturnTrue();
        $this->propertyObserver->allows('dependencies')->andReturn(NULL);

        $value = $cfg->getProperty('property_1', 'services', 'srv_1');
        $this->assertEquals('value_1', $value);
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
     * @covers \VSHF\Config\Config::registerPropertyObserver
     * @covers \VSHF\Config\Config::getProperty
     */
    public function testPropertyObserverNonValidating(): void
    {
        $cfg = new Config();
        $cfg->hydrateResource(['property_1' => 'value_1'], 'services', 'srv_1');

        $cfg->registerPropertyObserver('property_1', get_class($this->propertyObserver), 'services');

        $this->propertyObserver->allows('validate')->andReturnFalse();
        $this->propertyObserver->allows('dependencies')->andReturn(NULL);

        $this->expectException(\UnexpectedValueException::class);

        $cfg->getProperty('property_1', 'services', 'srv_1');
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
     * @covers \VSHF\Config\Config::registerPropertyObserver
     * @covers \VSHF\Config\Config::hydrateResource
     * @covers \VSHF\Config\Config::getProperty
     */
    public function testPropertyObserverDefault(): void
    {
        $this->propertyObserver->allows('validate')->andReturnTrue();
        $this->propertyObserver->allows('dependencies')->andReturn(NULL);

        $cfg = new Config();
        $cfg->registerPropertyObserver('property_1', get_class($this->propertyObserver), 'services');

        $cfg->hydrateResource(['property_1' => 'value_1'], 'services', 'srv_1');
        $this->assertEquals('value_1', $cfg->getProperty('property_1', 'services', 'srv_1'));

        $cfg->hydrateResource(['property_2' => 'value_2'], 'services', 'srv_1');
        $this->assertEquals('defaultValue', $cfg->getProperty('property_1', 'services', 'srv_1'));
    }

    /**
     * @return void
     * @covers \VSHF\Config\Config::__construct
     * @covers \VSHF\Config\Config::getAllSettingsRaw
     * @covers \VSHF\Config\Config::getAllSettingsByContextRaw
     */
    public function test__construct(): void
    {
        $cfg = new Config();
        $this->assertIsArray($cfg->getAllSettingsRaw());
        $this->assertIsArray($cfg->getAllSettingsByContextRaw());
        $this->assertIsArray($cfg->getAllSettingsByContextRaw('unknownContext'));

        $cfg = new Config(['settingId' => 'settingValue']);
        $this->assertIsArray($cfg->getAllSettingsRaw());
        $this->assertIsArray($cfg->getAllSettingsByContextRaw());
        $this->assertIsArray($cfg->getAllSettingsByContextRaw('unknownContext'));

        $this->assertArrayHasKey('settingId', $cfg->getAllSettingsByContextRaw());
        $this->assertArrayNotHasKey('settingId', $cfg->getAllSettingsByContextRaw('unknownContext'));
    }

    public function setUp(): void
    {
        $this->observer = \Mockery::mock('overload:VSHF\Config\ObserverInterface');
        $this->observer->allows('default')->andReturn('defaultValue');
        $this->observer->allows('onGet');
        $this->observer->allows('onSave');
        $this->observer->allows('onBeforeGet');
        $this->observer
            ->allows('sanitize')
            ->with(\Mockery::type('string'))
            ->andReturnArg(0);

        $this->propertyObserver = \Mockery::mock('overload:VSHF\Config\PropertyObserverInterface');
        $this->propertyObserver->allows('default')->andReturn('defaultValue');
        $this->propertyObserver->allows('onGet');
        $this->propertyObserver->allows('onBeforeGet');
        $this->propertyObserver->allows('onSave');
        $this->propertyObserver
            ->allows('sanitize')
            ->with(\Mockery::type('string'), \Mockery::type('string'))
            ->andReturnArg(0);
    }

    public function tearDown(): void
    {
        \Mockery::close();
    }
}
