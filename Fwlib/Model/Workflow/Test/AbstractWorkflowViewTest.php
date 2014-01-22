<?php
namespace Fwlib\Model\Workflow\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Model\Workflow\AbstractWorkflowView;
use Fwlib\Model\Workflow\Test\AbstractWorkflowDummy;
use Fwlib\Test\ServiceContainerTest;

/**
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-11
 */
class AbstractWorkflowViewTest extends PHPunitTestCase
{
    protected $serviceContainer;
    protected $view;


    public function __construct()
    {
        $this->serviceContainer = ServiceContainerTest::getInstance();

        $this->view = $this->buildMock();
    }


    protected function buildMock()
    {
        $view = $this->getMockBuilder(
            'Fwlib\Model\Workflow\AbstractWorkflowView'
        )
        ->setMethods(
            array(
                'getOutputHeader', 'getOutputFooter',
                'fetchDetailEditable', 'fetchDetailReadonly',
                'fetchAction', 'fetchLink', 'fetchLog',
            )
        )
        ->getMockForAbstractClass();

        $view->expects($this->any())
            ->method('getOutputHeader')
            ->will($this->returnValue('{header}'));

        $view->expects($this->any())
            ->method('getOutputFooter')
            ->will($this->returnValue('{footer}'));

        $view->expects($this->any())
            ->method('fetchDetailEditable')
            ->will($this->returnValue('{detailEditable}'));

        $view->expects($this->any())
            ->method('fetchDetailReadonly')
            ->will($this->returnValue('{detailReadonly}'));

        $view->expects($this->any())
            ->method('fetchAction')
            ->will($this->returnValue('{action}'));

        $view->expects($this->any())
            ->method('fetchLink')
            ->will($this->returnValue('{link}'));

        $view->expects($this->any())
            ->method('fetchLog')
            ->will($this->returnValue('{log}'));

        $view->setServiceContainer($this->serviceContainer);

        $this->reflectionSet(
            $view,
            'workflowClassnamePrefix',
            'Fwlib\\Model\\Workflow\\Test\\'
        );

        return $view;
    }


    public function testGetOutput()
    {
        $view = $this->view;

        // Workflow action not defined, view action comes from url
        $_GET = array(
            'a'     => 'AbstractWorkflowDummy-detail',
            'uuid'  => 'workflowUuid',
        );

        // Empty body output for empty action, same with parent view
        $output = $view->getOutput();
        $this->assertEquals(
            '{header}{footer}',
            $output
        );

        $output = $view->getOutput($_GET['a']);
        $this->assertEquals(
            '{header}{detailReadonly}{link}{log}{footer}',
            $output
        );

        $_GET['a'] = 'AbstractWorkflowDummy-edit';
        $output = $view->getOutput($_GET['a']);
        $this->assertEquals(
            '{header}{detailEditable}{action}{link}{log}{footer}',
            $output
        );

        $_GET['a'] = 'AbstractWorkflowDummy-review';
        $output = $view->getOutput($_GET['a']);
        $this->assertEquals(
            '{header}{detailReadonly}{action}{link}{log}{footer}',
            $output
        );


        // With workflow action, the view action defined in
        // 'viewActionAfterExecute' will be used, ignore view action from url.
        // The view action of submit is detail
        $_GET = array(
            'a'     => 'AbstractWorkflowDummy-edit',
            'wfa'   => 'submit',
            'uuid'  => 'workflowUuid',
        );

        $output = $view->getOutput($_GET['a']);
        $this->assertEquals(
            '{header}{detailReadonly}{link}{log}{footer}',
            $output
        );
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage not defined
     */
    public function testGetOutputWithInvalidViewAction()
    {
        $view = $this->view;

        $_GET = array(
            'a'     => 'AbstractWorkflowDummy-invalid-view-action',
        );

        $output = $view->getOutput($_GET['a']);
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage View action lost
     */
    public function testGetOutputWithoutViewAction()
    {
        $view = $this->view;

        $_GET = array(
            'a'     => 'AbstractWorkflowDummy',
        );

        $output = $view->getOutput($_GET['a']);
    }
}
