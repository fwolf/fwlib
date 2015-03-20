<?php
namespace FwlibTest\Web\Helper;

use Fwlib\Web\Helper\GetOutputBodyByFetchMethodTrait;
use FwlibTest\Aide\ObjectMockBuilder\FwlibWebRequestTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class GetOutputBodyByFetchMethodTraitTest extends PHPUnitTestCase
{
    use FwlibWebRequestTrait;


    /**
     * @return MockObject | GetOutputBodyByFetchMethodTrait
     */
    protected function buildMock()
    {
        $mock = $this->getMockBuilder(GetOutputBodyByFetchMethodTrait::class)
            ->setMethods(['fetchTestAction', 'getRequest'])
            ->getMockForTrait();

        $mock->expects($this->any())
            ->method('fetchTestAction')
            ->will($this->returnValue('body for test action'));

        $mock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->buildRequestMock());

        return $mock;
    }


    public function testGetOutput()
    {
        $view = $this->buildMock();

        $this->getAction = '';
        $this->assertEquals(
            '',
            $this->reflectionCall($view, 'getOutputBody')
        );

        $this->getAction = 'test-action';
        $this->assertEquals(
            'body for test action',
            $this->reflectionCall($view, 'getOutputBody')
        );
    }


    /**
     * @expectedException \Fwlib\Web\Exception\ViewMethodNotDefinedException
     * @expectedExceptionMessage View fetch method for action
     */
    public function testGetOutputWithInvalidAction()
    {
        $view = $this->buildMock();

        $this->getAction = 'test-action-not-exist';
        $this->reflectionCall($view, 'getOutputBody');
    }
}
