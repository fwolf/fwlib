<?php
namespace Fwlib\Web;

/**
 * View interface
 *
 * Called by controller and generate output content.
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface ViewInterface
{
    /**
     * Generate output for given action
     *
     * @return  string
     */
    public function getOutput();


    /**
     * Setter of request instance
     *
     * @param   RequestInterface    $request
     * @return  static
     */
    public function setRequest(RequestInterface $request);
}
