<?php
namespace Fwlib\Web\Helper;

use Fwlib\Util\UtilContainer;

/**
 * Trait of get controller class by namespace prefix
 *
 * All controller are in same namespace, with name 'ModuleController'.
 *
 * @property    string  $controllerNamespace    Include ending backslash
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait GetControllerClassByNamespaceTrait
{
    /**
     * @see ControllerTrait::getControllerClass()
     *
     * @param   string  $module
     * @return  string
     */
    protected function getControllerClass($module)
    {
        return $this->getControllerClassByNamespace($module);
    }


    /**
     * Get controller class by namespace
     *
     * @param   string  $module
     * @return  string
     */
    protected function getControllerClassByNamespace($module)
    {
        $stringUtil = UtilContainer::getInstance()->getString();

        $class = $this->controllerNamespace .
            $stringUtil->toStudlyCaps($module) . 'Controller';

        return class_exists($class) ? $class : '';
    }
}
