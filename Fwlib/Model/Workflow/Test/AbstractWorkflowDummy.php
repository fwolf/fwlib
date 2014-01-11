<?php
namespace Fwlib\Model\Workflow\Test;

use Fwlib\Model\Workflow\AbstractWorkflow;

abstract class AbstractWorkflowDummy extends AbstractWorkflow
{
    protected $node = array(
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


    protected function updateContent()
    {
        parent::updateContent();

        $this->title .= ' Dummy';

        return $this;
    }
}
