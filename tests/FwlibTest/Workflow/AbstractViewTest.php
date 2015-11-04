<?php
namespace FwlibTest\Workflow;

use Fwlib\Workflow\AbstractView;
use FwlibTest\Aide\ObjectMockBuilder\FwlibWebRequestTrait;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractViewTest extends PHPUnitTestCase
{
    use FwlibWebRequestTrait;


    /**
     * @return MockObject|AbstractView
     */
    protected function buildMock()
    {
        $view = $this->getMockBuilder(AbstractView::class)
            ->setMethods([
                'getOutputHeader',
                'getOutputFooter',
                'fetchDetailEditable',
                'fetchDetailReadonly',
                'fetchAction',
                'fetchLink',
                'fetchLog',
            ])
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
            AbstractManagerDummy::class
        );

        $request = $this->buildRequestMock();
        /** @var AbstractView $view */
        $view->setRequest($request);
        $this->getAction = 'workflow-dummy';

        return $view;
    }


    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testBuildQueryUrl()
    {
        /** @var MockObject|AbstractView $view */
        $view = $this->getMockBuilder(AbstractView::class)
            ->getMockForAbstractClass();

        $workflow = $this->getMock(
            'stdClass',
            ['getUuid']
        );
        $workflow->expects($this->any())
            ->method('getUuid')
            ->will($this->returnValue('dummy-uuid'));

        $this->reflectionSet($view, 'workflow', $workflow);


        $_GET = [
            'm'                => 'dummy-module',
            'a'                => 'workflow-dummy',
            'uselessParameter' => 'dummy',
        ];

        $url = $view->buildQueryUrl(
            'detail',
            [
                'someParam' => 'param-value',
            ],
            ['uselessParameter']
        );

        $this->assertStringEndsWith(
            '?m=dummy-module&a=workflow-dummy&va=detail&uuid=dummy-uuid&someParam=param-value',
            $url
        );
    }


    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testCreateOrLoadWorkflow()
    {
        $view = $this->buildMock();

        // Trigger method call
        $viewActionParameter =
            $this->reflectionGet($view, 'viewActionParameter');
        $_GET[$viewActionParameter] = 'detail';
        $this->getAction = 'workflow-dummy';
        $view->getOutput();

        $workflowModel = $this->reflectionGet($view, 'workflow')->getModel();

        $this->assertNotNull($workflowModel);
    }


    public function testGenerateTitle()
    {
        $view = $this->buildMock();

        $this->getAction = 'workflow-dummy';
        $uuid = 'workflowUuid';

        // Initialize workflow instance
        $this->reflectionCall($view, 'createOrLoadWorkflow', [$uuid]);

        $title = $this->reflectionCall($view, 'generateTitle');
        $this->assertEquals('Workflow Title Dummy', $title);
    }


    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testGetOutput()
    {
        $view = $this->buildMock();

        $_GET = [
            'a'    => 'workflow-dummy',
            'va'   => 'detail',
            'uuid' => 'workflowUuid',
        ];

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
        $_GET = [
            'a'    => 'workflow-dummy',
            'va'   => 'edit',
            'uuid' => 'workflowUuid',
        ];
        $_POST = [
            'wfa' => 'submit',
        ];

        $output = $view->getOutput();
        $this->assertEquals(
            '{header}{detailReadonly}{link}{log}{footer}',
            $output
        );


        // Empty body output for empty action, same with parent view
        $this->getAction = '';
        $output = $view->getOutput();
        $this->assertEquals(
            '{header}{footer}',
            $output
        );
    }


    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     *
     * @expectedException \Exception
     * @expectedExceptionMessage not defined
     */
    public function testGetOutputWithInvalidViewAction()
    {
        $view = $this->buildMock();

        $_GET = [
            'a' => 'workflow-dummy-invalid-view-action',
        ];
        $_POST = [];

        $view->getOutput();
    }


    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testReceiveContentsFromRequest()
    {
        $view = $this->buildMock();

        $_POST = [
            'a' => 'A',
            'b' => 'B',
        ];

        $this->reflectionSet($view, 'receivableKeys', '*');
        $this->assertEqualArray(
            $_POST,
            $this->reflectionCall($view, 'receiveContentsFromRequest')
        );

        $this->reflectionSet($view, 'receivableKeys', ['a']);
        $this->assertEqualArray(
            ['a' => 'A'],
            $this->reflectionCall($view, 'receiveContentsFromRequest')
        );
    }
}
