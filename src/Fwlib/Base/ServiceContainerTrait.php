<?php
namespace Fwlib\Base;

use Fwlib\Base\Exception\ServiceInstanceCreationFailException;

/**
 * Trait for service container
 *
 * Usually only one container instance is need, so use Singleton pattern.
 *
 * Service name better use camelCase or StudyCaps style.
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 * @see         ServiceContainerInterface
 */
trait ServiceContainerTrait
{
    use SingletonTrait;


    /**
     * Service class map, require static getInstance() method
     *
     * For easy inherit and not conflict with user registered class, do not
     * set value here, set them in initializeServiceClassMap().
     *
     * @see SingleInstanceTrait
     * @var string[]
     */
    protected $serviceClassMap = [];

    /**
     * Service instances
     *
     * @var object[]
     */
    protected $instances = [];


    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->serviceClassMap = $this->getInitialServiceClassMap();
    }


    /**
     * Create service instance by name
     *
     * Will try below sequence to create instance:
     *
     *  - createName() in this container
     *  - Name::getInstance()
     *  - new Name
     *
     * @param   string  $name
     * @return  object
     * @throws  ServiceInstanceCreationFailException
     */
    protected function createService($name)
    {
        $service = null;

        $method = 'create' . ucfirst($name);
        if (method_exists($this, $method)) {
            $service = $this->$method();


        } elseif (isset($this->serviceClassMap[$name])) {
            $className = $this->serviceClassMap[$name];

            // Singleton or single instance class instantiate diffs
            if (method_exists($className, 'getInstance')) {
                $service = $className::getInstance();
            } else {
                $service = new $className;
            }


        } else {
            throw (new ServiceInstanceCreationFailException)
                ->setServiceName($name);
        }


        return $service;
    }


    /**
     * Get service instance by name
     *
     * The return type maybe variable, so this method is inner use only. Make
     * getFoo() methods in client class to identify return type.
     *
     * When $forcenew is true, it will ignore exists service instance and
     * create a new one-time use instance, which will not be stored in instance
     * array.
     *
     * $forcenew does not affect instances create according to class map,
     * because they use singleton or single instance pattern.
     *
     * @param   string  $name
     * @param   boolean $forcenew
     * @return  object
     */
    protected function get($name, $forcenew = false)
    {
        if ($forcenew) {
            return $this->createService($name);

        } else {
            if (!isset($this->instances[$name])) {
                $this->instances[$name] = $this->createService($name);
            }

            return $this->instances[$name];
        }
    }


    /**
     * Return initial service class map
     *
     * Dummy for inherit and extend by child class.
     *
     * @return  string[]
     */
    protected function getInitialServiceClassMap()
    {
        $classMap = [];

        return $classMap;
    }


    /**
     * @see ServiceContainerInterface::getRegistered()
     *
     * @param   string  $name
     * @return  object
     */
    public function getRegistered($name)
    {
        return $this->get($name, false);
    }


    /**
     * @see ServiceContainerInterface::register()
     *
     * @param   string        $name
     * @param   string|object $service
     * @return  static
     */
    public function register($name, $service)
    {
        if (is_string($service)) {
            return $this->registerClass($name, $service);
        } else {
            return $this->registerInstance($name, $service);
        }
    }


    /**
     * @see ServiceContainerInterface::registerClass()
     *
     * @param   string  $name
     * @param   string  $className  Full qualified name without leading '\'
     * @return  static
     */
    public function registerClass($name, $className)
    {
        $this->serviceClassMap[$name] = $className;

        return $this;
    }


    /**
     * @see ServiceContainerInterface::registerInstance()
     *
     * @param   string  $name
     * @param   object  $instance
     * @return  static
     */
    public function registerInstance($name, $instance)
    {
        $this->instances[$name] = $instance;

        return $this;
    }
}
