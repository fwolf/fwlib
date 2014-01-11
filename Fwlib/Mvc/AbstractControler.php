<?php
namespace Fwlib\Mvc;

use Fwlib\Base\AbstractServiceContainer;
use Fwlib\Mvc\ControlerInterface;
use Fwlib\Mvc\ViewInterface;
use Fwlib\Util\UtilContainer;

/**
 * Controler and Router in MVC
 *
 * In application, Controler is common called in index.php as entry, the main
 * perpose is to route user request(via $_GET) to View.
 *
 * Also, it can delegate request to other Controler, so sub-dir can have their
 * own index too.
 *
 * @package     Fwlib\Mvc
 * @copyright   Copyright 2008-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-04-06
 */
abstract class AbstractControler implements ControlerInterface
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
     * Root Controler use empty string as module name.
     *
     * @var string
     */
    private $module = '';

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
     * @var AbstractServiceContainer
     */
    protected $serviceContainer = null;

    /**
     * @var UtilContainer
     */
    protected $utilContainer = null;


    /**
     * Contructor
     *
     * @param   string  $pathToRoot
     */
    public function __construct($pathToRoot = null)
    {
        $this->setPathToRoot($pathToRoot);
    }


    /**
     * Create controler instance
     *
     * @param   string  $className
     * @return  ControlerInterface
     */
    protected function createControler($className)
    {
        $controler = new $className($this->pathToRoot);

        $controler->setServiceContainer($this->serviceContainer);

        return $controler;
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

        $view->setServiceContainer($this->serviceContainer);

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

            return $view->getOutput($action);

        } catch (\Exception $e) {
            return $this->displayError($e->getMessage());
        }
    }


    /**
     * Render error message for display
     *
     * Error from Controler include module/action configure error, or wrong
     * request data, eg: user input wrong url. These error are different with
     * other process error like validate fail, they should not exists when
     * Controler and View are correctly defined/called, and user didn't use
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
     * Get class name of Controler by module
     *
     * By given $module name, use switch or check prefix, to determin which
     * Controler should use. Return null if not found.
     *
     * Should extend by child class if need to use module.
     *
     * @param   string  $module
     * @return  string
     */
    protected function getControlerClass($module)
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
     * @param   array   $request    Defaut $_GET
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
     * By given $action string, use switch or check prefix, to determin which
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
     * @return  AbstractControler
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
     * {@inheritdoc}
     *
     * @param   AbstractServiceContainer    $serviceContainer
     * @return  AbstractControler
     */
    public function setServiceContainer(
        AbstractServiceContainer $serviceContainer
    ) {
        $this->serviceContainer = $serviceContainer;

        $this->utilContainer = $serviceContainer->get('UtilContainer');

        return $this;
    }


    /**
     * Transfer request to another Controler
     *
     * @param   string  $module
     * @return  string
     */
    protected function transfer($module)
    {
        try {
            $controlerClass = $this->getControlerClass($module);
            if (empty($controlerClass)) {
                throw new \Exception(
                    "Controler for module $module not defined"
                );
            }

            $controler = $this->createControler($controlerClass);

            return $controler->getOutput();

        } catch (\Exception $e) {
            return $this->displayError($e->getMessage());
        }
    }
}
