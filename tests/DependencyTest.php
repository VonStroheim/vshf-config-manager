<?php

namespace VSHF\Config\Tests;

use VSHF\Config\Config;
use VSHF\Config\Dependency;
use PHPUnit\Framework\TestCase;

class DependencyTest extends TestCase
{
    public function setUp(): void
    {
        $this->observer = \Mockery::mock('overload:VSHF\Config\ObserverInterface');
        $this->observer->allows('default')->andReturn('defaultValue');
        $this->observer->allows('onGet');
        $this->observer->allows('onBeforeGet');
        $this->observer->allows('onGetFilter')->andReturnArg(0);
        $this->observer->allows('onSave');
        $this->observer
            ->allows('sanitize')
            ->with(\Mockery::type('string'))
            ->andReturnArg(0);
    }

    /**
     * @return void
     * @covers \VSHF\Config\Dependency::getGroupLogic
     * @covers \VSHF\Config\Dependency::on
     * @covers \VSHF\Config\Dependency::getConditions
     * @covers \VSHF\Config\DependencyCondition::beingEqualTo
     * @covers \VSHF\Config\DependencyCondition::verified
     * @covers \VSHF\Config\DependencyCondition::addSelfToDependencyAndReturnIt
     * @covers \VSHF\Config\DependencyCondition::getSettingId
     * @covers \VSHF\Config\DependencyCondition::getContext
     */
    public function testDependencyEqual(): void
    {
        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingEqualTo('testValue');

        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn($dependency);

        $cfg = new Config([
            'settingId'       => 'settingValue',
            'parentSettingId' => 'testValue'
        ]);

        $cfg->registerObserver('settingId', get_class($this->observer));
        $cfg->registerObserver('parentSettingId', get_class($this->observer));

        $this->assertEquals('settingValue', $cfg->get('settingId'), 'Dependency condition OK');

        $cfg->hydrate([
            'settingId'       => 'settingValue',
            'parentSettingId' => 'testValueDiffers'
        ]);
        $this->assertNull($cfg->get('settingId'), 'Dependency condition fails');
    }

    /**
     * @return void
     * @covers \VSHF\Config\Dependency::getGroupLogic
     * @covers \VSHF\Config\Dependency::on
     * @covers \VSHF\Config\Dependency::getConditions
     * @covers \VSHF\Config\DependencyCondition::beingNotEqualTo
     * @covers \VSHF\Config\DependencyCondition::verified
     * @covers \VSHF\Config\DependencyCondition::addSelfToDependencyAndReturnIt
     * @covers \VSHF\Config\DependencyCondition::getSettingId
     * @covers \VSHF\Config\DependencyCondition::getContext
     */
    public function testDependencyNotEqual(): void
    {
        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingNotEqualTo('testValue');

        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn($dependency);

        $cfg = new Config([
            'settingId'       => 'settingValue',
            'parentSettingId' => 'testValue'
        ]);

        $cfg->registerObserver('settingId', get_class($this->observer));
        $cfg->registerObserver('parentSettingId', get_class($this->observer));

        $this->assertNull($cfg->get('settingId'), 'Dependency condition fails');

        $cfg->hydrate([
            'settingId'       => 'settingValue',
            'parentSettingId' => 'testValueDiffers'
        ]);
        $this->assertEquals('settingValue', $cfg->get('settingId'), 'Dependency condition OK');
    }

    /**
     * @return void
     * @covers \VSHF\Config\Dependency::getGroupLogic
     * @covers \VSHF\Config\Dependency::on
     * @covers \VSHF\Config\Dependency::getConditions
     * @covers \VSHF\Config\DependencyCondition::beingTruthy
     * @covers \VSHF\Config\DependencyCondition::verified
     * @covers \VSHF\Config\DependencyCondition::addSelfToDependencyAndReturnIt
     * @covers \VSHF\Config\DependencyCondition::getSettingId
     * @covers \VSHF\Config\DependencyCondition::getContext
     */
    public function testDependencyTruthy(): void
    {
        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingTruthy();

        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn($dependency);

        $cfg = new Config([
            'settingId'       => 'settingValue',
            'parentSettingId' => '1'
        ]);

        $cfg->registerObserver('settingId', get_class($this->observer));
        $cfg->registerObserver('parentSettingId', get_class($this->observer));

        $this->assertEquals('settingValue', $cfg->get('settingId'), 'Dependency condition OK');

        $cfg->hydrate([
            'settingId'       => 'settingValue',
            'parentSettingId' => ''
        ]);
        $this->assertNull($cfg->get('settingId'), 'Dependency condition fails');
    }

    /**
     * @return void
     * @covers \VSHF\Config\Dependency::getGroupLogic
     * @covers \VSHF\Config\Dependency::on
     * @covers \VSHF\Config\Dependency::getConditions
     * @covers \VSHF\Config\DependencyCondition::beingFalsy
     * @covers \VSHF\Config\DependencyCondition::verified
     * @covers \VSHF\Config\DependencyCondition::addSelfToDependencyAndReturnIt
     * @covers \VSHF\Config\DependencyCondition::getSettingId
     * @covers \VSHF\Config\DependencyCondition::getContext
     */
    public function testDependencyFalsy(): void
    {
        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingFalsy();

        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn($dependency);

        $cfg = new Config([
            'settingId'       => 'settingValue',
            'parentSettingId' => ''
        ]);

        $cfg->registerObserver('settingId', get_class($this->observer));
        $cfg->registerObserver('parentSettingId', get_class($this->observer));

        $this->assertEquals('settingValue', $cfg->get('settingId'), 'Dependency condition OK');

        $cfg->hydrate([
            'settingId'       => 'settingValue',
            'parentSettingId' => '1'
        ]);
        $this->assertNull($cfg->get('settingId'), 'Dependency condition fails');
    }

    /**
     * @return void
     * @covers \VSHF\Config\Dependency::getGroupLogic
     * @covers \VSHF\Config\Dependency::on
     * @covers \VSHF\Config\Dependency::getConditions
     * @covers \VSHF\Config\DependencyCondition::beingEmpty
     * @covers \VSHF\Config\DependencyCondition::verified
     * @covers \VSHF\Config\DependencyCondition::addSelfToDependencyAndReturnIt
     * @covers \VSHF\Config\DependencyCondition::getSettingId
     * @covers \VSHF\Config\DependencyCondition::getContext
     */
    public function testDependencyEmpty(): void
    {
        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingEmpty();

        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn($dependency);

        $cfg = new Config([
            'settingId'       => 'settingValue',
            'parentSettingId' => ''
        ]);

        $cfg->registerObserver('settingId', get_class($this->observer));
        $cfg->registerObserver('parentSettingId', get_class($this->observer));

        $this->assertEquals('settingValue', $cfg->get('settingId'), 'Dependency condition OK');

        $cfg->hydrate([
            'settingId'       => 'settingValue',
            'parentSettingId' => '1'
        ]);
        $this->assertNull($cfg->get('settingId'), 'Dependency condition fails');
    }

    /**
     * @return void
     * @covers \VSHF\Config\Dependency::getGroupLogic
     * @covers \VSHF\Config\Dependency::on
     * @covers \VSHF\Config\Dependency::getConditions
     * @covers \VSHF\Config\DependencyCondition::beingNotEmpty
     * @covers \VSHF\Config\DependencyCondition::verified
     * @covers \VSHF\Config\DependencyCondition::addSelfToDependencyAndReturnIt
     * @covers \VSHF\Config\DependencyCondition::getSettingId
     * @covers \VSHF\Config\DependencyCondition::getContext
     */
    public function testDependencyNotEmpty(): void
    {
        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingNotEmpty();

        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn($dependency);

        $cfg = new Config([
            'settingId'       => 'settingValue',
            'parentSettingId' => '1'
        ]);

        $cfg->registerObserver('settingId', get_class($this->observer));
        $cfg->registerObserver('parentSettingId', get_class($this->observer));

        $this->assertEquals('settingValue', $cfg->get('settingId'), 'Dependency condition OK');

        $cfg->hydrate([
            'settingId'       => 'settingValue',
            'parentSettingId' => ''
        ]);
        $this->assertNull($cfg->get('settingId'), 'Dependency condition fails');
    }

    /**
     * @return void
     * @covers \VSHF\Config\Dependency::getGroupLogic
     * @covers \VSHF\Config\Dependency::on
     * @covers \VSHF\Config\Dependency::getConditions
     * @covers \VSHF\Config\DependencyCondition::beingIn
     * @covers \VSHF\Config\DependencyCondition::verified
     * @covers \VSHF\Config\DependencyCondition::addSelfToDependencyAndReturnIt
     * @covers \VSHF\Config\DependencyCondition::getSettingId
     * @covers \VSHF\Config\DependencyCondition::getContext
     */
    public function testDependencyIn(): void
    {
        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingIn([
                'value1',
                'value2'
            ]);

        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn($dependency);

        $cfg = new Config([
            'settingId'       => 'settingValue',
            'parentSettingId' => 'value1'
        ]);

        $cfg->registerObserver('settingId', get_class($this->observer));
        $cfg->registerObserver('parentSettingId', get_class($this->observer));

        $this->assertEquals('settingValue', $cfg->get('settingId'), 'Dependency condition OK');

        $cfg->hydrate([
            'settingId'       => 'settingValue',
            'parentSettingId' => 'value3'
        ]);
        $this->assertNull($cfg->get('settingId'), 'Dependency condition fails');
    }

    /**
     * @return void
     * @covers \VSHF\Config\Dependency::getGroupLogic
     * @covers \VSHF\Config\Dependency::on
     * @covers \VSHF\Config\Dependency::getConditions
     * @covers \VSHF\Config\DependencyCondition::beingNotIn
     * @covers \VSHF\Config\DependencyCondition::verified
     * @covers \VSHF\Config\DependencyCondition::addSelfToDependencyAndReturnIt
     * @covers \VSHF\Config\DependencyCondition::getSettingId
     * @covers \VSHF\Config\DependencyCondition::getContext
     */
    public function testDependencyNotIn(): void
    {
        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingNotIn([
                'value1',
                'value2'
            ]);

        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn($dependency);

        $cfg = new Config([
            'settingId'       => 'settingValue',
            'parentSettingId' => 'value3'
        ]);

        $cfg->registerObserver('settingId', get_class($this->observer));
        $cfg->registerObserver('parentSettingId', get_class($this->observer));

        $this->assertEquals('settingValue', $cfg->get('settingId'), 'Dependency condition OK');

        $cfg->hydrate([
            'settingId'       => 'settingValue',
            'parentSettingId' => 'value1'
        ]);
        $this->assertNull($cfg->get('settingId'), 'Dependency condition fails');
    }

    /**
     * @return void
     * @covers \VSHF\Config\Dependency::getGroupLogic
     * @covers \VSHF\Config\Dependency::on
     * @covers \VSHF\Config\Dependency::and
     * @covers \VSHF\Config\Dependency::getConditions
     * @covers \VSHF\Config\DependencyCondition::beingEqualTo
     * @covers \VSHF\Config\DependencyCondition::beingIn
     * @covers \VSHF\Config\DependencyCondition::verified
     * @covers \VSHF\Config\DependencyCondition::addSelfToDependencyAndReturnIt
     * @covers \VSHF\Config\DependencyCondition::getSettingId
     * @covers \VSHF\Config\DependencyCondition::getContext
     */
    public function testMultiDependencyAnd(): void
    {
        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingIn([
                'value1',
                'value2'
            ])
            ->and('parentSetting2Id')
            ->beingEqualTo('value3');

        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn($dependency);

        $cfg = new Config([
            'settingId'        => 'settingValue',
            'parentSettingId'  => 'value1',
            'parentSetting2Id' => 'value3'
        ]);

        $cfg->registerObserver('settingId', get_class($this->observer));
        $cfg->registerObserver('parentSettingId', get_class($this->observer));
        $cfg->registerObserver('parentSetting2Id', get_class($this->observer));

        $this->assertEquals('settingValue', $cfg->get('settingId'), 'Dependency condition OK');

        $cfg->hydrate([
            'settingId'        => 'settingValue',
            'parentSettingId'  => 'value1',
            'parentSetting2Id' => 'value2'
        ]);
        $this->assertNull($cfg->get('settingId'), 'Dependency condition fails');
    }

    /**
     * @return void
     * @covers \VSHF\Config\Dependency::getGroupLogic
     * @covers \VSHF\Config\Dependency::on
     * @covers \VSHF\Config\Dependency::or
     * @covers \VSHF\Config\Dependency::getConditions
     * @covers \VSHF\Config\DependencyCondition::beingEqualTo
     * @covers \VSHF\Config\DependencyCondition::beingIn
     * @covers \VSHF\Config\DependencyCondition::verified
     * @covers \VSHF\Config\DependencyCondition::addSelfToDependencyAndReturnIt
     * @covers \VSHF\Config\DependencyCondition::getSettingId
     * @covers \VSHF\Config\DependencyCondition::getContext
     */
    public function testMultiDependencyOr(): void
    {
        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingIn([
                'value1',
                'value2'
            ])
            ->or('parentSetting2Id')
            ->beingEqualTo('value3');

        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn($dependency);

        $cfg = new Config([
            'settingId'        => 'settingValue',
            'parentSettingId'  => 'value3',
            'parentSetting2Id' => 'value3'
        ]);

        $cfg->registerObserver('settingId', get_class($this->observer));
        $cfg->registerObserver('parentSettingId', get_class($this->observer));
        $cfg->registerObserver('parentSetting2Id', get_class($this->observer));

        $this->assertEquals('settingValue', $cfg->get('settingId'), 'Dependency condition OK');

        $cfg->hydrate([
            'settingId'        => 'settingValue',
            'parentSettingId'  => 'value3',
            'parentSetting2Id' => 'value1'
        ]);
        $this->assertNull($cfg->get('settingId'), 'Dependency condition fails');
    }

    /**
     * @return void
     * @covers \VSHF\Config\Dependency::getGroupLogic
     * @covers \VSHF\Config\Dependency::on
     * @covers \VSHF\Config\Dependency::andGroup
     * @covers \VSHF\Config\Dependency::endGroup
     * @covers \VSHF\Config\Dependency::getConditions
     * @covers \VSHF\Config\DependencyCondition::beingEqualTo
     * @covers \VSHF\Config\DependencyCondition::beingIn
     * @covers \VSHF\Config\DependencyCondition::verified
     * @covers \VSHF\Config\DependencyCondition::addSelfToDependencyAndReturnIt
     * @covers \VSHF\Config\DependencyCondition::getSettingId
     * @covers \VSHF\Config\DependencyCondition::getContext
     */
    public function testMultiDependencyAndGroup(): void
    {
        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingIn([
                'value1',
                'value2'
            ])
            ->andGroup()
            ->on('parentSetting2Id')
            ->beingEqualTo('value3')
            ->or('parentSetting2Id')
            ->beingEqualTo('value1')
            ->endGroup();

        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn($dependency);

        $cfg = new Config([
            'settingId'        => 'settingValue',
            'parentSettingId'  => 'value1',
            'parentSetting2Id' => 'value3'
        ]);

        $cfg->registerObserver('settingId', get_class($this->observer));
        $cfg->registerObserver('parentSettingId', get_class($this->observer));
        $cfg->registerObserver('parentSetting2Id', get_class($this->observer));

        $this->assertEquals('settingValue', $cfg->get('settingId'), 'Dependency condition OK');

        $cfg->hydrate([
            'settingId'        => 'settingValue',
            'parentSettingId'  => 'value3',
            'parentSetting2Id' => 'value1'
        ]);
        $this->assertNull($cfg->get('settingId'), 'Dependency condition fails');
    }

    /**
     * @return void
     * @covers \VSHF\Config\Dependency::getGroupLogic
     * @covers \VSHF\Config\Dependency::on
     * @covers \VSHF\Config\Dependency::orGroup
     * @covers \VSHF\Config\Dependency::endGroup
     * @covers \VSHF\Config\Dependency::getConditions
     * @covers \VSHF\Config\DependencyCondition::beingEqualTo
     * @covers \VSHF\Config\DependencyCondition::beingIn
     * @covers \VSHF\Config\DependencyCondition::verified
     * @covers \VSHF\Config\DependencyCondition::addSelfToDependencyAndReturnIt
     * @covers \VSHF\Config\DependencyCondition::getSettingId
     * @covers \VSHF\Config\DependencyCondition::getContext
     */
    public function testMultiDependencyOrGroup(): void
    {
        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingIn([
                'value1',
                'value2'
            ])
            ->orGroup()
            ->on('parentSetting2Id')
            ->beingEqualTo('value3')
            ->or('parentSetting2Id')
            ->beingEqualTo('value1')
            ->endGroup();

        $this->observer->allows('validate')->andReturnTrue();
        $this->observer->allows('dependencies')->andReturn($dependency);

        $cfg = new Config([
            'settingId'        => 'settingValue',
            'parentSettingId'  => 'value3',
            'parentSetting2Id' => 'value3'
        ]);

        $cfg->registerObserver('settingId', get_class($this->observer));
        $cfg->registerObserver('parentSettingId', get_class($this->observer));
        $cfg->registerObserver('parentSetting2Id', get_class($this->observer));

        $this->assertEquals('settingValue', $cfg->get('settingId'), 'Dependency condition OK');

        $cfg->hydrate([
            'settingId'        => 'settingValue',
            'parentSettingId'  => 'value3',
            'parentSetting2Id' => 'value2'
        ]);
        $this->assertNull($cfg->get('settingId'), 'Dependency condition fails');
    }

    /**
     * @return void
     * @covers \VSHF\Config\Dependency::getGroupLogic
     * @covers \VSHF\Config\Dependency::on
     * @covers \VSHF\Config\Dependency::getConditions
     * @covers \VSHF\Config\DependencyCondition::beingEqualTo
     * @covers \VSHF\Config\DependencyCondition::beingIn
     * @covers \VSHF\Config\DependencyCondition::verified
     * @covers \VSHF\Config\DependencyCondition::addSelfToDependencyAndReturnIt
     * @covers \VSHF\Config\DependencyCondition::getSettingId
     * @covers \VSHF\Config\DependencyCondition::getContext
     */
    public function testMultiDependencyGroupLogicCoherence(): void
    {
        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingEqualTo('value3')
            ->and('parentSetting2Id')
            ->beingEqualTo('value3')
            ->and('parentSetting3Id')
            ->beingEqualTo('value1');

        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingEqualTo('value3')
            ->or('parentSetting2Id')
            ->beingEqualTo('value3')
            ->or('parentSetting3Id')
            ->beingEqualTo('value1');

        $this->expectException(\BadMethodCallException::class);
        $dependency = new Dependency();
        $dependency
            ->on('parentSettingId')
            ->beingEqualTo('value3')
            ->and('parentSetting2Id')
            ->beingEqualTo('value3')
            ->or('parentSetting2Id')
            ->beingEqualTo('value1');
    }

    /**
     * @return void
     * @covers \VSHF\Config\Dependency::getGroupLogic
     * @covers \VSHF\Config\Dependency::on
     * @covers \VSHF\Config\Dependency::getConditions
     * @covers \VSHF\Config\DependencyCondition::beingEqualTo
     * @covers \VSHF\Config\DependencyCondition::beingIn
     * @covers \VSHF\Config\DependencyCondition::verified
     * @covers \VSHF\Config\DependencyCondition::addSelfToDependencyAndReturnIt
     * @covers \VSHF\Config\DependencyCondition::getSettingId
     * @covers \VSHF\Config\DependencyCondition::getContext
     */
    public function testCrossDependency(): void
    {
        //TODO
        $this->assertTrue(TRUE);
    }

    public function tearDown(): void
    {
        \Mockery::close();
    }
}
