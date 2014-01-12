<?php
namespace Fwlib\Model\Workflow;

use Fwlib\Base\ServiceContainerInterface;
use Fwlib\Model\Workflow\WorkflowInterface;
use Fwlib\Mvc\AbstractModel;

/**
 * Workflow instance
 *
 * @package     Fwlib\Model\Workflow
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-08
 */
abstract class AbstractWorkflow extends AbstractModel implements
    WorkflowInterface
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
     * Exported DbDiff json string
     *
     * @var string
     */
    protected $dbDiff = '';

    /**
     * Workflow node schema array
     *
     * Should at least have one start node and one end node.
     *
     * Action name should be unique in all nodes, same action may cause error,
     * and confusion for reading code, especially when add controler/view
     * action in view or template.
     *
     * Default value of resultCode is self::RESULT_CODE_NOT_ENDED if not set.
     * ResultCode should set only on action point to end node, set on other
     * action is meanless.
     *
     * Available of action is default true if key 'availableCheck' is not set
     * or empty.  A method name string can be assigned to the key, then this
     * action will only be available when this method return true. Different
     * action can share same check method, or have their own check method.
     *
     * @var array
     */
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
    public function __construct(
        ServiceContainerInterface $serviceContainer,
        $uuid = ''
    ) {
        $this->serviceContainer = $serviceContainer;

        if (!empty($uuid)) {
            $this->load($uuid);
        }
    }


    /**
     * Check if an action is valid and available
     *
     * @param   string  $action
     * @return  bool
     */
    protected function checkActionAvailable($action)
    {
        if (!isset($this->node[$this->currentNode]['action'][$action])) {
            return false;
        }

        $actionArray = $this->node[$this->currentNode]['action'][$action];

        if (empty($actionArray['availableCheck'])) {
            return true;
        }

        $method = $actionArray['availableCheck'];
        return $this->$method($action);
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
    abstract protected function commit();


    /**
     * {@inheritdoc}
     *
     * In default, this method only include updateContent(), user should
     * define customized executeAction() method to do extra job like convert
     * form input data, this method need not include moveTo() anymore. To set
     * specified resultCode when change node, set it in action property in
     * $node define array.
     */
    public function execute($action)
    {
        if (!$this->checkActionAvailable($action)) {
            throw new \Exception("Invalid or not allowed action $action");
        }

        if (empty($this->uuid)) {
            $this->initialize();
        }

        $method = 'execute' . ucfirst($action);
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $this->updateContent();
        }

        $actionArray = $this->node[$this->currentNode]['action'][$action];
        $this->moveTo(
            $actionArray['next'],
            (isset($actionArray['resultCode']) ? $actionArray['resultCode']
                : self::RESULT_CODE_NOT_ENDED)
        );

        return $this;
    }


    /**
     * Generate an uuid
     *
     * @return  string
     */
    protected function generateUuid()
    {
        $uuidUtil = $this->getUtil('UuidBase36');

        return $uuidUtil->generate();
    }


    /**
     * {@inheritdoc}
     */
    public function getAvailableAction()
    {
        $availableAction = array();
        foreach ((array)$this->node[$this->currentNode]['action'] as
            $action => $actionArray) {

            if ($this->checkActionAvailable($action)) {
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
     * Initialize this workflow instance as new
     *
     * The load() method is readed from an exists instance data, so it will
     * skip this method.
     */
    protected function initialize()
    {
        $this->uuid = $this->generateUuid();

        // Other property can be extend and assign by child class

        // Optional: Log workflow create operate, mark node changed from empty
        // to start node 'start'.
        $this->saveLog('');
    }


    /**
     * Is an action available ?
     *
     * There need not to check the action belongs to currentNode, because the
     * caller of this method will only check for action under currentNode.
     *
     * Additional auth, privilege and other check can also applied here, this
     * is more flexible than condition string.
     *
     * @param   string  $action
     * @return  bool
     */
    protected function isActionAvailable($action)
    {
        // This is only a dummy, child class should rewrite logic.
        if ('submit' == $action && true) {
            return true;
        }

        return false;
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
        $dbDiff = $this->getService('DbDiff');
        $dbDiff->import($this->dbDiff)
            ->rollback();
    }


    /**
     * Save workflow
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
     * @return  AbstractWorkflow
     */
    protected function updateContent()
    {
        $this->content = array_merge($this->content, $_POST);

        return $this;
    }
}
