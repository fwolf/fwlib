<?php
namespace FwlibTest\Base;

use Fwlib\Base\ServiceContainerTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ServiceContainerTraitTest extends PHPUnitTestCase
{
    /**
     * @return MockObject | ServiceContainerTrait
     */
    protected function buildMock()
    {
        $serviceContainer = $this->getMockBuilder(
            'Fwlib\Base\ServiceContainerTrait'
        )
            ->disableOriginalConstructor()
            ->setMethods(['createFoo'])
            ->getMockForTrait();

        $serviceContainer->expects($this->any())
            ->method('createFoo')
            ->will($this->onConsecutiveCalls(42, 43));

        return $serviceContainer;
    }


    public function testGetFromClass()
    {
        $serviceContainer = $this->buildMock();

        // Test for constructor
        $serviceContainer = $serviceContainer::getInstance();


        // Create instance with getInstance()
        $serviceContainer->register(
            'ServiceContainerTest',
            'Fwlib\Test\ServiceContainerTest'
        );

        $serviceContainerTest = $this->reflectionCall(
            $serviceContainer,
            'get',
            ['ServiceContainerTest']
        );

        $this->assertInstanceOf(
            'Fwlib\Test\ServiceContainerTest',
            $serviceContainerTest
        );


        // Create instance with new
        // Service name can be any case style
        $serviceContainer->register('exception', 'Exception');
        $exception = $this->reflectionCall(
            $serviceContainer,
            'get',
            ['exception']
        );
        $this->assertInstanceOf('Exception', $exception);
    }


    public function testGetFromInstance()
    {
        $serviceContainer = $this->buildMock();

        $this->assertEquals(
            42,
            $this->reflectionCall($serviceContainer, 'get', ['Foo'])
        );
        $this->assertEquals(
            42,
            $this->reflectionCall($serviceContainer, 'get', ['Foo'])
        );
        $this->assertEquals(
            43,
            $this->reflectionCall($serviceContainer, 'get', ['Foo', true])
        );


        $bar = new \stdClass;
        $bar->property = 'dummy';

        $serviceContainer->register('Bar', $bar);

        $this->assertEquals(
            $bar,
            $this->reflectionCall($serviceContainer, 'get', ['Bar'])
        );
    }


    /**
     * @expectedException \Fwlib\Base\Exception\ServiceInstanceCreationFailException
     */
    public function testGetInvalidService()
    {
        $serviceContainer = $this->buildMock();

        $this->reflectionCall($serviceContainer, 'get', ['NotExistService']);
    }
}
