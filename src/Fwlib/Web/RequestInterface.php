<?php
namespace Fwlib\Web;

/**
 * Request informer
 *
 * This is abstract layer of request, for real http request, use
 * {@see \Fwlib\Util\Common\HttpUtil}.
 *
 *
 * Root path should set at application beginning, is a path to public root,
 * used for generate path of other resources, these path usually used in url.
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
     * Getter of root path
     *
     * @return  string
     */
    public function getRootPath();


    /**
     * Setter of root path
     *
     * @param   string  $rootPath
     * @return  static
     */
    public function setRootPath($rootPath);
}
