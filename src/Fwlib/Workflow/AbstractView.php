<?php
namespace Fwlib\Workflow;

use Fwlib\Web\AbstractView as BaseView;

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
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractView extends BaseView
{
    /**
     * When update contents, these keys will be auto received
     *
     * Value '*' means accept all keys.
     *
     * @var array|string    String '*' or array of keys.
     */
    protected $receivableContentKeys = '*';

    /**
     * Request parameter of uuid
     *
     * @var string
     */
    protected $uuidParameter = 'uuid';

    /**
     * View action after execute workflow action
     *
     * If there are workflow action and have corresponding view action defined
     * here, the origin view action $action will be skipped in
     * getViewAction(), value of this array will act as working view action.
     *
     * @var array
     */
    protected $viewActionAfterExecute = [
        'edit'   => 'edit',
        'submit' => 'detail',
    ];

    /**
     * Request parameter of view action
     *
     * @var string
     */
    protected $viewActionParameter = 'va';

    /**
     * Workflow manager instance
     *
     * @type    AbstractManager
     */
    protected $workflow = null;

    /**
     * Request parameter of workflow action
     *
     * @var string
     */
    protected $workflowActionParameter = 'wfa';

    /**
     * Workflow manager classname
     *
     * @var string
     */
    protected $workflowClass = '';


    /**
     * Build url about this workflow
     *
     * When used, more router parameter may need to be added.
     *
     * @param   string  $viewAction
     * @param   array   $queryData
     * @param   array   $removeKeys
     * @return  string  Relative url, start with '?'
     */
    public function buildQueryUrl(
        $viewAction,
        array $queryData = [],
        array $removeKeys = []
    ) {
        $params = $_GET;
        $params[$this->viewActionParameter] = $viewAction;

        if (!empty($this->workflow)) {
            $uuid = $this->workflow->getUuid();
            if (!empty($uuid)) {
                $params[$this->uuidParameter] = $uuid;
            }
        }

        $params = array_merge($params, $queryData);

        foreach ($removeKeys as $key) {
            unset($params[$key]);
        }

        $url = $this->getHtmlHelper()->getRootPath() .
            '?' . http_build_query($params);

        return $url;
    }


    /**
     * Create or load workflow instance
     *
     * @return  AbstractManager
     */
    protected function createOrLoadWorkflow()
    {
        if (is_null($this->workflow)) {
            $uuid = $this->getWorkflowUuid();
            $workflow = new $this->workflowClass($uuid);

            $this->workflow = $workflow;
        }

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
     * Generate view title
     *
     * @return  string
     */
    protected function generateTitle()
    {
        $title = $this->workflow->getWorkflowTitle();

        return $title;
    }


    /**
     * {@inheritdoc}
     */
    protected function getOutputBody()
    {
        $action = $this->getRequest()->getAction();
        if (empty($action)) {
            return '';
        }

        $workflow = $this->createOrLoadWorkflow();
        $this->setTitle($this->generateTitle());

        $workflowAction = $this->getWorkflowAction();
        if (!empty($workflowAction)) {
            $contents = $this->receiveContentsFromRequest();
            $workflow->updateContents($contents)
                ->execute($workflowAction);
        }

        $viewAction = $this->getViewAction($workflowAction);

        $stringUtil = $this->getUtilContainer()->getString();
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
     * @param   array   $request
     * @return  string
     */
    protected function getViewAction($workflowAction = '', $request = null)
    {
        // Try action after execute first
        if (empty($workflowAction)) {
            $workflowAction = $this->getWorkflowAction($request);
        }
        $viewAction = (isset($this->viewActionAfterExecute[$workflowAction]))
            ? $this->viewActionAfterExecute[$workflowAction]
            : '';


        // Then read from request
        if (empty($viewAction)) {
            $viewAction = trim($this->getRequest()->getAction());
            if (is_null($request)) {
                $request = $_GET;
            }

            if (isset($request[$this->viewActionParameter])) {
                $viewAction = trim($request[$this->viewActionParameter]);
            }
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
            $request = $_POST;
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


    /**
     * Receive contents from request
     *
     * Child class can extend to decide which keys should be received, accept
     * only specified keys is more secure.
     *
     * @return  array
     */
    protected function receiveContentsFromRequest()
    {
        $contents = $_POST;

        if ('*' != $this->receivableContentKeys) {
            $contents = array_intersect_key(
                $contents,
                array_fill_keys($this->receivableContentKeys, null)
            );
        }

        $contents = array_map('trim', $contents);

        return $contents;
    }
}
