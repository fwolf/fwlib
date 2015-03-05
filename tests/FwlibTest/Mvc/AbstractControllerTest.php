<?php
namespace FwlibTest\Mvc;

use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Mvc\AbstractController;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractControllerTest extends PHPUnitTestCase
{
    protected $controller;
    protected $serviceContainer;
    public static $controllerClass;
    public static $viewClass;


    public function __construct()
    {
        $this->controller = $this->buildMock('path/to/root');
    }


    protected function buildMock($pathToRoot)
    {
        $controller = $this->getMock(
            AbstractController::class,
            [
                'createController', 'createView',
                'getControllerClass', 'getViewClass'
            ],
            [$pathToRoot]
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


        return $controller;
    }


    /**
     * Build a mock, implements abstract method only
     */
    protected function buildMockBasis($pathToRoot)
    {
        $controller = $this->getMock(
            AbstractController::class,
            ['getViewClass'],
            [$pathToRoot]
        );

        $controller->expects($this->any())
            ->method('getViewClass')
            ->will($this->returnCallback(function () {
                return AbstractControllerTest::$viewClass;
            }));


        return $controller;
    }


    /**
     * Build a mock, with getControllerClass() method
     */
    protected function buildMockWithGetControllerClass($pathToRoot)
    {
        $controller = $this->getMock(
            AbstractController::class,
            ['getControllerClass', 'getViewClass'],
            [$pathToRoot]
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


        return $controller;
    }


    public function testDisplay()
    {
        $_GET = [
            'a' => 'test-action',
        ];
        // Need a dummy view class name, empty will throw exception
        self::$viewClass = 'Dummy';

        $output = $this->controller->getOutput();
        $this->assertEquals('Dummy Output', $output);

        // Action can be empty, need View allow output without action.
        $_GET = [];
        $output = $this->controller->getOutput();
        $this->assertEquals('Dummy Output', $output);
    }


    public function testDisplayWithActualView()
    {
        $controller = $this->buildMockWithGetControllerClass(null);
        self::$viewClass = 'FwlibTest\Mvc\AbstractControllerDummy';

        $output = $controller->getOutput(null);
        $this->assertEquals('Output from dummy', $output);
    }


    public function testDisplayWithEmptyViewClass()
    {
        $_GET = [
            'action' => 'test-action',
        ];
        self::$viewClass = '';

        $output = $this->controller->getOutput();
        $this->assertStringStartsWith('Error: View for action', $output);
    }


    public function testSetPathToRoot()
    {
        $this->controller->setPathToRoot('path/to/root');

        $this->assertEquals(
            'path/to/root/',
            $this->reflectionGet($this->controller, 'pathToRoot')
        );
    }


    public function testTransfer()
    {
        $_GET = [
            'm' => 'testModule',
        ];
        // Need a dummy view class name, or will throw exception
        self::$controllerClass = 'Dummy';

        $output = $this->controller->getOutput();
        $this->assertEquals('Dummy Output', $output);
    }


    public function testTransferWithActualController()
    {
        $_GET = [
            'm' => 'testModule',
        ];
        $controller = $this->buildMockWithGetControllerClass(null);

        self::$controllerClass = 'FwlibTest\Mvc\AbstractControllerDummy';

        $output = $controller->getOutput();
        $this->assertEquals('Output from dummy', $output);
    }


    public function testTransferWithEmptyControllerClass()
    {
        $_GET = [
            'm' => 'testModule',
        ];
        $controller = $this->buildMockBasis(null);

        $output = $controller->getOutput();
        $this->assertStringStartsWith('Error: Controller for module', $output);
    }
}
