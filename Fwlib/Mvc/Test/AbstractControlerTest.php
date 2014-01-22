<?php
namespace Fwlib\Mvc\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Mvc\AbstractControler;
use Fwlib\Test\ServiceContainerTest;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-24
 */
class AbstractControlerTest extends PHPunitTestCase
{
    protected $controler;
    protected $serviceContainer;
    public static $controlerClass;
    public static $viewClass;


    public function __construct()
    {
        $this->serviceContainer = ServiceContainerTest::getInstance();

        $this->controler = $this->buildMock('path/to/root');
    }


    protected function buildMock($pathToRoot)
    {
        $controler = $this->getMock(
            'Fwlib\Mvc\AbstractControler',
            array('createControler', 'createView',
                'getControlerClass', 'getViewClass'),
            array($pathToRoot)
        );

        $controler->expects($this->any())
            ->method('getViewClass')
            ->will($this->returnCallback(function () {
                return AbstractControlerTest::$viewClass;
            }));

        $controler->expects($this->any())
            ->method('getControlerClass')
            ->will($this->returnCallback(function () {
                return AbstractControlerTest::$controlerClass;
            }));

        $controler->setServiceContainer($this->serviceContainer);


        // Mock a controler/view instance for output
        $mock = $this->getMock('stdClass', array('getOutput'));
        $mock->expects($this->any())
            ->method('getOutput')
            ->will($this->returnValue('Dummy Output'));

        // Attach this mock
        $controler->expects($this->any())
            ->method('createControler')
            ->will($this->returnValue($mock));
        $controler->expects($this->any())
            ->method('createView')
            ->will($this->returnValue($mock));


        return $controler;
    }


    /**
     * Build a mock, implements abstract method only
     */
    protected function buildMockBasis($pathToRoot)
    {
        $controler = $this->getMock(
            'Fwlib\Mvc\AbstractControler',
            array('getViewClass'),
            array($pathToRoot)
        );

        $controler->expects($this->any())
            ->method('getViewClass')
            ->will($this->returnCallback(function () {
                return AbstractControlerTest::$viewClass;
            }));

        $controler->setServiceContainer($this->serviceContainer);


        return $controler;
    }


    /**
     * Build a mock, with getControlerClass() method
     */
    protected function buildMockWithGetControlerClass($pathToRoot)
    {
        $controler = $this->getMock(
            'Fwlib\Mvc\AbstractControler',
            array('getControlerClass', 'getViewClass'),
            array($pathToRoot)
        );

        $controler->expects($this->any())
            ->method('getControlerClass')
            ->will($this->returnCallback(function () {
                return AbstractControlerTest::$controlerClass;
            }));

        $controler->expects($this->any())
            ->method('getViewClass')
            ->will($this->returnCallback(function () {
                return AbstractControlerTest::$viewClass;
            }));

        $controler->setServiceContainer($this->serviceContainer);


        return $controler;
    }


    public function testDisplay()
    {
        $_GET = array(
            'a' => 'test-action',
        );
        // Need a dummy view class name, empty will throw exception
        self::$viewClass = 'Dummy';

        $output = $this->controler->getOutput();
        $this->assertEquals('Dummy Output', $output);

        // Action can be empty, need View allow output without action.
        $_GET = array();
        $output = $this->controler->getOutput();
        $this->assertEquals('Dummy Output', $output);
    }


    public function testDisplayWithActualView()
    {
        $controler = $this->buildMockWithGetControlerClass(null);
        self::$viewClass = 'Fwlib\Mvc\Test\AbstractControlerDummy';

        $output = $controler->getOutput(null);
        $this->assertEquals('Output from dummy', $output);
    }


    public function testDisplayWithEmptyViewClass()
    {
        $_GET = array(
            'action' => 'test-action',
        );
        self::$viewClass = '';

        $output = $this->controler->getOutput();
        $this->assertStringStartsWith('Error: View for action', $output);
    }


    public function testSetPathToRoot()
    {
        $this->controler->setPathToRoot('path/to/root');

        $this->assertEquals(
            'path/to/root/',
            $this->reflectionGet($this->controler, 'pathToRoot')
        );
    }


    public function testTransfer()
    {
        $_GET = array(
            'm' => 'testModule',
        );
        // Need a dummy view class name, or will throw exception
        self::$controlerClass = 'Dummy';

        $output = $this->controler->getOutput();
        $this->assertEquals('Dummy Output', $output);
    }


    public function testTransferWithActualControler()
    {
        $_GET = array(
            'm' => 'testModule',
        );
        $controler = $this->buildMockWithGetControlerClass(null);

        self::$controlerClass = 'Fwlib\Mvc\Test\AbstractControlerDummy';

        $output = $controler->getOutput();
        $this->assertEquals('Output from dummy', $output);
    }


    public function testTransferWithEmptyControlerClass()
    {
        $_GET = array(
            'm' => 'testModule',
        );
        $controler = $this->buildMockBasis(null);

        $output = $controler->getOutput();
        $this->assertStringStartsWith('Error: Controler for module', $output);
    }
}
