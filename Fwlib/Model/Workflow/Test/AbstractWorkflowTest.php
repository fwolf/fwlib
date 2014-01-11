<?php
namespace Fwlib\Model\Workflow\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Model\Workflow\AbstractWorkflow;
use Fwlib\Model\Workflow\Test\AbstractWorkflowDummy;
use Fwlib\Test\ServiceContainerTest;

/**
 * Test for Fwlib\Model\Workflow\AbstractWorkflow
 *
 * @package     Fwlib\Model\Workflow\Test
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-10
 */
class AbstractWorkflowTest extends PHPunitTestCase
{
    protected $serviceContainer;
    public static $executeCustomizedAction = false;


    public function __construct()
    {
        $this->serviceContainer = ServiceContainerTest::getInstance();
    }


    protected function buildMock($uuid = '')
    {
        $workflow = $this->getMockBuilder(
            'Fwlib\Model\Workflow\AbstractWorkflow'
        )
        ->setMethods(array())
        ->setConstructorArgs(
            array($this->serviceContainer, $uuid)
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
            array($this->serviceContainer, $uuid)
        )
        ->getMockForAbstractClass();

        return $workflow;
    }


    public function testExecute()
    {
        $workflow = $this->buildMock();

        $contentData = array('dummy');
        $_POST = $contentData;

        $workflow->execute('submit');

        // Node is moved
        $this->assertEquals('end', $workflow->getCurrentNode());

        // Uuid is generated
        $this->assertNotEmpty($workflow->getUuid());

        // ResultCode is approved
        $this->assertTrue($workflow->isApproved());
        $this->assertEquals('Approved', $workflow->getResultCodeTitle());

        // Content data is set
        $this->assertEqualArray($contentData, $workflow->getContent());
    }


    public function testExecuteWithCustomizedExecuteActionMethod()
    {
        $workflow = $this->buildMockWithDummy();

        $this->assertFalse($workflow->isEnded());

        $workflow->execute('customizedAction');

        $this->assertTrue($workflow->isEnded());
        $this->assertEquals(
            AbstractWorkflow::RESULT_CODE_REJECTED,
            $workflow->getResultCode()
        );
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
    public function testExecuteWithUnAvailableAction()
    {
        $workflow = $this->buildMockWithDummy('dummyUuid');

        $workflow->execute('unAvailableAction');
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


    public function testRollback()
    {
        // Mock a DbDiff object
        $dbDiff = $this->getMockBuilder(
            'Fwlib\Db\DbDiff'
        )
        ->setMethods(
            array('import', 'rollback')
        )
        ->disableOriginalConstructor()
        ->getMock();

        $dbDiff->expects($this->once())
            ->method('import')
            ->will($this->returnSelf());

        $dbDiff->expects($this->once())
            ->method('rollback');


        $workflow = $this->buildMockWithDummy();
        $this->serviceContainer->register('DbDiff', $dbDiff);

        $workflow->execute('submit');
        $this->assertTrue($workflow->isEnded());
        $this->assertTrue($workflow->isApproved());
        $this->assertEquals(
            'AbstractWorkflow Dummy',
            $workflow->getTitle()
        );

        $workflow->execute('rollback');
        $this->assertFalse($workflow->isEnded());
        $this->assertFalse($workflow->isApproved());
    }
}
