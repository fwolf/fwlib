<?php
namespace Fwlib\Workflow\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Workflow\AbstractView;
use Fwlib\Workflow\Test\AbstractManagerDummy;

/**
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractViewTest extends PHPunitTestCase
{
    protected function buildMock()
    {
        $view = $this->getMockBuilder(
            'Fwlib\Workflow\AbstractView'
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

        $this->reflectionSet(
            $view,
            'workflowClass',
            'Fwlib\Workflow\Test\AbstractManagerDummy'
        );
        $this->reflectionSet(
            $view,
            'action',
            'workflow-dummy'
        );

        return $view;
    }


    public function testBuildQueryUrl()
    {
        $view = $this->getMockBuilder(
            'Fwlib\Workflow\AbstractView'
        )
        ->getMockForAbstractClass();

        $workflow = $this->getMock(
            'stdClass',
            array('getUuid')
        );
        $workflow->expects($this->any())
            ->method('getUuid')
            ->will($this->returnValue('dummy-uuid'));

        $this->reflectionSet($view, 'workflow', $workflow);


        $_GET = array(
            'm' => 'dummy-module',
            'a' => 'workflow-dummy',
            'uselessParameter' => 'dummy',
        );

        $url = $view->buildQueryUrl(
            'detail',
            array(
                'someParam' => 'param-value',
            ),
            array('uselessParameter')
        );

        $this->assertStringEndsWith(
            '?m=dummy-module&a=workflow-dummy&va=detail&uuid=dummy-uuid&someParam=param-value',
            $url
        );
    }


    public function testCreateOrLoadWorkflow()
    {
        $view = $this->buildMock();

        // Trigger method call
        $viewActionParameter =
            $this->reflectionGet($view, 'viewActionParameter');
        $_GET[$viewActionParameter] = 'detail';
        $view->setAction('workflow-dummy')->getOutput();

        $workflowModel = $this->reflectionGet($view, 'workflow')->getModel();

        $this->assertNotNull($workflowModel);
    }


    public function testGenerateTitle()
    {
        $view = $this->buildMock();

        $view->setAction('workflow-dummy');
        $uuid = 'workflowUuid';

        // Initialize workflow instance
        $this->reflectionCall($view, 'createOrLoadWorkflow', array($uuid));

        $title = $this->reflectionCall($view, 'generateTitle');
        $this->assertEquals('Workflow Title Dummy', $title);
    }


    public function testGetOutput()
    {
        $view = $this->buildMock();

        $_GET = array(
            'a'     => 'workflow-dummy',
            'va'    => 'detail',
            'uuid'  => 'workflowUuid',
        );

        $output = $view->getOutput();
        $this->assertEquals(
            '{header}{detailReadonly}{link}{log}{footer}',
            $output
        );

        $_GET['va'] = 'edit';
        $output = $view->getOutput();
        $this->assertEquals(
            '{header}{detailEditable}{action}{link}{log}{footer}',
            $output
        );

        $_GET['va'] = 'review';
        $output = $view->getOutput();
        $this->assertEquals(
            '{header}{detailReadonly}{action}{link}{log}{footer}',
            $output
        );


        // With workflow action, the view action defined in
        // 'viewActionAfterExecute' will be used, ignore view action from url.
        // The view action of submit is detail
        $_GET = array(
            'a'     => 'workflow-dummy',
            'va'    => 'edit',
            'uuid'  => 'workflowUuid',
        );
        $_POST = array(
            'wfa'   => 'submit',
        );

        $output = $view->getOutput();
        $this->assertEquals(
            '{header}{detailReadonly}{link}{log}{footer}',
            $output
        );


        // Empty body output for empty action, same with parent view
        $this->reflectionSet($view, 'action', '');
        $output = $view->getOutput();
        $this->assertEquals(
            '{header}{footer}',
            $output
        );
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage not defined
     */
    public function testGetOutputWithInvalidViewAction()
    {
        $view = $this->buildMock();

        $_GET = array(
            'a'     => 'workflow-dummy-invalid-view-action',
        );
        $_POST = array();

        $output = $view->setAction($_GET['a'])->getOutput();
    }


    public function testReceiveContentsFromRequest()
    {
        $view = $this->buildMock();

        $_POST = array(
            'a' => 'A',
            'b' => 'B',
        );

        $this->reflectionSet($view, 'receivableContentKeys', '*');
        $this->assertEqualArray(
            $_POST,
            $this->reflectionCall($view, 'receiveContentsFromRequest')
        );

        $this->reflectionSet($view, 'receivableContentKeys', array('a'));
        $this->assertEqualArray(
            array('a' => 'A'),
            $this->reflectionCall($view, 'receiveContentsFromRequest')
        );
    }
}
