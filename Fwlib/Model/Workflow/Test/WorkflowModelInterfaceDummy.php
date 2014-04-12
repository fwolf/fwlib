<?php
namespace Fwlib\Model\Workflow\Test;

use Fwlib\Model\Workflow\WorkflowModelInterface;

class WorkflowModelInterfaceDummy implements WorkflowModelInterface
{
    public $contents = array();
    public $currentNode = 'start';
    public $links = array();
    public $logs = array();
    public $resultCode = 0;
    public $title = 'Workflow Model Interface Dummy';
    public $uuid = '';


    public function __construct($uuid = '')
    {
        $this->uuid = $uuid;
    }


    public function addLog($action, $actionTitle, $prevNode, $nextNode)
    {
        $this->logs[] = array(
            'action'    => $action,
            'actionTitle'   => $actionTitle,
            'prevNode'  => $prevNode,
            'nextNode'  => $nextNode,
        );

        return $this;
    }


    public function getContent($key)
    {
        return $this->contents[$key];
    }


    public function getContents()
    {
        return $this->contents;
    }


    public function getCurrentNode()
    {
        return $this->currentNode;
    }


    public function getLogs()
    {
        return $this->logs;
    }


    public function getResultCode()
    {
        return $this->resultCode;
    }


    public function getTitle()
    {
        return $this->title;
    }


    public function getUuid()
    {
        return $this->uuid;
    }


    public function save()
    {
        return $this;
    }


    public function setContent($key, $value)
    {
        $this->contents[$key] = $value;

        return $this;
    }


    public function setContents($data)
    {
        $this->contents = array_merge($this->contents, $data);

        return $this;
    }


    public function setCurrentNode($node)
    {
        $this->currentNode = $node;

        return $this;
    }


    public function setResultCode($code)
    {
        $this->resultCode = $code;

        return $this;
    }


    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }


    public function syncLinks($links)
    {
        $this->links = $links;

        return $this;
    }
}
