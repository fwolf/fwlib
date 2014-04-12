<?php
namespace Fwlib\Model\Workflow\Test;

use Fwlib\Model\Workflow\AbstractWorkflow;

/**
 * This class is not abstract, because AbstractWorkflowView need to create its
 * instance for test, abstract method in parent class will got an empty
 * declare here.
 */
class AbstractWorkflowDummy extends AbstractWorkflow
{
    protected $nodes = array(
        'start' => array(
            'title'     => 'Started',
            'action'    => array(
                'edit'      => array(
                    'title' => 'Edit',
                    'next'  => 'start',
                ),
                'submit'    => array(
                    'title' => 'Submit',
                    'next'  => 'end',
                    'resultCode'     => self::RESULT_CODE_APPROVED,
                ),
                'notAvailableAction' => array(
                ),
                'customizedAction'  => array(
                    'title' => 'Customize Action',
                    'next'  => 'end',
                    'resultCode'     => self::RESULT_CODE_REJECTED,
                ),
            ),
        ),
        'end'   => array(
            'title'     => 'Ended',
            'action'    => array(
                'rollback'  => array(
                    'title' => 'Rollback',
                    'next'  => 'start',
                ),
            ),
        ),
    );

    protected static $workflowTitle = 'Workflow Title Dummy';


    protected function commit()
    {
    }


    protected function executeCustomizedAction()
    {
        $this->model->setTitle('changed');
    }


    protected function isActionCustomizedActionAvailable()
    {
        // Just a dummy for code coverage
        return true;
    }


    protected function isActionNotAvailableActionAvailable()
    {
        return 'this action is not available';
    }
}
