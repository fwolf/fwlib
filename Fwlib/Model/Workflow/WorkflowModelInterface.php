<?php
namespace Fwlib\Model\Workflow;


/**
 * Workflow model interface
 *
 * Only deal with storage relate operate, should have no bussiness logic done
 * here.
 *
 * All bussiness relate information are stored in $content, but doesn't
 * include workflow instance property like uuid, currentNode etc.
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-04-12
 */
interface WorkflowModelInterface
{
    /**
     * Add a workflow log
     *
     * Log can only be added, can't do remove on them.
     *
     * @param   string  $action
     * @param   string  $actionTitle
     * @param   string  $prevNode
     * @param   string  $nextNode
     * @return  WorkflowModelInterface
     */
    public function addLog($action, $actionTitle, $prevNode, $nextNode);


    /**
     * Get single key of content
     *
     * @param   string  $key
     * @return  mixed
     */
    public function getContent($key);


    /**
     * Get whole content array
     *
     * @return  array
     */
    public function getContents();


    /**
     * Getter of current node
     *
     * @return  string
     */
    public function getCurrentNode();


    /**
     * Getter of result code
     *
     * @return  int
     */
    public function getResultCode();


    /**
     * Getter of title
     *
     * @return  string
     */
    public function getTitle();


    /**
     * Getter of uuid
     *
     * @return  string
     */
    public function getUuid();


    /**
     * Save to storage
     *
     * @return  WorkflowModelInterface
     */
    public function save();


    /**
     * Set single key of content
     *
     * @param   string  $key
     * @param   mixed   $value
     * @return  WorkflowModelInterface
     */
    public function setContent($key, $value);


    /**
     * Set multiple key of content
     *
     * @param   array   $data
     * @return  WorkflowModelInterface
     */
    public function setContents($data);


    /**
     * Setter of current node
     *
     * The current node should not be empty, and should be invalid value
     * defined in workflow manager class. The only exception is method
     * saveLog() may write log with prev node '', to mark the start of a
     * workflow instance.
     *
     * @param   string  $node
     * @return  WorkflowModelInterface
     */
    public function setCurrentNode($node);


    /**
     * Setter of result code
     *
     * @return  int
     */
    public function setResultCode($code);


    /**
     * Setter of title
     *
     * @param   string  $title
     * @return  WorkflowModelInterface
     */
    public function setTitle($title);


    /**
     * Sync links with storage
     *
     * Links can be added or removed.
     *
     * @param   array   $links
     * @return  WorkflowModelInterface
     */
    public function syncLinks($links);
}
