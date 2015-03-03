<?php
namespace FwlibTest\Base;

use Fwlib\Base\ServiceContainer;
use Fwlib\Base\ServiceContainerAwareTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ServiceContainerAwareTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | ServiceContainerAwareTrait
     */
    protected function buildMock()
    {
        $serviceContainerAware = $this->getMockBuilder(
            ServiceContainerAwareTrait::class
        )
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMockForTrait();

        return $serviceContainerAware;
    }


    public function testGetWithServiceContainerPropertyUnset()
    {
        $serviceContainerAware = $this->buildMock();

        $this->reflectionCall($serviceContainerAware, 'getServiceContainer');
        $this->assertNull(
            $this->reflectionGet($serviceContainerAware, 'serviceContainer')
        );
    }


    public function testGetWithServiceContainerPropertySet()
    {
        $serviceContainerAware = $this->buildMock();

        $serviceContainer = ServiceContainer::getInstance();
        $serviceContainer->register('FooException', new \Exception);

        $serviceContainerAware->setServiceContainer($serviceContainer);

        $this->assertInstanceOf(
            'Exception',
            $this->reflectionCall(
                $this->reflectionCall(
                    $serviceContainerAware,
                    'getServiceContainer'
                ),
                'get',
                ['FooException']
            )
        );
    }
}
