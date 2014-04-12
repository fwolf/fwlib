<?php
namespace Fwlib\Model\Workflow;

use Fwlib\Model\Workflow\WorkflowModelInterface;

/**
 * Workflow interface
 *
 * This class is mostly like a workflow manager, the access of storage is done
 * in workflow model.
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-09
 */
interface WorkflowInterface
{
    /**
     * Execute an action
     *
     * @param   string  $action
     * @return  WorkflowInterface
     */
    public function execute($action);


    /**
     * Get available action under current node
     *
     * @return  array
     */
    public function getAvailableActions();


    /**
     * Getter of workflow title
     *
     * @return  string
     */
    public static function getWorkflowTitle();


    /**
     * Is the result code measn approved ?
     *
     * @return  boolean
     */
    public function isApproved();


    /**
     * Is this workflow ended ?
     *
     * @return  boolean
     */
    public function isEnded();


    /**
     * Load workflow model instance by given uuid
     *
     * @param   string  $uuid
     * @return  WorkflowInterface
     */
    public function load($uuid);


    /**
     * Set a workflow model instance
     *
     * @param   WorkflowModelInterface  $model
     * @return  WorkflowInterface
     */
    public function setModel(WorkflowModelInterface $model);
}
