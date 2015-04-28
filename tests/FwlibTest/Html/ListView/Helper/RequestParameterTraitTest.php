<?php
namespace FwlibTest\Html\ListView\Helper;

use Fwlib\Html\ListView\Helper\RequestParameterTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 */
class RequestParameterTraitTest extends PHPUnitTestCase
{
    /**
     * @param   string[]    $methods
     * @return  MockObject|RequestParameterTrait
     */
    protected function buildMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(RequestParameterTrait::class)
            ->setMethods($methods)
            ->getMockForTrait();

        return $mock;
    }


    public function testAccessors()
    {
        $request = $this->buildMock(['getConfig']);
        $request->expects($this->any())
            ->method('getConfig')
            ->willReturnOnConsecutiveCalls(1, 1, 1, 42);

        $request->setOrderByDirectionParameter('OD');
        $this->assertEquals('OD', $request->getOrderByDirectionParameter());

        $request->setOrderByParameter('OB');
        $this->assertEquals('OB', $request->getOrderByParameter());

        $request->setPageParameter('page');
        $this->assertEquals('page', $request->getPageParameter());

        $request->setPageSizeParameter('pageSize');
        $this->assertEquals('pageSize42', $request->getPageSizeParameter());
    }
}
