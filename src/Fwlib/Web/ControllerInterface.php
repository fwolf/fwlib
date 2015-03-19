<?php
namespace Fwlib\Web;

/**
 * Controller interface
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface ControllerInterface
{
    /**
     * Dispatch user request and get output
     *
     * Will transfer request to another Controller, or instance View to get
     * output.
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
