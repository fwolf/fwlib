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
                    'availableCheck' => 'isActionAvailable',
                ),
                'unAvailableAction' => array(
                    'availableCheck' => 'isActionAvailable',
                ),
                'customizedAction'  => array(
                    'next'  => 'end',
                    'resultCode'     => self::RESULT_CODE_REJECTED,
                ),
            ),
        ),
        'end'   => array(
            'title'     => 'Ended',
            'action'    => array(
                'rollback'  => array(
                    'next'  => 'start',
                ),
            ),
        ),
    );


    protected function executeCustomizedAction()
    {
        $this->title = 'changed';
    }


    public function load($uuid)
    {
    }


    protected function save()
    {
    }


    protected function saveLink()
    {
    }


    protected function saveLog($prevNode)
    {
    }


    protected function updateContent()
    {
        parent::updateContent();

        $this->title .= ' Dummy';

        return $this;
    }
}
