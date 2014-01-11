<?php
namespace Fwlib\Model\Workflow;

use Fwlib\Model\Workflow\WorkflowInterface;
use Fwlib\Mvc\AbstractView;

/**
 * View for workflow
 *
 * In this implement, there are 3 type of basic view action:
 *
 * - detail: readonly, without action
 * - edit  : editable, withaction(need by edit)
 * - review: readonly, with action
 *
 * These view action build fetch result by smaller parts:
 *
 * - fetchDetailReadonly
 * - fetchDetailEditable
 * - fetchAction
 * - fetchLink
 * - fetchLog
 *
 * These part can used as normal view action too.
 *
 * This view can declare an instance of a normal view, and use it to provide
 * same header, footer with other non-workflow views.
 *
 * @package     Fwlib\Model\Workflow
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-09
 */
abstract class AbstractWorkflowView extends AbstractView
{
    /**
     * Request parameter of uuid
     *
     * @var string
     */
    protected $uuidParameter = 'uuid';

    /**
     * View action after execute workflow action
     *
     * If view action are defined here, will skip getWorkflowViewAction(),
     * which parse action from url param.
     *
     * @var array
     */
    protected $viewActionAfterExecute = array(
        'edit'   => 'edit',
        'submit' => 'detail',
    );

    /**
     * Request parameter of workflow action
     *
     * @var string
     */
    protected $workflowActionParameter = 'wfa';

    /**
     * Prefix of workflow classname
     *
     * When using workflow classname as part of action in url, the full
     * qualified classname is too encumbrance, so only unqualified name is
     * used.  With this classname, the new operate can only access class under
     * same namespace with view.  If workflow class is under different
     * namespace with view, their full qualified classname prefix need to
     * define here, the getWorkflowClassname() method will read and add it to
     * unqualified name.
     *
     * @var string
     */
    protected $workflowClassnamePrefix = '';


    /**
     * Create workflow instance
     *
     * @param   string  $classname
     * @param   string  $uuid
     * @return  WorkflowInterface
     */
    protected function createWorkflow($classname, $uuid = '')
    {
        $workflow = new $classname($this->serviceContainer, $uuid);

        return $workflow;
    }


    /**
     * Display workflow action
     *
     * @param   WorkflowInterface   $workflow
     * @return  string
     */
    abstract protected function fetchAction(WorkflowInterface $workflow);


    /**
     * Display workflow detail readonly without action
     *
     * @param   WorkflowInterface   $workflow
     * @return  string
     */
    protected function fetchDetail(WorkflowInterface $workflow)
    {
        return $this->fetchDetailReadonly($workflow) .
            $this->fetchLink($workflow) .
            $this->fetchLog($workflow);
    }


    /**
     * Display workflow detail editable
     *
     * @param   WorkflowInterface   $workflow
     * @return  string
     */
    abstract protected function fetchDetailEditable(WorkflowInterface $workflow);


    /**
     * Display workflow detail readonly
     *
     * @param   WorkflowInterface   $workflow
     * @return  string
     */
    abstract protected function fetchDetailReadonly(WorkflowInterface $workflow);


    /**
     * Display workflow detail editable with action
     *
     * @param   WorkflowInterface   $workflow
     * @return  string
     */
    protected function fetchEdit(WorkflowInterface $workflow)
    {
        return $this->fetchDetailEditable($workflow) .
            $this->fetchAction($workflow) .
            $this->fetchLink($workflow) .
            $this->fetchLog($workflow);
    }


    /**
     * Display workflow link
     *
     * @param   WorkflowInterface   $workflow
     * @return  string
     */
    abstract protected function fetchLink(WorkflowInterface $workflow);


    /**
     * Display workflow log
     *
     * @param   WorkflowInterface   $workflow
     * @return  string
     */
    abstract protected function fetchLog(WorkflowInterface $workflow);


    /**
     * Display workflow detail readonly with action
     *
     * @param   WorkflowInterface   $workflow
     * @return  string
     */
    protected function fetchReview(WorkflowInterface $workflow)
    {
        return $this->fetchDetailReadonly($workflow) .
            $this->fetchAction($workflow) .
            $this->fetchLink($workflow) .
            $this->fetchLog($workflow);
    }


    /**
     * {@inheritdoc}
     *
     * The action param transfer from Controler should follow format:
     * 'WorkflowClassname-view-action', if child class changed this format,
     * need to change getWorkflowClassname() and getViewAction() method too.
     */
    protected function getOutputBody($action = '')
    {
        if (empty($action)) {
            return '';
        }

        $workflowClassname = $this->getWorkflowClassname($action);
        $uuid = $this->getWorkflowUuid();
        $workflow = $this->createWorkflow($workflowClassname, $uuid);

        $workflowAction = $this->getWorkflowAction();
        if (!empty($workflowAction)) {
            $workflow->execute($workflowAction);
        }

        $viewAction = $this->getViewAction($workflowAction, $action);

        $stringUtil = $this->getUtil('StringUtil');
        $fetchMethod = $this->methodPrefix .
            $stringUtil->toStudlyCaps($viewAction);

        if (!method_exists($this, $fetchMethod)) {
            throw new \Exception(
                "View {$this->methodPrefix} method for action {$viewAction} is not defined"
            );
        }

        return $this->$fetchMethod($workflow);
    }


    /**
     * Get view action
     *
     * @param   string  $workflowAction
     * @param   string  $action
     * @return  string
     */
    protected function getViewAction($workflowAction, $action)
    {
        // Try action after execute first
        $viewAction = (isset($this->viewActionAfterExecute[$workflowAction]))
            ? $this->viewActionAfterExecute[$workflowAction]
            : '';


        // Then read from $action
        if (empty($viewAction)) {
            // This search will not return false, because there is simular
            // operate when get workflow classname, if '-' cannot found in
            // action, exception is already throwed.
            $viewAction = strstr($action, '-');

            $viewAction = substr($viewAction, 1);
        }

        return $viewAction;
    }


    /**
     * Get workflow action from user request
     *
     * @param   array   $request
     * @return  string
     */
    protected function getWorkflowAction(array $request = null)
    {
        if (is_null($request)) {
            $request = $_GET;
        }

        if (isset($request[$this->workflowActionParameter])) {
            $action = trim($request[$this->workflowActionParameter]);

        } else {
            $action = '';
        }

        return $action;
    }


    /**
     * Get workflow classname from $action
     *
     * @param   string  $action
     * @return  string
     */
    protected function getWorkflowClassname($action)
    {
        $classname = strstr($action, '-', true);

        // Without view action
        if (false === $classname) {
            throw new \Exception('View action lost');
        }

        return $this->workflowClassnamePrefix . $classname;
    }


    /**
     * Get workflow uuid from user request
     *
     * @param   array   $request
     * @return  string
     */
    protected function getWorkflowUuid(array $request = null)
    {
        if (is_null($request)) {
            $request = $_GET;
        }

        if (isset($request[$this->uuidParameter])) {
            $uuid = trim($request[$this->uuidParameter]);

        } else {
            $uuid = '';
        }

        return $uuid;
    }
}
