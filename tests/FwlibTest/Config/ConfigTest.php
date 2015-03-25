<?php
namespace FwlibTest\Config;

use Fwlib\Config\Config;
use Fwlib\Util\UtilContainerInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ConfigTest extends PHPUnitTestCase
{
    /**
     * @return  MockObject | Config
     */
    protected function buildMock()
    {
        $mock = $this->getMock(Config::class, null);

        return $mock;
    }


    public function testAccessors()
    {
        $config = new Config;

        $utilContainer = $this->reflectionCall($config, 'getUtilContainer');
        $this->assertInstanceOf(
            UtilContainerInterface::class,
            $utilContainer
        );
    }


    public function testSetGet()
    {
        $config = new Config;

        // Single value
        $config->set('foo', 'bar');
        $this->assertEquals($config->get('foo'), 'bar');
        $this->assertFalse(isset($config['foo2']));
        $config['foo2'] = 'bar2';
        $this->assertEquals('bar2', $config['foo2']);
        unset($config['foo2']);
        $this->assertFalse(isset($config['foo2']));

        // Value with separator turns to array
        $config->set('foo1.bar', 42);
        $this->assertEquals($config->get('foo1'), ['bar' => 42]);
        $config['foo3.bar'] = 'bar3';
        $this->assertEquals('bar3', $config['foo3.bar']);

        // Value with empty middle level
        $config->set('a.b.c', 42);
        $this->assertEquals($config->get('a.b.c', 43), 42);
        $this->assertEquals(
            $config->get('a'),
            [
                'b' => [
                    'c' => 42,
                ],
            ]
        );

        // Default value
        $this->assertEquals(42, $config->get('notExists.bar', 42));


        // Set array data
        $configData = [
            'a'     => 1,
            'b.1'   => 2,
            'b.2'   => 3,
            'c.1.1' => 4,
        ];
        // load() will reset all previous set data.
        $config->load($configData);
        $expectedResult = [
            'a' => 1,
            'b' => [
                1   => 2,
                2   => 3
            ],
            'c' => [
                1   => [
                    1   => 4,
                ],
            ],
        ];
        $this->assertEqualArray(
            $expectedResult,
            $this->reflectionGet($config, 'configs')
        );
    }
}
