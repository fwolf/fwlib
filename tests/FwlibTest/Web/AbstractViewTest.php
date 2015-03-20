<?php
namespace FwlibTest\Web;

use Fwlib\Bridge\Smarty;
use Fwlib\Web\AbstractView;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractViewTest extends PHPUnitTestCase
{
    protected $view;
    public static $class_exists = true;
    public static $assignByRef = [];
    public static $error_log = '';


    protected function buildMock()
    {
        $view = $this->getMock(
            AbstractView::class,
            ['fetchTestAction']
        );

        $view->expects($this->any())
            ->method('fetchTestAction')
            ->will($this->returnValue('<body for test action>'));


        // Mock a smarty instance
        $smarty = $this->getMock(
            Smarty::class,
            ['fetch', 'assignByRef']
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
        $view = $this->buildMock();

        $view->setModule('Module');
        $this->assertEquals('Module', $this->reflectionGet($view, 'module'));

        $view->setTitle('Title');
        $this->assertEquals('Title', $this->reflectionGet($view, 'title'));
    }


    public function testGetOutput()
    {
        $view = $this->buildMock();

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
     * @expectedException \Exception
     * @expectedExceptionMessage View fetch method for action
     */
    public function testGetOutputWithInvalidAction()
    {
        $view = $this->buildMock();

        $view->setAction('test-action-not-exist')->getOutput();
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage View method for part
     */
    public function testGetOutputWithInvalidPart()
    {
        $view = $this->buildMock();

        $view->setOutputParts(['NotExist']);
        $view->getOutput();
    }


    public function testGetOutputWithoutTidyExtension()
    {
        $view = $this->buildMock();

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
        $view = $this->buildMock();

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
}


// Fake function for test
namespace Fwlib\Web;

function class_exists()
{
    return \FwlibTest\Web\AbstractViewTest::$class_exists;
}


function error_log($message)
{
    \FwlibTest\Web\AbstractViewTest::$error_log = $message;
}
