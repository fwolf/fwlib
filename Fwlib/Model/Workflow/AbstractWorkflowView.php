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
 * These parts can be used as normal view action too.
 *
 * This view can declare an instance of a normal view, and use it to provide
 * same header, footer with other non-workflow views.
 *
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
     * Workflow instance
     *
     * @var WorkflowInterface
     */
    protected $workflow = null;

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
     * used. The classname in url parameter will be used to create workflow
     * instance, unqualified classname means view can only create workflow
     * class instance in same namespace with them.  If workflow class is under
     * different namespace with view, their full qualified classname prefix
     * need to define here, the getWorkflowClassname() method will read and
     * add it to unqualified name.
     *
     * @var string
     */
    protected $workflowClassnamePrefix = '';


    /**
     * Create or load workflow instance
     *
     * @param   string  $classname
     * @param   string  $uuid
     * @return  WorkflowInterface
     */
    protected function createOrLoadWorkflow($classname, $uuid = '')
    {
        $this->workflow = new $classname($uuid);

        return $this->workflow;
    }


    /**
     * Display workflow action
     *
     * @return  string
     */
    abstract protected function fetchAction();


    /**
     * Display workflow detail readonly without action
     *
     * @return  string
     */
    protected function fetchDetail()
    {
        return $this->fetchDetailReadonly() .
            $this->fetchLink() .
            $this->fetchLog();
    }


    /**
     * Display workflow detail editable
     *
     * @return  string
     */
    abstract protected function fetchDetailEditable();


    /**
     * Display workflow detail readonly
     *
     * @return  string
     */
    abstract protected function fetchDetailReadonly();


    /**
     * Display workflow detail editable with action
     *
     * @return  string
     */
    protected function fetchEdit()
    {
        return $this->fetchDetailEditable() .
            $this->fetchAction() .
            $this->fetchLink() .
            $this->fetchLog();
    }


    /**
     * Display workflow link
     *
     * @return  string
     */
    abstract protected function fetchLink();


    /**
     * Display workflow log
     *
     * @return  string
     */
    abstract protected function fetchLog();


    /**
     * Display workflow detail readonly with action
     *
     * @return  string
     */
    protected function fetchReview()
    {
        return $this->fetchDetailReadonly() .
            $this->fetchAction() .
            $this->fetchLink() .
            $this->fetchLog();
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
        $this->createOrLoadWorkflow($workflowClassname, $uuid);

        $workflowAction = $this->getWorkflowAction();
        if (!empty($workflowAction)) {
            $this->workflow->execute($workflowAction);
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

        return $this->$fetchMethod();
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
