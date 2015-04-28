<?php
namespace FwlibTest\Html\ListView;

use Fwlib\Html\ListView\Config;
use Fwlib\Html\ListView\ConfigAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ConfigAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @param   string[]    $methods
     * @return  MockObject|ConfigAwareTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(ConfigAwareTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testAccessors()
    {
        $configAware = $this->buildMock();

        $config = $this->reflectionCall($configAware, 'getConfigInstance');
        $this->assertInstanceOf(Config::class, $config);

        $config->set('id', 42);
        $configAware->setConfigInstance($config);
    }
}
