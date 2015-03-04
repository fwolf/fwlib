<?php
namespace FwlibTest\Config;

use Fwlib\Config\ConfigAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ConfigAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | ConfigAwareTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(
            ConfigAwareTrait::class
        )
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    public function testNormalSetGet()
    {
        $configAware = $this->buildMock();

        $configAware->setConfigs([ 'prefix.key1'   => 10,]);
        $this->assertEquals(10, $configAware->getConfig('prefix.key1'));

        $configAware->setConfig('prefix.key2', 20);
        $this->assertEquals(
            ['key1' => 10, 'key2' => 20],
            $configAware->getConfig('prefix')
        );
    }
}
