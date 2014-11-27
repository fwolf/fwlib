<?php
namespace Fwlib\Base;

use Fwlib\Base\AbstractSingleton;
use Fwlib\Base\ServiceContainerInterface;

/**
 * Service Container
 *
 * Usually only one container instance is need, so use Singleton pattern.
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractServiceContainer extends AbstractSingleton implements
    ServiceContainerInterface
{
    /**
     * Class name for call Class::getInstance()
     *
     * @var array
     */
    protected $serviceClass = array();

    /**
     * Service instance
     *
     * @var array
     */
    protected $serviceInstance = array();


    /**
     * {@inheritdoc}
     *
     * $forcenew doesn't affect child class of Fwlib\Base\AbstractSingleton.
     *
     * @param   string  $name
     * @param   boolean $forcenew
     * @return  object
     */
    public function get($name, $forcenew = false)
    {
        if ($forcenew) {
            return $this->newService($name);

        } else {
            if (!isset($this->serviceInstance[$name])) {
                $this->serviceInstance[$name] = $this->newService($name);
            }

            return $this->serviceInstance[$name];
        }
    }


    /**
     * New service instance by name
     *
     * @param   string  $name
     * @return  object
     */
    protected function newService($name)
    {
        $service = null;

        $method = 'new' . ucfirst($name);
        if (method_exists($this, $method)) {
            $service = $this->$method();


        } elseif (isset($this->serviceClass[$name])) {
            $className = $this->serviceClass[$name];

            // Singleton or similar class's instantiate diffs
            if (method_exists($className, 'getInstance')) {
                $service = $className::getInstance();
            } else {
                $service = new $className;
            }


        } else {
            throw new \Exception("Invalid service '$name'.");
        }


        if (method_exists($service, 'setServiceContainer')) {
            $service->setServiceContainer($this);
        }

        return $service;
    }


    /**
     * {@inheritdoc}
     *
     * @param   string  $name
     * @param   string|object   $service
     * @return  $this
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
     * {@inheritdoc}
     *
     * @param   string  $name
     * @param   string  $className  Full qualified name without leading '\'
     * @return  $this
     */
    public function registerClass($name, $className)
    {
        $this->serviceClass[$name] = $className;

        return $this;
    }


    /**
     * {@inheritdoc}
     *
     * @param   string  $name
     * @param   object  $instance
     * @return  $this
     */
    public function registerInstance($name, $instance)
    {
        $this->serviceInstance[$name] = $instance;

        return $this;
    }
}
