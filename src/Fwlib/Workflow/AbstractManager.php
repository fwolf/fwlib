<?php
namespace Fwlib\Workflow;

use Fwlib\Workflow\Exception\InvalidActionException;

/**
 * Workflow manager
 *
 * Some method belongs/mapped to workflow model, put here for easy usage.
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractManager implements ManagerInterface
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
     * Storage of disabled actions
     *
     * {node, action: {}}
     *
     * @var array
     */
    protected $disabledActions = [];

    /**
     * Workflow model instance
     *
     * @var ModelInterface
     */
    protected $model = null;

    /**
     * Classname of workflow model
     *
     * When start a new workflow, this classname is used to create empty model
     * instance.
     *
     * @var string
     */
    protected $modelClass = ModelInterface::class;

    /**
     * Workflow nodes schema array
     *
     * Should at least have one start node and one end node.
     *
     * Action name should be unique in all nodes, same action may cause error,
     * and confusion for reading code, especially when add controller/view
     * action in view or template.
     *
     * Default value of resultCode is self::RESULT_CODE_NOT_ENDED if not set.
     * ResultCode should set only on action relate to end node. When leave end
     * node(rollback), resultCode is reset(param default value of move()), or
     * user can specify through action.  Set resultCode on other action is
     * useless.
     *
     * The end node should not have action 'next' point to itself, that action
     * will always trigger both commit() and rollback(). A check for this in
     * move() will throw Exception 'end twice'.
     *
     * @var array
     */
    protected $nodes = [
        'start' => [
            'title'     => 'Started',
            'actions'   => [
                'edit'      => [
                    'title' => 'Edit',
                    'next'  => 'start',
                ],
                'submit'    => [
                    'title' => 'Submit',
                    'next'  => 'end',
                    'resultCode'     => self::RESULT_CODE_APPROVED,
                ],
            ],
        ],
        'end'   => [
            'title'     => 'Ended',
        ],
    ];

    /**
     * Not available actions
     *
     * @var array   {action: {title, message or reason}}
     */
    protected $notAvailableActions = [];

    /**
     * Workflow result code title
     *
     * @var array
     */
    protected $resultCodeTitle = [
        self::RESULT_CODE_NOT_ENDED => 'Not Ended',
        self::RESULT_CODE_APPROVED  => 'Approved',
        self::RESULT_CODE_REJECTED  => 'Rejected',
        self::RESULT_CODE_CANCELED  => 'Canceled',
    ];

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
        $this->load($uuid);
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
     * Workflow may have no rollback ability, but should commit something, so
     * commit() is abstract and must fill by child class, as rollback() is
     * default empty.
     */
    abstract protected function commit();


    /**
     * {@inheritdoc}
     */
    public function disableAction($action)
    {
        foreach ($this->nodes as $nodeIndex => &$node) {
            if (isset($node['actions'][$action])) {
                $this->disabledActions[$action] = [
                    'node'   => $nodeIndex,
                    'action' => $node['actions'][$action],
                ];

                unset($node['actions'][$action]);

                break;
            }
        }
        unset($node);

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function disableActions(array $actions)
    {
        foreach ($this->nodes as $nodeIndex => &$node) {
            // Some node like end has no actions
            if (!array_key_exists('actions', $node)) {
                continue;
            }

            foreach ($node['actions'] as $action => $value) {
                if (in_array($action, $actions)) {
                    $this->disabledActions[$action] = [
                        'node'   => $nodeIndex,
                        'action' => $node['actions'][$action],
                    ];

                    unset($node['actions'][$action]);
                }
            }
        }
        unset($node);

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function enableAction($action)
    {
        if (isset($this->disabledActions[$action])) {
            $this->nodes[$this->disabledActions[$action]['node']]['actions']
                [$action] = $this->disabledActions[$action]['action'];

            unset($this->disabledActions[$action]);
        }

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function enableActions(array $actions)
    {
        foreach ($actions as $action) {
            $this->enableAction($action);
        }

        return $this;
    }


    /**
     * {@inheritdoc}
     *
     * User can define customized executeAction() method to do extra job like
     * generate profile code. this method should not include move() anymore,
     * nor should this method set resultCode, that should be set as action
     * property in $nodes define array.
     */
    public function execute($action)
    {
        // Contents are changed by updateContents() from outside, before this.

        $method = 'execute' . ucfirst($action);
        if (method_exists($this, $method)) {
            $this->$method();
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
     * Get title of action, will search action in nodes
     *
     * @param   string  $action
     * @return  string
     * @throws  \Exception  If nodes use invalid action
     */
    public function getActionTitle($action)
    {
        foreach ($this->nodes as &$node) {
            if (isset($node['actions'][$action])) {
                return $node['actions'][$action]['title'];
            }
        }
        unset($node);

        throw new \Exception("Get title from invalid action $action");
    }


    /**
     * {@inheritdoc}
     */
    public function getAvailableActions()
    {
        $availableActions = [];
        if (!isset($this->nodes[$this->model->getCurrentNode()]['actions'])) {
            return $availableActions;
        }

        $actions = $this->nodes[$this->model->getCurrentNode()]['actions'];
        foreach ($actions as $action => $actionArray) {
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
     * Getter of $notAvailableActions
     *
     * @return  array
     */
    public function getNotAvailableActions()
    {
        return $this->notAvailableActions;
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
                $this->notAvailableActions[$action] = [
                    'title'   => $this->nodes[$this->model->getCurrentNode()]
                        ['actions'][$action]['title'],
                    'message' => (string)$checkResult,
                ];
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
    public function limitActions(array $actions)
    {
        foreach ($this->nodes as $nodeIndex => &$node) {
            // Some node like end has no actions
            if (!array_key_exists('actions', $node)) {
                continue;
            }

            foreach ($node['actions'] as $action => $value) {
                if (!in_array($action, $actions)) {
                    $this->disabledActions[$action] = [
                        'node'   => $nodeIndex,
                        'action' => $node['actions'][$action],
                    ];

                    unset($node['actions'][$action]);
                }
            }
        }
        unset($node);

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function load($uuid)
    {
        $this->model = new $this->modelClass($uuid);

        // Check and initialize model instance
        if (empty($uuid)) {
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
     * mechanism, because in common nothing need to do, although child class
     * can extend this method to add that.
     *
     * @param   string  $action     Moved by action
     * @param   string  $node
     * @param   int     $resultCode Should set when to or from end node.
     * @return  AbstractManager
     * @throws  \Exception  If 2 user end a workflow at same time.
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
    public function setModel(ModelInterface $model)
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
     * @return  AbstractManager
     */
    public function setNodeActionTitle($node, $action, $title)
    {
        $this->nodes[$node]['actions'][$action]['title'] = $title;

        return $this;
    }


    /**
     * Update $contents
     *
     * Eg: Assign filtered $_POST data from View.
     *
     * @param   array   $contents
     * @return  AbstractManager
     */
    public function updateContents(array $contents = null)
    {
        if (!empty($contents)) {
            $this->model->setContents(
                array_merge($this->model->getContents(), $contents)
            );
        }

        // Even request may contain no data, there may have preset readonly
        // information in contents, and model need to be updated.
        $this->updateModelByContents();

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
