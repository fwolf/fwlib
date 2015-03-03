<?php
namespace FwlibTest\Base;

use Fwlib\Base\ServiceContainer;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ServiceContainerTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | ServiceContainer
     */
    protected function buildMock()
    {
        return ServiceContainer::getInstance();
    }


    /**
     * Check of returned type is not necessary, service container will always
     * return instance, or throw exception.
     */
    public function testGetMethods()
    {
        $container = $this->buildMock();

        // Through class map
        $this->assertNotEmpty($container->getCachedCaller());
        $this->assertNotEmpty($container->getCurl());
        $this->assertNotEmpty($container->getSmarty());
        $this->assertNotEmpty($container->getValidator());

        // Through create method
        $this->assertNotEmpty($container->getListTable());
    }
}
