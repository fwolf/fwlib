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
     * $request can be $_REQUEST, $_GET, $_POST.
     *
     * @param   array   $request
     * @return  string
     */
    public function getOutput(array $request = null);
}
