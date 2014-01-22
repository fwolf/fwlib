<?php
namespace Fwlib\Mvc;

use Fwlib\Base\AbstractServiceContainer;

/**
 * Controler interface
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-24
 */
interface ControlerInterface
{
    /**
     * Dispatch user request and get output
     *
     * Will transfer request to another Controler, or instance View to get
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
     * @return  ControlerInterface
     */
    public function setPathToRoot($pathToRoot);


    /**
     * Setter of ServiceContainer instance
     *
     * ServiceContainer can inject to View, and then inject to Model.
     *
     * @param   AbstractServiceContainer    $serviceContainer
     * @return  ControlerInterface
     */
    public function setServiceContainer(
        AbstractServiceContainer $serviceContainer
    );
}
