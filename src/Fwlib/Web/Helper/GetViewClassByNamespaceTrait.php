<?php
namespace Fwlib\Web\Helper;

use Fwlib\Util\UtilContainer;

/**
 * Trait of get view class by namespace prefix
 *
 * All view are in same namespace, base class name is converted by chain of
 * module and action, eg for module 'foo' and action 'do-job', the base class
 * name can be(by match order):
 *
 *  - Foo\DoJob
 *  - Foo\Do\Job
 *
 * If module is not empty, it will take a section in view FQN.
 *
 * If no view matched, a default view can be returned.
 *
 * @property    string  $defaultView
 * @property    string  $viewNamespace  Include ending backslash
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait GetViewClassByNamespaceTrait
{
    /**
     * @see ControllerTrait::getViewClass()
     *
     * @param   string  $action
     * @return  string
     */
    protected function getViewClass($action)
    {
        $viewClass = $this->getViewClassByNamespace($action);

        return empty($viewClass) ? $this->defaultView : $viewClass;
    }


    /**
     * Get view class by namespace
     *
     * Module will append to namespace too, the real view namespace is:
     *  $namespace = $viewNamespace \ $module
     *
     * Action will be split by separator '-', and try append each section to
     * namespace to see if class exists, and return first valid class name.
     *
     * Eg: For an action 'profile-list', will first try
     * '$namespace \ ProfileList', then '$namespace \ Profile \ List'.
     *
     * @param   string  $action
     * @return  string
     */
    protected function getViewClassByNamespace($action)
    {
        if (empty($action)) {
            return '';
        }

        $stringUtil = UtilContainer::getInstance()->getString();

        $namespace = $this->viewNamespace;
        if (!empty($this->module)) {
            $namespace .= $stringUtil->toStudlyCaps($this->module) . '\\';
        }

        $actionSections = explode('-', $action);
        $actionSections = array_map(
            [$stringUtil, 'toStudlyCaps'],
            $actionSections
        );

        while (!empty($actionSections)) {
            $class = $namespace . implode('', $actionSections);
            if (class_exists($class)) {
                return $class;

            } else {
                $section = array_shift($actionSections);
                $namespace .= $section . '\\';
            }
        }

        return '';
    }
}
