<?php
namespace Fwlib\Model\Workflow;

use Fwlib\Base\ServiceContainerInterface;

/**
 * Workflow interface
 *
 * @package     Fwlib\Model\Workflow
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
     * When $uuid is assigned, will load data from storage by it and fill to
     * workflow property, this commonly need extra service like db connection,
     * so use ServiceContainer as first param.
     *
     * @param   ServiceContainerInterface   $serviceContainer
     * @param   string  $uuid
     */
    public function __construct(
        ServiceContainerInterface $serviceContainer,
        $uuid = ''
    );


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
     * @return  int|string
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
     * Getter of title
     *
     * @return  string
     */
    public function getTitle();


    /**
     * Getter of uuid
     *
     * @return  int|string
     */
    public function getUuid();


    /**
     * Is this workflow ended ?
     *
     * @return  bool
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
