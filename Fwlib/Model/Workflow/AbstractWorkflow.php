<?php
namespace Fwlib\Model\Workflow;

use Fwlib\Model\Workflow\Exception\InvalidActionException;
use Fwlib\Model\Workflow\WorkflowInterface;
use Fwlib\Model\Workflow\WorkflowModelInterface;

/**
 * Workflow manager
 *
 * Some method belongs/mapped to workflow model, put here for easy usage.
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-08
 */
abstract class AbstractWorkflow implements WorkflowInterface
{
    /**
     * Workflow end result code
     *
     * When workflow is ended, it can have a result code to present status
     * like approved or rejected or user canceled.
     */
    const RESULT_CODE_NOT_ENDED = 0;
    const RESULT_CODE_APPROVED = 1;
    const RESULT_CODE_REJECTED = -1;
    const RESULT_CODE_CANCELED = -2;


    /**
     * Classname of workflow model
     *
     * When start a new workflow, this classname is used to create empty model
     * instance.
     *
     * @var string
     */
    protected $modelClass = 'Fwlib\Model\Workflow\WorkflowModelInterface';

    /**
     * Workflow model instance
     *
     * @var WorkflowModelInterface
     */
    protected $model = null;

    /**
     * Workflow nodes schema array
     *
     * Should at least have one start node and one end node.
     *
     * Action name should be unique in all nodes, same action may cause error,
     * and confusion for reading code, especially when add controler/view
     * action in view or template.
     *
     * Default value of resultCode is self::RESULT_CODE_NOT_ENDED if not set.
     * ResultCode should set only on action relate to end node. When leave end
     * node(rollback), resultCode is resetted(param default value of move()),
     * or user can specify through action.  Set resultCode on other action is
     * useless.
     *
     * @var array
     */
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
            ),
        ),
        'end'   => array(
            'title'     => 'Ended',
        ),
    );

    /**
     * Not available actions
     *
     * @var array   {action: {title, message or reason}}
     */
    protected $notAvailableActions = array();

    /**
     * Workflow result code title
     *
     * @var array
     */
    protected $resultCodeTitle = array(
        self::RESULT_CODE_NOT_ENDED => 'Not Ended',
        self::RESULT_CODE_APPROVED  => 'Approved',
        self::RESULT_CODE_REJECTED  => 'Rejected',
        self::RESULT_CODE_CANCELED  => 'Canceled',
    );

    /**
     * Title of workflow class
     *
     * Usually include the description of what this workflow will do.
     *
     * @return  string
     */
    protected $workflowTitle = 'Workflow Title';


    /**
     * Constructor
     *
     * @param   string  $uuid
     */
    public function __construct($uuid = '')
    {
        if (!empty($uuid)) {
            $this->load($uuid);
        }
    }


    /**
     * Store change done in commit(), for rollback
     */
    protected function afterCommit()
    {
        // Dummy, do nothing
    }


    /**
     * Prepare to record changes in commit(), for rollback
     */
    protected function beforeCommit()
    {
        // Dummy, do nothing
    }


    /**
     * Process after workflow end and resultCode is approved
     *
     * In common, this method should write $contents to entity storage.
     *
     * If use DbDiff to store entity db change, there will have an extra
     * UPDATE to db (the former one is save()), by this cost, the workflow got
     * possibility to rollback from end node.
     *
     * Workflow may have no rollback ablity, but should commit something, so
     * commit() is abstract and must fill by child class, as rollback() is
     * default empty.
     */
    abstract protected function commit();


    /**
     * {@inheritdoc}
     *
     * In default, this method only include updateContents(), user should
     * define customized executeAction() method to do extra job like convert
     * form input data, this method should not include move() anymore. To set
     * specified resultCode when change node, set it in action property in
     * $nodes define array.
     */
    public function execute($action)
    {
        // Must have a model instance
        if (empty($this->model)) {
            $this->load('');
        }

        // User method should decide whether or how to call updateContents()
        $method = 'execute' . ucfirst($action);
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $this->updateContents();
        }

        // Check action available by updated state, not original
        if (!$this->isActionAvailable($action)) {
            throw new InvalidActionException(
                "Invalid or not allowed action $action"
            );
        }

        $actionArray = $this->nodes[$this->model->getCurrentNode()]
            ['actions'][$action];
        $this->move(
            $action,
            $actionArray['next'],
            (isset($actionArray['resultCode']) ? $actionArray['resultCode']
                : static::RESULT_CODE_NOT_ENDED)
        );

        return $this;
    }


    /**
     * Find links/relations the workflow instance have
     *
     * @return  array|null  Return null to disable save of link
     */
    protected function findLinks()
    {
        // Dummy, return null
        return null;
    }


    /**
     * Getter of $notAvailableActions
     *
     * @return  array
     */
    public function getNotAvailableActions()
    {
        return $this->notAvailableActions;
    }


    /**
     * {@inheritdoc}
     */
    public function getAvailableActions()
    {
        $availableActions = array();
        if (!isset($this->nodes[$this->model->getCurrentNode()]['actions'])) {
            return $availableActions;
        }

        foreach ($this->nodes[$this->model->getCurrentNode()] ['actions'] as
            $action => $actionArray) {

            if ($this->isActionAvailable($action)) {
                $availableActions[$action] = $actionArray;
            }
        }

        return $availableActions;
    }


    /**
     * Getter of single content
     *
     * @param   string  $key
     * @return  array
     */
    public function getContent($key)
    {
        return $this->model->getContent($key);
    }


    /**
     * Getter of whole content array
     *
     * @return  array
     */
    public function getContents()
    {
        return $this->model->getContents();
    }


    /**
     * Getter of current node
     *
     * @return  string
     */
    public function getCurrentNode()
    {
        return $this->model->getCurrentNode();
    }


    /**
     * Getter of current node title
     *
     * @return  string
     */
    public function getCurrentNodeTitle()
    {
        $node = $this->model->getCurrentNode();

        return $this->nodes[$node]['title'];
    }


    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        return $this->model;
    }


    /**
     * {@inheritdoc}
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }


    /**
     * Getter of result code
     *
     * @return  int
     */
    public function getResultCode()
    {
        return $this->model->getResultCode();
    }


    /**
     * Get title of result code
     *
     * If $resultCode is null, will get result code of $model, otherwise will
     * return title for given result code.
     *
     * @param   int     $resultCode
     * @return  string
     */
    public function getResultCodeTitle($resultCode = null)
    {
        if (is_null($resultCode)) {
            $resultCode = $this->model->getResultCode();
        }

        return $this->resultCodeTitle[$resultCode];
    }


    /**
     * Getter of title
     *
     * @return  string
     */
    public function getTitle()
    {
        return $this->model->getTitle();
    }


    /**
     * Getter of uuid
     *
     * @return  string
     */
    public function getUuid()
    {
        return $this->model->getUuid();
    }


    /**
     * {@inheritdoc}
     */
    public function getWorkflowTitle()
    {
        return $this->workflowTitle;
    }


    /**
     * Initialize an empty workflow instance
     */
    protected function initialize()
    {
        // Prepare content or assign default value to model
        $this->model->setResultCode(static::RESULT_CODE_NOT_ENDED);
    }


    /**
     * Is an action available ?
     *
     * Only actions of current node can be available, and default available.
     *
     * User should not extend this method directly, instead, user can create
     * customize check method for any single $action, named as
     * isAction[ActionName]Available(). These method should explicit return
     * true to pass available check, other return value will be consider as
     * check fail, and will be saved as fail reason/message in property
     * $notAvailableActions. This property can be used to show user why these
     * action can't execute.
     *
     * This is more flexible than complicated condition string.
     *
     * @param   string  $action
     * @return  boolean
     */
    public function isActionAvailable($action)
    {
        if (!isset(
            $this->nodes[$this->model->getCurrentNode()]['actions'][$action]
        )) {
            return false;
        }

        // Use action specified check method
        $method = "isAction" . ucfirst($action) . "Available";
        if (method_exists($this, $method)) {
            $checkResult = $this->$method();

            if (true !== $checkResult) {
                $this->notAvailableActions[$action] = array(
                    'title'   => $this->nodes[$this->model->getCurrentNode()]
                        ['actions'][$action]['title'],
                    'message' => (string)$checkResult,
                );
                return false;
            }
        }

        unset($this->notAvailableActions[$action]);
        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function isApproved()
    {
        return static::RESULT_CODE_APPROVED == $this->model->getResultCode();
    }


    /**
     * {@inheritdoc}
     */
    public function isEnded()
    {
        return 'end' == $this->model->getCurrentNode();
    }


    /**
     * {@inheritdoc}
     */
    public function load($uuid)
    {
        $this->model = new $this->modelClass($uuid);

        // Check and initlize model instance
        if (0 == strlen($this->model->getUuid())) {
            $this->initialize();
        }

        return $this;
    }


    /**
     * Move workflow to another node
     *
     * After workflow move to end node and is approved, the method commit()
     * will be called, the reverse operate is rollback(), called when node
     * leave from end. The end result rejected or canceled has no alike
     * mechanishm, because in common nothing need to do, although child class
     * can extend this method to add that.
     *
     * @param   string  $action     Moved by action
     * @param   string  $node
     * @param   int     $resultCode Should set when to or from end node.
     * @return  AbstractWorkflow
     */
    protected function move(
        $action,
        $node,
        $resultCode = self::RESULT_CODE_NOT_ENDED
    ) {
        $prevIsApproved = $this->isApproved();
        $prevIsEnd = $this->isEnded();
        $prevNode = $this->model->getCurrentNode();

        $this->model->setCurrentNode($node);
        $this->model->setResultCode($resultCode);
        $currentIsApproved = $this->isApproved();
        $currentIsEnd = $this->isEnded();

        // Safe check for concurrence, if two user end a workflow at same
        // time, the later one will throw error.
        if ($currentIsEnd && $prevIsEnd) {
            throw new \Exception(
                'Workflow can\'t end twice'
            );
        }

        $this->save();

        if ($prevNode != $node) {
            $this->saveLog($action, $prevNode, $node);
        }

        if ($currentIsEnd && $currentIsApproved) {
            $this->beforeCommit();
            $this->commit();
            $this->afterCommit();

        } elseif ($prevIsEnd && $prevIsApproved) {
            $this->rollback();
        }

        return $this;
    }


    /**
     * Receive contents from request
     *
     * Child can extend to do more prepare work on contents before update.
     *
     * @return  array
     */
    protected function receiveContentsFromRequest()
    {
        $contents = $_POST;

        // For security, better specify which keys to pick
        //$contents = array_intersect_key($_POST, array_fill_keys($keys, null));

        return $contents;
    }


    /**
     * Rollback data written by commit()
     */
    protected function rollback()
    {
        // Dummy, do nothing
    }


    /**
     * Save workflow
     *
     * For new created workflow instance, save() method should generate and
     * update $uuid property.
     */
    protected function save()
    {
        $this->model->save();

        $links = $this->findLinks();
        $this->model->syncLinks($links);
    }


    /**
     * Save workflow change log
     *
     * Log is only saved when node change.
     *
     * @param   string  $action
     * @param   string  $prevNode
     * @param   string  $nextNode
     */
    protected function saveLog($action, $prevNode, $nextNode)
    {
        $actionTitle = $this->nodes[$prevNode]['actions'][$action]['title'];

        $this->model->addLog($action, $actionTitle, $prevNode, $nextNode);
    }


    /**
     * {@inheritdoc}
     */
    public function setModel(WorkflowModelInterface $model)
    {
        $this->model = $model;

        return $this;
    }


    /**
     * Change action title
     *
     * Sometimes action title can't fit both detail and review view mode. For
     * example, an action with title 'Save' works fine in review mode. But in
     * detail mode, the title 'Go to Edit' is more suitable.
     *
     * Note: choose a appropriate title is better than do modify like this.
     *
     * @param   string  $node
     * @param   string  $action
     * @param   string  $title
     * @return  AbstractWorkflow
     */
    public function setNodeActionTitle($node, $action, $title)
    {
        $this->nodes[$node]['actions'][$action]['title'] = $title;

        return $this;
    }


    /**
     * Update $contents, will be called when execute action
     *
     * @param   array   $contents
     * @return  AbstractWorkflow
     */
    protected function updateContents(array $contents = null)
    {
        if (is_null($contents)) {
            $contents = $this->receiveContentsFromRequest();
        }

        if (!empty($contents)) {
            $this->model->setContents(
                array_merge($this->model->getContents(), $contents)
            );

            $this->updateModelByContents();
        }

        return $this;
    }


    /**
     * Update model information by contents
     *
     * Model property that not depends on contents should set in initialize()
     * or executeAction() method.
     */
    protected function updateModelByContents()
    {
        // Dummy
    }
}
