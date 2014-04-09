<?php
namespace Fwlib\Model\Workflow\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Model\Workflow\AbstractWorkflow;
use Fwlib\Model\Workflow\Test\AbstractWorkflowDummy;
use Fwlib\Test\ServiceContainerTest;

/**
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-10
 */
class AbstractWorkflowTest extends PHPunitTestCase
{
    protected function buildMock($uuid = '')
    {
        $workflow = $this->getMockBuilder(
            'Fwlib\Model\Workflow\AbstractWorkflow'
        )
        ->setMethods(array())
        ->setConstructorArgs(
            array($uuid)
        )
        ->getMockForAbstractClass();

        return $workflow;
    }


    protected function buildMockWithDummy($uuid = '')
    {
        $workflow = $this->getMockBuilder(
            'Fwlib\Model\Workflow\Test\AbstractWorkflowDummy'
        )
        ->setMethods(array())
        ->setConstructorArgs(
            array($uuid)
        )
        ->getMockForAbstractClass();

        return $workflow;
    }


    public function testGetActionNotAvailableMessage()
    {
        $workflow = $this->buildMockWithDummy('dummyUuid');

        $workflow->getAvailableAction();

        $this->assertArrayHasKey(
            'notAvailableAction',
            $workflow->getActionNotAvailableMessage()
        );
    }


    public function testExecute()
    {
        $workflow = $this->buildMockWithDummy();

        $contentData = array('dummy');
        $_POST = $contentData;

        $workflow->execute('submit');

        // Node is moved
        $this->assertEquals('end', $workflow->getCurrentNode());

        // ResultCode is approved
        $this->assertTrue($workflow->isApproved());
        $this->assertEquals('Approved', $workflow->getResultCodeTitle());

        // Content data is set
        $this->assertEqualArray($contentData, $workflow->getContent());

        // Rollback
        $workflow->execute('rollback');
        $this->assertEquals('start', $workflow->getCurrentNode());
    }


    public function testExecuteWithCustomizedExecuteActionMethod()
    {
        $workflow = $this->buildMockWithDummy();

        $this->assertFalse($workflow->isEnded());

        $workflow->execute('customizedAction');

        // Check the customized method is executed
        $this->assertEquals('changed', $workflow->getTitle());

        $this->assertTrue($workflow->isEnded());
        $this->assertEquals(
            AbstractWorkflow::RESULT_CODE_REJECTED,
            $workflow->getResultCode()
        );

        // Uuid is generated
        $this->assertNotEmpty($workflow->getUuid());
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid or not allowed action
     */
    public function testExecuteWithInvalidAction()
    {
        $workflow = $this->buildMock();

        $workflow->execute('actionNotExists');
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid or not allowed action
     */
    public function testExecuteWithNotAvailableAction()
    {
        $workflow = $this->buildMockWithDummy('dummyUuid');

        $workflow->execute('notAvailableAction');
    }


    public function testGetAvailableAction()
    {
        $workflow = $this->buildMockWithDummy();

        $availableAction = $workflow->getAvailableAction();

        $this->assertEqualArray(
            array('edit', 'submit', 'customizedAction'),
            array_keys($availableAction)
        );
    }


    /**
     * @expectedException Exception
     * @expectedExceptionMessage end twice
     */
    public function testMoveToEndTwice()
    {
        $workflow = $this->buildMock();

        $workflow->execute('submit');

        // Normally if we execute 'submit' again, will fail because there has
        // no 'submit' in node 'end', so to simulate concurrence execute, we
        // use reflection to call moveTo() directly.
        $this->reflectionCall($workflow, 'moveTo', array('end'));
    }
}
