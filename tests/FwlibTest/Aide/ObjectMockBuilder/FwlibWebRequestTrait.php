<?php
namespace FwlibTest\Aide\ObjectMockBuilder;

use Fwlib\Web\Request;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait FwlibWebRequestTrait
{
    /**
     * @var string
     */
    protected $getAction;

    /**
     * @var string
     */
    protected $getModule;

    /**
     * @var Request
     */
    protected $requestMock;


    /**
     * @return MockObject | Request
     */
    protected function buildRequestMock()
    {
        if (is_null($this->requestMock)) {
            /** @var PHPUnitTestCase|static $this */
            $mock = $this->getMockBuilder(Request::class)
                ->disableOriginalConstructor()
                ->setMethods(['getAction', 'getModule'])
                ->getMock();

            $mock->expects($this->any())
                ->method('getAction')
                ->willReturnCallback(function() {
                    return $this->getAction;
                });

            $mock->expects($this->any())
                ->method('getModule')
                ->willReturnCallback(function() {
                    return $this->getModule;
                });

            $this->requestMock = $mock;
        }

        return $this->requestMock;
    }
}
