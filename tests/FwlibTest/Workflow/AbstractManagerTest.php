<?php
namespace FwlibTest\Workflow;

use Fwlib\Workflow\ModelInterface;
use Fwolf\Wrapper\PHPUnit\PHPUnitTestCase;
use Fwlib\Workflow\AbstractManager;

/**
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractManagerTest extends PHPUnitTestCase
{
    protected function buildMock($uuid = '')
    {
        $workflow = $this->getMockBuilder(
            'FwlibTest\Workflow\AbstractManagerDummy'
        )
        ->setMethods([])
        ->setConstructorArgs([$uuid])
        ->getMockForAbstractClass();

        return $workflow;
    }


    public function testAccessors()
    {
        $workflow = $this->buildMock('uuid dummy');

        $this->assertEquals('uuid dummy', $workflow->getUuid());
        $this->assertNotEmpty($workflow->getCurrentNodeTitle());
        $this->assertNotEmpty($workflow->getModelClass());
        $this->assertInstanceOf(
            ModelInterface::class,
            $workflow->getModel()
        );

        // Get title for current result code
        $this->assertEquals('Not Ended', $workflow->getResultCodeTitle());
        // Get title with given result code
        $this->assertEquals(
            'Approved',
            $workflow->getResultCodeTitle($workflow::RESULT_CODE_APPROVED)
        );

        $workflow->setNodeActionTitle('start', 'edit', 'Edit Title Modified');
        $nodes = $this->reflectionGet($workflow, 'nodes');
        $this->assertEquals(
            'Edit Title Modified',
            $nodes['start']['actions']['edit']['title']
        );

        $workflowModel = $workflow->getModel();
        $workflow->setModel($workflowModel);
        $this->assertInstanceOf(
            ModelInterface::class,
            $workflow->getModel()
        );
    }


    public function testDisableEnableAction()
    {
        $workflow = $this->buildMock();

        $actionsOld = $workflow->getAvailableActions();

        $workflow->disableAction('notExist');
        $this->assertEqualArray(
            $actionsOld,
            $workflow->getAvailableActions()
        );

        $workflow->disableAction('submit');
        $actionsNew = $workflow->getAvailableActions();
        $this->assertEquals(count($actionsOld), count($actionsNew) + 1);
        $this->assertArrayNotHasKey('submit', $actionsNew);

        $workflow->enableAction('submit');
        $actionsNew = $workflow->getAvailableActions();
        // Actions order has been changed assertEqualArray() will fail
        $this->assertEquals($actionsOld, $actionsNew);
    }


    public function testDisableEnableActions()
    {
        $workflow = $this->buildMock();

        $actionsOld = $workflow->getAvailableActions();

        $workflow->disableActions(['notExist', 'submit']);
        $actionsNew = $workflow->getAvailableActions();

        $this->assertEquals(count($actionsOld), count($actionsNew) + 1);
        $this->assertArrayNotHasKey('submit', $actionsNew);

        $workflow->enableActions(['notExist', 'submit']);
        $actionsNew = $workflow->getAvailableActions();
        $this->assertEquals($actionsOld, $actionsNew);
    }


    public function testExecute()
    {
        $workflow = $this->buildMock();

        $contentData = ['key' => 'dummy'];
        $_POST = $contentData;

        $workflow->updateContents($_POST)->execute('submit');

        // Node is moved
        $this->assertEquals('end', $workflow->getCurrentNode());

        // ResultCode is approved
        $this->assertTrue($workflow->isApproved());
        $this->assertEquals('Approved', $workflow->getResultCodeTitle());

        // Content data is set
        $this->assertEquals('dummy', $workflow->getContent('key'));
        $this->assertEqualArray($contentData, $workflow->getContents());

        // Rollback
        $workflow->execute('rollback');
        $this->assertEquals('start', $workflow->getCurrentNode());
        $this->assertFalse($workflow->isApproved());
    }


    public function testExecuteWithCustomizedExecuteActionMethod()
    {
        $workflow = $this->buildMock('uuid');

        $this->assertFalse($workflow->isEnded());

        $workflow->execute('customizedAction');

        // Check the customized method is executed
        $this->assertEquals('changed', $workflow->getTitle());

        $this->assertTrue($workflow->isEnded());
        $this->assertEquals(
            $workflow::RESULT_CODE_REJECTED,
            $workflow->getResultCode()
        );

        // Uuid is generated
        $this->assertNotEmpty($workflow->getUuid());
    }


    /**
     * @expectedException \Fwlib\Workflow\Exception\InvalidActionException
     */
    public function testExecuteWithInvalidAction()
    {
        $workflow = $this->buildMock();

        $workflow->execute('actionNotExists');
    }


    /**
     * @expectedException \Fwlib\Workflow\Exception\InvalidActionException
     */
    public function testExecuteWithInvalidActionByReSubmit()
    {
        $workflow = $this->buildMock();

        $workflow->execute('submit');
        $workflow->execute('submit');
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid or not allowed action
     */
    public function testExecuteWithNotAvailableAction()
    {
        $workflow = $this->buildMock('dummyUuid');

        $workflow->execute('notAvailableAction');
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage invalid action
     */
    public function testGetActionTitle()
    {
        $workflow = $this->buildMock();

        $this->assertEquals('Submit', $workflow->getActionTitle('submit'));

        $workflow->getActionTitle('not exists action');
    }


    public function testGetAvailableAction()
    {
        $workflow = $this->buildMock();

        $availableAction = $workflow->getAvailableActions();

        $this->assertEqualArray(
            ['edit', 'submit', 'customizedAction'],
            array_keys($availableAction)
        );


        $workflow->execute('submit');

        // Simulate an end node without actions
        $nodes = $this->reflectionGet($workflow, 'nodes');
        unset($nodes['end']['actions']);
        $this->reflectionSet($workflow, 'nodes', $nodes);

        $this->assertEmpty($workflow->getAvailableActions());
    }


    public function testGetNotAvailableActions()
    {
        $workflow = $this->buildMock('dummyUuid');

        $workflow->getAvailableActions();

        $this->assertArrayHasKey(
            'notAvailableAction',
            $workflow->getNotAvailableActions()
        );
    }


    public function testGetWorkflowTitle()
    {
        $workflow = $this->buildMock();

        $this->assertEquals(
            'Workflow Title Dummy',
            $workflow->getWorkflowTitle()
        );
    }


    public function testLimitActions()
    {
        $workflow = $this->buildMock();

        $actionsOld = $workflow->getAvailableActions();

        $workflow->limitActions(['notExist', 'edit', 'submit']);
        $actionsNew = $workflow->getAvailableActions();

        $this->assertEquals(2, count($actionsNew));
        $this->assertArrayHasKey('submit', $actionsNew);

        $workflow->enableAction('customizedAction');
        $actionsNew = $workflow->getAvailableActions();
        $this->assertArrayHasKey('customizedAction', $actionsNew);
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage end twice
     */
    public function testMoveToEndTwice()
    {
        $workflow = $this->buildMock();

        $workflow->execute('submit');

        // Normally if we execute 'submit' again, will fail because there has
        // no 'submit' in node 'end', so to simulate concurrence execute, we
        // use reflection to call move() directly.
        $this->reflectionCall($workflow, 'move', ['rollback', 'end', 'end']);
    }
}
