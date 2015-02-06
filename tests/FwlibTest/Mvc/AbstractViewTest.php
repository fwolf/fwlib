<?php
namespace FwlibTest\Mvc;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Mvc\AbstractView;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractViewTest extends PHPunitTestCase
{
    protected $view;
    public static $class_exists = true;
    public static $assignByRef = array();
    public static $error_log = '';


    protected function buildMock($pathToRoot)
    {
        $view = $this->getMock(
            'Fwlib\Mvc\AbstractView',
            array('fetchTestAction'),
            array($pathToRoot)
        );

        $view->expects($this->any())
            ->method('fetchTestAction')
            ->will($this->returnValue('<body for test action>'));


        // Mock a smarty instance
        $smarty = $this->getMock(
            'Fwlib\Bridge\Smarty',
            array('fetch', 'assignByRef')
        );

        $smarty->expects($this->any())
            ->method('fetch')
            ->will($this->returnArgument(0));

        $smarty->expects($this->any())
            ->method('assignByRef')
            ->will($this->returnCallback(function ($name, $value) {
                AbstractViewTest::$assignByRef[$name] = $value;
            }));


        return $view;
    }


    public function testAccessors()
    {
        $view = $this->buildMock('path/to/root');

        $view->setModule('Module');
        $this->assertEquals('Module', $this->reflectionGet($view, 'module'));

        $view->setTitle('Title');
        $this->assertEquals('Title', $this->reflectionGet($view, 'title'));
    }


    public function testGetOutput()
    {
        $view = $this->buildMock('path/to/root/');

        $this->assertEquals(
            '<!-- header --><!-- footer -->',
            $view->getOutput()
        );

        $this->assertEquals(
            '<!-- header --><body for test action><!-- footer -->',
            $view->setAction('test-action')->getOutput()
        );
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage View fetch method for action
     */
    public function testGetOutputWithInvalidAction()
    {
        $view = $this->buildMock('path/to/root/');

        $view->setAction('test-action-not-exist')->getOutput();
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage View method for part
     */
    public function testGetOutputWithInvalidPart()
    {
        $view = $this->buildMock('path/to/root/');

        $view->setOutputParts(array('NotExist'));
        $view->getOutput();
    }


    public function testGetOutputWithoutTidyExtension()
    {
        $view = $this->buildMock('path/to/root/');

        self::$class_exists = false;
        $view->setUseTidy(true);
        $this->assertTrue($view->getUseTidy());

        $view->getOutput();

        $this->assertEquals(
            'Tidy extension is not installed',
            self::$error_log
        );
    }


    /**
     * @requires extension tidy
     */
    public function testGetOutputWithTidy()
    {
        $view = $this->buildMock('path/to/root/');

        self::$class_exists = false;
        $view->setUseTidy(false);
        $this->assertFalse($view->getUseTidy());
        $this->assertEquals(
            '<!-- header --><body for test action><!-- footer -->',
            $view->setAction('test-action')->getOutput()
        );


        self::$class_exists = true;
        $view->setUseTidy(true);
        $output = $view->setAction('test-action')->getOutput();

        $this->assertStringEndsWith('</html>', $output);
    }


    public function testRemoveCssAndJs()
    {
        $view = $this->buildMock('path/to/root/');

        $this->reflectionCall(
            $view,
            'addCss',
            array('reset', 'path/to/reset.css', 'screen, print')
        );
        $this->reflectionCall(
            $view,
            'addCss',
            array('default', 'path/to/default.css')
        );
        $this->reflectionCall(
            $view,
            'addJs',
            array('jquery', 'path/to/jquery.js')
        );

        // Simulate get output
        $this->reflectionCall($view, 'getOutputHeader');

        $css = $this->reflectionGet($view, 'css');
        $this->assertArrayHasKey('reset', $css);
        $this->assertArrayHasKey('default', $css);

        $js = $this->reflectionGet($view, 'js');
        $this->assertArrayHasKey('jquery', $js);


        $this->reflectionCall($view, 'removeCss', array('reset'));
        $css = $this->reflectionGet($view, 'css');
        $this->assertArrayNotHasKey('reset', $css);

        $this->reflectionCall($view, 'removejs', array('jquery'));
        $js = $this->reflectionGet($view, 'js');
        $this->assertArrayNotHasKey('jquery', $js);
    }
}


// Fake function for test
namespace Fwlib\Mvc;


function class_exists()
{
    return \FwlibTest\Mvc\AbstractViewTest::$class_exists;
}


function error_log($message)
{
    \FwlibTest\Mvc\AbstractViewTest::$error_log = $message;
}