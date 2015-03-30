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
     * @return MockObject | RequestParameterTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(RequestParameterTrait::class)
            ->setMethods(null)
            ->getMockForTrait();

        return $mock;
    }


    public function testAccessors()
    {
        $request = $this->buildMock();

        $request->setOrderByDirectionParameter('OD');
        $this->assertEquals('OD', $request->getOrderByDirectionParameter());

        $request->setOrderByParameter('OB');
        $this->assertEquals('OB', $request->getOrderByParameter());

        $request->setPageParameter('page');
        $this->assertEquals('page', $request->getPageParameter());

        $request->setPageSizeParameter('pageSize');
        $this->assertEquals('pageSize', $request->getPageSizeParameter());
    }
}
