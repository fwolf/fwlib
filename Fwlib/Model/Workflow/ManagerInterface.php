<?php
namespace Fwlib\Model\Workflow;

use Fwlib\Model\Workflow\ModelInterface;

/**
 * Workflow manager interface
 *
 * Control workflow execution, get available actions and other information.
 * The access of storage is done by workflow model.
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-01-09
 */
interface ManagerInterface
{
    /**
     * Execute an action
     *
     * @param   string  $action
     * @return  ManagerInterface
     */
    public function execute($action);


    /**
     * Get available action under current node
     *
     * @return  array
     */
    public function getAvailableActions();


    /**
     * Getter of workflow model instance
     *
     * @return  ModelInterface
     */
    public function getModel();


    /**
     * Getter of workflow model class
     *
     * @return  string
     */
    public function getModelClass();


    /**
     * Getter of workflow title
     *
     * @return  string
     */
    public function getWorkflowTitle();


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
     * @return  ManagerInterface
     */
    public function load($uuid);


    /**
     * Set a workflow model instance
     *
     * @param   ModelInterface  $model
     * @return  ManagerInterface
     */
    public function setModel(ModelInterface $model);
}
