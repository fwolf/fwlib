<?php
namespace Fwlib\Mvc\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Bridge\Smarty;
use Fwlib\Mvc\AbstractView;
use Fwlib\Test\ServiceContainerTest;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-24
 */
class AbstractViewTest extends PHPunitTestCase
{
    protected $serviceContainer;
    protected $view;
    public static $class_exists = true;
    public static $assignByRef = array();
    public static $error_log = '';


    public function __construct()
    {
        $this->serviceContainer = ServiceContainerTest::getInstance();
    }


    protected function buildMock($pathToRoot)
    {
        $view = $this->getMock(
            'Fwlib\Mvc\AbstractView',
            array('fetchTestAction'),
            array($pathToRoot)
        );

        $view->setServiceContainer($this->serviceContainer);

        $view->expects($this->any())
            ->method('fetchTestAction')
            ->will($this->returnValue('_body for test action_'));


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

        $this->serviceContainer->register('Smarty', $smarty);


        return $view;
    }


    public function testForCoverage()
    {
        $view = $this->buildMock('path/to/root');
        $view->setTitle('test title');

        $this->assertTrue(true);
    }


    public function testGetOutput()
    {
        $view = $this->buildMock('path/to/root/');

        $this->assertEquals(
            'header.tplfooter.tpl',
            $view->getOutput(null)
        );

        $this->assertEquals(
            'header.tpl_body for test action_footer.tpl',
            $view->getOutput('test-action')
        );
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage View fetch method for action
     */
    public function testGetOutputWithInvalidAction()
    {
        $view = $this->buildMock('path/to/root/');

        $view->getOutput('test-action-not-exist');
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage View method for part
     */
    public function testGetOutputWithInvalidPart()
    {
        $view = $this->buildMock('path/to/root/');

        $view->setOutputPart(array('NotExist'));
        $view->getOutput(null);
    }


    public function testGetOutputWithoutTidyExtension()
    {
        $view = $this->buildMock('path/to/root/');

        self::$class_exists = false;
        $view->setUseTidy(true);
        $this->assertTrue($view->getUseTidy());

        $view->getOutput(null);

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
            'header.tpl_body for test action_footer.tpl',
            $view->getOutput('test-action')
        );


        self::$class_exists = true;
        $view->setUseTidy(true);
        $output = $view->getOutput('test-action');

        $this->assertStringEndsWith('</html>', $output);
    }


    public function testRemoveCssAndJs()
    {
        $view = $this->buildMock('path/to/root/');

        $this->reflectionCall(
            $view,
            'addCss',
            array('reset', 'path/to/reset.css', 'media, print')
        );
        $this->reflectionCall(
            $view,
            'addCss',
            array('default', 'path/to/default.css', 'media, print')
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
    return \Fwlib\Mvc\Test\AbstractViewTest::$class_exists;
}


function error_log($message)
{
    \Fwlib\Mvc\Test\AbstractViewTest::$error_log = $message;
}
