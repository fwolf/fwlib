<?php
namespace Fwlib\Mvc;

/**
 * Controller interface
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
interface ControllerInterface
{
    /**
     * Dispatch user request and get output
     *
     * Will transfer request to another Controller, or instance View to get
     * output.
     *
     * $request can be $_REQUEST, $_GET, $_POST.
     *
     * @param   array   $request
     * @return  string
     */
    public function getOutput(array $request = null);


    /**
     * Setter of $pathToRoot
     *
     * @param   string  $pathToRoot
     * @return  ControllerInterface
     */
    public function setPathToRoot($pathToRoot);
}
