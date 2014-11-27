<?php
namespace Fwlib\Base\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Base\AbstractServiceContainer;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class AbstractServiceContainerTest extends PHPunitTestCase
{
    protected function buildMock()
    {
        $serviceContainer = $this->getMockBuilder(
            'Fwlib\Base\AbstractServiceContainer'
        )
            ->disableOriginalConstructor()
            ->setMethods(array('newFoo'))
            ->getMock();

        $serviceContainer->expects($this->any())
            ->method('newFoo')
            ->will($this->onConsecutiveCalls(42, 43));

        return $serviceContainer;
    }


    public function testGetFromClass()
    {
        $serviceContainer = $this->buildMock();

        $serviceContainer->register(
            'ServiceContainerTest',
            'Fwlib\Test\ServiceContainerTest'
        );

        $serviceContainerTest = $serviceContainer->get('ServiceContainerTest');

        $this->assertInstanceOf(
            'Fwlib\Test\ServiceContainerTest',
            $serviceContainerTest
        );
    }


    public function testGetFromInstance()
    {
        $serviceContainer = $this->buildMock();

        $this->assertEquals(42, $serviceContainer->get('Foo'));
        $this->assertEquals(42, $serviceContainer->get('Foo'));
        $this->assertEquals(43, $serviceContainer->get('Foo', true));


        $bar = new \stdClass;
        $bar->property = 'dummy';

        $serviceContainer->register('Bar', $bar);

        $this->assertEquals($bar, $serviceContainer->get('Bar'));
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid service
     */
    public function testGetInvalidService()
    {
        $serviceContainer = $this->buildMock();

        $serviceContainer->get('NotExist');
    }
}
