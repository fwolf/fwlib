<?php
namespace FwlibTest\Config;

use Fwlib\Config\StringOptionsAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class StringOptionsAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @var array
     */
    protected static $setConfigs = [];


    /**
     * @param   string[]    $methods
     * @return  MockObject|StringOptionsAwareTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(StringOptionsAwareTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testSetStringOptions()
    {
        $trait = $this->buildMock(['setConfigs']);
        $trait->expects($this->any())
            ->method('setConfigs')
            ->willReturnCallback(function ($configs) {
                self::$setConfigs = $configs;
            });

        $trait->setStringOptions('foo, bar=42');
        $this->assertEquals(
            ['foo' => true, 'bar' => 42],
            self::$setConfigs
        );
    }
}
