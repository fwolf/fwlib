<?php
namespace Fwlib\Workflow\Test;

use Fwlib\Workflow\AbstractManager;

/**
 * This class is not abstract, because AbstractManagerView need to create its
 * instance for test, abstract method in parent class will got an empty
 * declare here.
 */
class AbstractManagerDummy extends AbstractManager
{
    protected $modelClass =
        'Fwlib\Workflow\Test\ModelInterfaceDummy';

    protected $nodes = array(
        'start' => array(
            'title'     => 'Started',
            'actions'   => array(
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
                    'title' => 'Not Available Action Title',
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
            'actions'   => array(
                'rollback'  => array(
                    'title' => 'Rollback',
                    'next'  => 'start',
                ),
            ),
        ),
    );

    protected $workflowTitle = 'Workflow Title Dummy';


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