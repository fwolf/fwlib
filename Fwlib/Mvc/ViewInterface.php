<?php
namespace Fwlib\Mvc;

use Fwlib\Base\AbstractServiceContainer;

/**
 * View interface
 *
 * @package     Fwlib\Mvc
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-24
 */
interface ViewInterface
{
    /**
     * Generate output for given action
     *
     * @param   array   $action
     * @return  string
     */
    public function getOutput($action);


    /**
     * Setter of ServiceContainer instance
     *
     * ServiceContainer can inject to Model.
     *
     * @param   AbstractServiceContainer    $serviceContainer
     * @return  ViewInterface
     */
    public function setServiceContainer(
        AbstractServiceContainer $serviceContainer
    );
}
