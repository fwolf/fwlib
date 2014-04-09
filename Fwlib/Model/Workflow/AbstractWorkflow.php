<?php
namespace Fwlib\Model\Workflow;

use Fwlib\Model\Workflow\WorkflowInterface;

/**
 * Workflow instance
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
     * Message/reason of action not available
     *
     * @var array   {action: message}
     */
    protected $actionNotAvailableMessage = array();

    /**
     * Content which workflow carried
     *
     * This doesn't include workflow property like uuid, currentNode etc.
     *
     * @var array
     */
    protected $content = array();

    /**
     * Current node of workflow
     *
     * This can't be empty, and should be any invalid valie not defined in
     * $node. Method save() may write log with prev node '', its only a marker
     * of workflow create, will not be currentNode of any workflow intance.
     *
     * @var string
     */
    protected $currentNode = 'start';

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
     * resultCode should set only on action point to end node, set on other
     * action is meanless.
     *
     * @var array
     */
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
            ),
        ),
        'end'   => array(
            'title'     => 'Ended',
        ),
    );

    /**
     * Workflow result code
     *
     * @var int
     */
    protected $resultCode = self::RESULT_CODE_NOT_ENDED;

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
     * Title of workflow
     *
     * Can be used in view or log.
     *
     * @var string
     */
    protected $title = 'AbstractWorkflow';

    /**
     * Workflow instance uuid
     *
     * @var string
     */
    protected $uuid = '';


    /**
     * {@inheritdoc}
     */
    public function __construct($uuid = '')
    {
        if (!empty($uuid)) {
            $this->load($uuid);
        }
    }


    /**
     * Process after workflow end and resultCode is approved
     *
     * In common, this method should write $content to entity storage.
     *
     * If use DbDiff to store entity db change, there will have an extra
     * UPDATE to db (the former one is save()), by this cost, the workflow got
     * possibility to rollback from end node.
     */
    protected function commit()
    {
        // Dummy, do nothing
    }


    /**
     * {@inheritdoc}
     *
     * In default, this method only include updateContent(), user should
     * define customized executeAction() method to do extra job like convert
     * form input data, this method should not include moveTo() anymore. To
     * set specified resultCode when change node, set it in action property in
     * $nodes define array.
     */
    public function execute($action)
    {
        if (!$this->isActionAvailable($action)) {
            throw new \Exception("Invalid or not allowed action $action");
        }

        if (empty($this->uuid)) {
            $this->initialize();
        }

        // User method should decide whether or how to call updateContent()
        $method = 'execute' . ucfirst($action);
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $this->updateContent();
        }

        $actionArray = $this->nodes[$this->currentNode]['action'][$action];
        $this->moveTo(
            $actionArray['next'],
            (isset($actionArray['resultCode']) ? $actionArray['resultCode']
                : self::RESULT_CODE_NOT_ENDED)
        );

        return $this;
    }


    /**
     * Getter of $actionNotAvailableMessage
     *
     * @return  array
     */
    public function getActionNotAvailableMessage()
    {
        return $this->actionNotAvailableMessage;
    }


    /**
     * {@inheritdoc}
     */
    public function getAvailableAction()
    {
        $availableAction = array();
        foreach ((array)$this->nodes[$this->currentNode]['action'] as
            $action => $actionArray) {

            if ($this->isActionAvailable($action)) {
                $availableAction[$action] = $actionArray;
            }
        }

        return $availableAction;
    }


    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->content;
    }


    /**
     * {@inheritdoc}
     */
    public function getCurrentNode()
    {
        return $this->currentNode;
    }


    /**
     * {@inheritdoc}
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }


    /**
     * {@inheritdoc}
     */
    public function getResultCodeTitle()
    {
        return $this->resultCodeTitle[$this->resultCode];
    }


    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * {@inheritdoc}
     */
    public function getUuid()
    {
        return $this->uuid;
    }


    /**
     * Initialize an empty workflow instance
     *
     * The load() method is readed from an exists instance data, so it will
     * skip this method.
     */
    protected function initialize()
    {
        // Prepare content for later work, or do nothing

        // Optional: Log workflow create operate, mark node changed from empty
        // to start node 'start'.
        $this->saveLog('');
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
     * $actionNotAvailableMessage. This property can be used to show user why
     * these action can't execute.
     *
     * This is more flexible than complicated condition string.
     *
     * @param   string  $action
     * @return  boolean
     */
    public function isActionAvailable($action)
    {
        if (!isset($this->nodes[$this->currentNode]['action'][$action])) {
            return false;
        }

        // Use action specified check method
        $method = "isAction" . ucfirst($action) . "Available";
        if (method_exists($this, $method)) {
            $checkResult = $this->$method();

            if (true !== $checkResult) {
                $this->actionNotAvailableMessage[$action] =
                    (string)$checkResult;
                return false;
            }
        }

        unset($this->actionNotAvailableMessage[$action]);
        return true;
    }


    /**
     * Is the result code measn approved ?
     *
     * @return  bool
     */
    public function isApproved()
    {
        return self::RESULT_CODE_APPROVED == $this->resultCode;
    }


    /**
     * {@inheritdoc}
     */
    public function isEnded()
    {
        return 'end' == $this->currentNode;
    }


    /**
     * {@inheritdoc}
     */
    abstract public function load($uuid);


    /**
     * Move workflow to another node
     *
     * After workflow move to end node and is approved, the method commit()
     * will be called, the reverse operate is rollback(), called when node
     * leave from end. The end result rejected or canceled has no alike
     * mechanishm, because in common nothing need to do, although child class
     * can extend this method to add that.
     *
     * @param   string  $node
     * @param   int     $resultCode Should set when to or from end node.
     * @return  AbstractWorkflow
     */
    protected function moveTo($node, $resultCode = self::RESULT_CODE_NOT_ENDED)
    {
        $prevIsApproved = $this->isApproved();
        $prevIsEnd = $this->isEnded();
        $prevNode = $this->currentNode;

        $this->currentNode = $node;
        $this->resultCode = $resultCode;
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
            $this->saveLog($prevNode);
        }

        if ($currentIsEnd && $currentIsApproved) {
            $this->commit();

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
    abstract protected function save();


    /**
     * Save workflow link relation
     *
     * Should be called in save() method.
     */
    abstract protected function saveLink();


    /**
     * Save workflow change log
     *
     * Log is only saved when node change.
     *
     * @param   string  $prevNode
     */
    abstract protected function saveLog($prevNode);


    /**
     * Update $content when execute action
     *
     * @param   array   $data
     * @return  AbstractWorkflow
     */
    protected function updateContent(array $data = null)
    {
        if (is_null($data)) {
            $data = $_POST;
        }

        $this->content = array_merge($this->content, $data);

        return $this;
    }
}
