<?php
namespace Fwlib\Mvc;

use Fwlib\Mvc\ControllerInterface;
use Fwlib\Mvc\ViewInterface;

/**
 * Controller and Router in MVC
 *
 * In application, Controller is common called in index.php as entry, the main
 * purpose is to route user request(via $_GET) to View.
 *
 * Also, it can delegate request to other Controller, so sub-dir can have their
 * own index too.
 *
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractController implements ControllerInterface
{
    /**
     * Request param of action
     *
     * @var string
     */
    protected $actionParameter = 'a';

    /**
     * Module name
     *
     * If module parsed from user request equals this, will call corresponding
     * View to get output.
     *
     * Root Controller use empty string as module name.
     *
     * @var string
     */
    protected $module = '';

    /**
     * Request param of module
     *
     * @var string
     */
    protected $moduleParameter = 'm';

    /**
     * Path to root
     *
     * External resource in application local storage will retrieve by
     * relative path to this path.
     *
     * @var string
     */
    protected $pathToRoot = '../../';


    /**
     * Constructor
     *
     * @param   string  $pathToRoot
     */
    public function __construct($pathToRoot = null)
    {
        $this->setPathToRoot($pathToRoot);
    }


    /**
     * Create controller instance
     *
     * @param   string  $className
     * @return  ControllerInterface
     */
    protected function createController($className)
    {
        $controller = new $className($this->pathToRoot);

        return $controller;
    }


    /**
     * Create view instance
     *
     * @param   string  $className
     * @return  ViewInterface
     */
    protected function createView($className)
    {
        $view = new $className($this->pathToRoot);

        $view->setModule($this->module);

        return $view;
    }


    /**
     * Call View for output
     *
     * @param   string  $action
     * @return  string
     */
    protected function display($action)
    {
        try {
            $viewClass = $this->getViewClass($action);
            if (empty($viewClass)) {
                throw new \Exception(
                    "View for action $action not defined"
                );
            }

            $view = $this->createView($viewClass);

            return $view->setAction($action)->getOutput();

        } catch (\Exception $e) {
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
     * @param   string  $message
     * @return  string
     */
    protected function displayError($message)
    {
        return "Error: $message";
    }


    /**
     * Get action from user request
     *
     * @param   array   $request
     * @return  string
     */
    protected function getActionFromRequest(array $request)
    {
        if (isset($request[$this->actionParameter])) {
            $action = trim($request[$this->actionParameter]);

        } else {
            $action = '';
        }

        return $action;
    }


    /**
     * Get class name of Controller by module
     *
     * By given $module name, use switch or check prefix, to determine which
     * Controller should use. Return null if not found.
     *
     * Should extend by child class if need to use module.
     *
     * @param   string  $module
     * @return  string
     */
    protected function getControllerClass($module)
    {
        return null;
    }


    /**
     * Get module name from user request
     *
     * @param   array   $request
     * @return  string
     */
    protected function getModuleFromRequest(array $request)
    {
        if (isset($request[$this->moduleParameter])) {
            $module = trim($request[$this->moduleParameter]);

        } else {
            $module = $this->module;
        }

        return $module;
    }


    /**
     * {@inheritdoc}
     *
     * @param   array   $request    Default $_GET
     * @return  string
     */
    public function getOutput(array $request = null)
    {
        if (is_null($request)) {
            $request = $_GET;
        }

        $module = $this->getModuleFromRequest($request);
        if ($module != $this->module) {
            $output = $this->transfer($module);

        } else {
            $action = $this->getActionFromRequest($request);
            $output = $this->display($action);
        }

        return $output;
    }


    /**
     * Get class name of View by action
     *
     * By given $action string, use switch or check prefix, to determine which
     * View should use. Return null if not found.
     *
     * Should extend by child class.
     *
     * @param   string  $action
     * @return  string
     */
    abstract protected function getViewClass($action);


    /**
     * {@inheritdoc}
     *
     * @param   string  $pathToRoot
     * @return  AbstractController
     */
    public function setPathToRoot($pathToRoot)
    {
        if (!is_null($pathToRoot)) {
            if (DIRECTORY_SEPARATOR != substr($pathToRoot, -1)) {
                $pathToRoot .= DIRECTORY_SEPARATOR;
            }

            $this->pathToRoot = $pathToRoot;
        }

        return $this;
    }


    /**
     * Transfer request to another Controller
     *
     * @param   string  $module
     * @return  string
     */
    protected function transfer($module)
    {
        try {
            $controllerClass = $this->getControllerClass($module);
            if (empty($controllerClass)) {
                throw new \Exception(
                    "Controller for module $module not defined"
                );
            }

            $controller = $this->createController($controllerClass);

            return $controller->getOutput();

        } catch (\Exception $e) {
            return $this->displayError($e->getMessage());
        }
    }
}
