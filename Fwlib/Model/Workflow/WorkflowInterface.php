<?php
namespace Fwlib\Model\Workflow;


/**
 * Workflow interface
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-09
 */
interface WorkflowInterface
{
    /**
     * Constructor
     *
     * @param   string  $uuid
     */
    public function __construct($uuid = '');


    /**
     * Execute an action
     *
     * @param   string  $action
     * @return  WorkflowInterface
     */
    public function execute($action);


    /**
     * Get available action currently
     *
     * @return  array
     */
    public function getAvailableAction();


    /**
     * Getter of content
     *
     * @return  array
     */
    public function getContent();


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
     * Getter of result code title
     *
     * @return  string
     */
    public function getResultCodeTitle();


    /**
     * Getter of workflow instance title
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
     * Getter of workflow title
     *
     * @return  string
     */
    public static function getWorkflowTitle();


    /**
     * Is this workflow ended ?
     *
     * @return  boolean
     */
    public function isEnded();


    /**
     * Load workflow by given uuid
     *
     * @param   string  $uuid
     * @return  WorkflowInterface
     */
    public function load($uuid);
}
