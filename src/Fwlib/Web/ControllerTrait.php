<?php
namespace Fwlib\Web;

use Fwlib\Web\Exception\ControllerNotDefinedException;
use Fwlib\Web\Exception\ViewNotDefinedException;

/**
 * @see         ControllerInterface
 *
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 *
 * @property    string $defaultModule
 * @property    string $module
 */
trait ControllerTrait
{
    use RequestAwareTrait;


    /**
     * Create controller instance
     *
     * @param   string $className
     * @return  ControllerInterface
     */
    protected function createController($className)
    {
        /** @var ControllerInterface $controller */
        $controller = new $className();

        return $controller;
    }


    /**
     * Create view instance
     *
     * @param   string $className
     * @return  ViewInterface
     */
    protected function createView($className)
    {
        /** @var ViewInterface $view */
        $view = new $className();

        return $view;
    }


    /**
     * Call View for output
     *
     * @param   string $action
     * @return  string
     */
    protected function display($action)
    {
        try {
            $viewClass = $this->getViewClass($action);
            if (empty($viewClass)) {
                throw new ViewNotDefinedException(
                    "View for action \"$action\" not defined"
                );
            }

            $view = $this->createView($viewClass);

            return $view->getOutput();

        } catch (ViewNotDefinedException $e) {
            return $this->displayError($e->getMessage());
        }
    }


    /**
     * Render error message for display
     *
     * Error from Controller include module/action configure error, or wrong
     * request data, eg: user input wrong url. These error are different with
     * other process error like validate fail, they should not exists when
     * Controller and View are correctly defined/called, and user did not use
     * wrong url or submit wrong request data.
     *
     * Usually, it can use View to show friendly error message in html format,
     * implement in child class.
     *
     * @param   string $message
     * @return  string
     */
    protected function displayError($message)
    {
        return "Error: $message";
    }


    /**
     * Get class name of controller by module
     *
     * By given $module name, determine which controller class should use.
     * Return null if not found.
     *
     * Must extend by child class. Small application can have no module.
     *
     * @param   string $module
     * @return  string
     */
    abstract protected function getControllerClass($module);


    /**
     * @see ControllerInterface::getOutput()
     *
     * @return  string
     */
    public function getOutput()
    {
        $module = $this->getRequestModule();

        if ($module != $this->module) {
            $output = $this->transfer($module);

        } else {
            $action = $this->getRequestAction();
            $output = $this->display($action);
        }

        return $output;
    }


    /**
     * Getter of request action for easily inject default action
     *
     * @return  string
     */
    protected function getRequestAction()
    {
        return $this->getRequest()->getAction();
    }


    /**
     * Getter of request module for easily inject default module
     *
     * @return  string
     */
    protected function getRequestModule()
    {
        $request = $this->getRequest();
        $module = $request->getModule();

        if (empty($module)) {
            $module = $this->defaultModule;

            // Change request for delegated controller work properly
            $request->setModule($module);
        }

        return $module;
    }


    /**
     * Get class name of view by action
     *
     * By given action string, determine which view should use. Return null if
     * not found.
     *
     * Must extend by child class.
     *
     * @param   string $action
     * @return  string
     */
    abstract protected function getViewClass($action);


    /**
     * Transfer request to another controller
     *
     * @param   string $module
     * @return  string
     */
    protected function transfer($module)
    {
        try {
            $controllerClass = $this->getControllerClass($module);
            if (empty($controllerClass)) {
                throw new ControllerNotDefinedException(
                    "Controller for module \"$module\" not defined"
                );
            }

            $controller = $this->createController($controllerClass);

            return $controller->getOutput();

        } catch (ControllerNotDefinedException $e) {
            return $this->displayError($e->getMessage());
        }
    }
}
