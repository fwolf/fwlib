<?php
namespace FwlibTest\Web;

use Fwlib\Bridge\Smarty;
use Fwlib\Web\AbstractView;
use FwlibTest\Aide\ObjectMockBuilder\FwlibWebRequestTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractViewTest extends PHPUnitTestCase
{
    use FwlibWebRequestTrait;


    protected $view;
    public static $assignByRef = [];


    /**
     * @return MockObject | AbstractView
     */
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

        /** @var AbstractView $view */
        $view->setRequest($this->buildRequestMock());

        return $view;
    }


    public function testAccessors()
    {
        $view = $this->buildMock();

        $this->reflectionCall($view, 'setTitle', ['Title']);
        $this->assertEquals('Title', $this->reflectionGet($view, 'title'));
    }


    public function testGetOutput()
    {
        $view = $this->buildMock();

        $this->assertEquals(
            '<!-- header --><!-- footer -->',
            $view->getOutput()
        );

        $this->getAction = 'test-action';
        $this->assertEquals(
            '<!-- header --><body for test action><!-- footer -->',
            $view->getOutput()
        );
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage View fetch method for action
     */
    public function testGetOutputWithInvalidAction()
    {
        $view = $this->buildMock();

        $this->getAction = 'test-action-not-exist';
        $view->getOutput();
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage View method for part
     */
    public function testGetOutputWithInvalidPart()
    {
        $view = $this->buildMock();

        $this->reflectionSet($view, 'outputParts', ['NotExist']);
        $view->getOutput();
    }
}
