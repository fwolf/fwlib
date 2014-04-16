<?php
namespace Fwlib\Model\Workflow;

use Fwlib\Model\Workflow\WorkflowInterface;
use Fwlib\Mvc\AbstractView;
use Fwlib\Util\UtilContainer;

/**
 * View for workflow
 *
 * In this implement, there are 3 type of basic view action:
 *
 * - detail: readonly, without action
 * - edit  : editable, with action(nature need, at least action of save)
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
     * View action prefix
     *
     * In common, view action will include information about which workflow it
     * belongs to, used in controler. But this long view action name is not
     * convenience here. As many view of a single workflow's names always have
     * same prefix, so set here and strip it in getViewAction() to get short
     * real view action to use.
     *
     * @var string
     */
    protected $viewActionPrefix = '';

    /**
     * View action after execute workflow action
     *
     * If there are workflow action and have corresponding view action defined
     * here, the origin view action $action will be skipped in
     * getViewAction(), value of this array will act as working view action.
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
     * Workflow classname
     *
     * @var string
     */
    protected $workflowClass = '';


    /**
     * Create or load workflow instance
     *
     * @param   string  $uuid
     * @return  WorkflowInterface
     */
    protected function createOrLoadWorkflow($uuid = '')
    {
        // Manual load, to make sure an empty workflow model instance is
        // create even given empty $uuid.
        $this->workflow = new $this->workflowClass;
        $this->workflow->load($uuid);

        return $this->workflow;
    }


    /**
     * Generate view title with workflow and instance info
     *
     * @return  string
     */
    protected function generateTitle()
    {
        $title = $this->workflow->getWorkflowTitle();

        $model = $this->workflow->getModel();
        if (!is_null($model)) {
            $modelTitle = $model->getTitle();

            $title .= (empty($modelTitle)) ? '' : " - $modelTitle";
        }

        return $title;
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
     */
    protected function getOutputBody()
    {
        if (empty($this->action)) {
            return '';
        }

        $uuid = $this->getWorkflowUuid();
        $this->createOrLoadWorkflow($uuid);
        $this->setTitle($this->generateTitle());

        $workflowAction = $this->getWorkflowAction();
        if (!empty($workflowAction)) {
            $this->workflow->execute($workflowAction);
        }

        $viewAction = $this->getViewAction($workflowAction);

        $stringUtil = UtilContainer::getInstance()->get('StringUtil');
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
     * @return  string
     */
    protected function getViewAction($workflowAction)
    {
        // Try action after execute first
        $viewAction = (isset($this->viewActionAfterExecute[$workflowAction]))
            ? $this->viewActionAfterExecute[$workflowAction]
            : '';


        // Then read from $action
        if (empty($viewAction)) {
            $viewAction = preg_replace(
                "/^{$this->viewActionPrefix}/",
                '',
                $this->action
            );
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
