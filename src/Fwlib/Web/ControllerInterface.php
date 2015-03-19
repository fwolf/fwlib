<?php
namespace Fwlib\Web;

/**
 * Controller interface
 *
 * In application, controller is commonly initialized in index.php as entry,
 * the main purpose is to route user request(via {@see \Fwlib\Web\Request}) to
 * various view, to retrieve output content.
 *
 * Also, it can delegate request to other controller, so every module can have
 * their own index too, although this is not common case.
 *
 * In this case, controller is also router.
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
