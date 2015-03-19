<?php
namespace FwlibTest\Web;

use Fwlib\Web\AbstractController;
use Fwlib\Web\Request;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractControllerTest extends PHPUnitTestCase
{
    /**
     * @var string
     */
    public static $controllerClass;

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
     * @var string
     */
    public static $viewClass;


    /**
     * @return  MockObject | AbstractController
     */
    protected function buildMock()
    {
        $controller = $this->getMock(
            AbstractController::class,
            [
                'createController', 'createView',
                'getControllerClass', 'getViewClass'
            ]
        );

        $controller->expects($this->any())
            ->method('getViewClass')
            ->will($this->returnCallback(function () {
                return AbstractControllerTest::$viewClass;
            }));

        $controller->expects($this->any())
            ->method('getControllerClass')
            ->will($this->returnCallback(function () {
                return AbstractControllerTest::$controllerClass;
            }));


        // Mock a controller/view instance for output
        $mock = $this->getMock('stdClass', ['getOutput', 'setAction']);
        $mock->expects($this->any())
            ->method('setAction')
            ->will($this->returnSelf());
        $mock->expects($this->any())
            ->method('getOutput')
            ->will($this->returnValue('Dummy Output'));

        // Attach this mock
        $controller->expects($this->any())
            ->method('createController')
            ->will($this->returnValue($mock));
        $controller->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($mock));

        /** @var AbstractController $controller */
        $controller->setRequest($this->buildRequestMock());

        return $controller;
    }


    /**
     * Build a mock, implements abstract method only
     *
     * @return  MockObject | AbstractController
     */
    protected function buildMockBasis()
    {
        $controller = $this->getMock(
            AbstractController::class,
            ['getViewClass']
        );

        $controller->expects($this->any())
            ->method('getViewClass')
            ->will($this->returnCallback(function () {
                return AbstractControllerTest::$viewClass;
            }));

        /** @var AbstractController $controller */
        $controller->setRequest($this->buildRequestMock());

        return $controller;
    }


    /**
     * Build a mock, with getControllerClass() method
     *
     * @return  MockObject | AbstractController
     */
    protected function buildMockWithGetControllerClass()
    {
        $controller = $this->getMock(
            AbstractController::class,
            ['getControllerClass', 'getViewClass']
        );

        $controller->expects($this->any())
            ->method('getControllerClass')
            ->will($this->returnCallback(function () {
                return AbstractControllerTest::$controllerClass;
            }));

        $controller->expects($this->any())
            ->method('getViewClass')
            ->will($this->returnCallback(function () {
                return AbstractControllerTest::$viewClass;
            }));

        /** @var AbstractController $controller */
        $controller->setRequest($this->buildRequestMock());

        return $controller;
    }


    /**
     * @return MockObject | Request
     */
    protected function buildRequestMock()
    {
        if (is_null($this->requestMock)) {
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


    public function testDisplay()
    {
        $controller = $this->buildMock();

        $this->getAction = 'test-action';
        // Need a dummy view class name, empty will throw exception
        self::$viewClass = 'Dummy';

        $output = $controller->getOutput();
        $this->assertEquals('Dummy Output', $output);

        // Action can be empty, need View allow output without action.
        $this->getAction = '';
        $output = $controller->getOutput();
        $this->assertEquals('Dummy Output', $output);
    }


    public function testDisplayWithActualView()
    {
        $controller = $this->buildMockWithGetControllerClass(null);
        self::$viewClass = 'FwlibTest\Web\AbstractControllerDummy';

        $output = $controller->getOutput(null);
        $this->assertEquals('Output from dummy', $output);
    }


    public function testDisplayWithEmptyViewClass()
    {
        $controller = $this->buildMock();

        $this->getAction = 'test-action';
        self::$viewClass = '';

        $output = $controller->getOutput();
        $this->assertStringStartsWith('Error: View for action', $output);
    }


    public function testTransfer()
    {
        $controller = $this->buildMock();

        $this->getModule = 'testModule';
        // Need a dummy view class name, or will throw exception
        self::$controllerClass = 'Dummy';

        $output = $controller->getOutput();
        $this->assertEquals('Dummy Output', $output);
    }


    public function testTransferWithActualController()
    {
        $this->getModule = 'testModule';
        $controller = $this->buildMockWithGetControllerClass(null);

        self::$controllerClass = 'FwlibTest\Web\AbstractControllerDummy';

        $output = $controller->getOutput();
        $this->assertEquals('Output from dummy', $output);
    }


    public function testTransferWithEmptyControllerClass()
    {
        $this->getModule = 'testModule';
        $controller = $this->buildMockBasis(null);

        $output = $controller->getOutput();
        $this->assertStringStartsWith('Error: Controller for module', $output);
    }
}
