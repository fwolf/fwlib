<?php
namespace Fwlib\Web;

/**
 * Request informer
 *
 * This is abstract layer of request, for real http request, use
 * {@see \Fwlib\Util\Common\HttpUtil}.
 *
 *
 * Module and action are used by router or controller, to deliver request to
 * its processor. Whatever its named, in common application two level
 * hierarchical is enough.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface RequestInterface
{
    /**
     * Get current action
     *
     * @return  string
     */
    public function getAction();


    /**
     * Get current module
     *
     * @return  string
     */
    public function getModule();


    /**
     * @param   string $action
     * @return  $this
     */
    public function setAction($action);


    /**
     * @param   string $module
     * @return  $this
     */
    public function setModule($module);
}
