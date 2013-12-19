<?php
namespace Fwlib\Base;

use Fwlib\Base\AbstractSingleton;

/**
 * Service Container
 *
 * For avoid of multiple create instance of some common class, such as Db,
 * Cache, make them as service object and store here to use in variant place.
 *
 * Use Dependency Injection on constructor is good but not conveniency, create
 * Factory for each class need inject is also complicate, so ServiceContainer
 * can be substitution. If dependent object is not passed in when construct,
 * using these object will trigger newInstanceXxx() method in
 * AbstractAutoNewInstance class, here we can use ServiceContainer to provide
 * real object instance, the only work needed is do setServiceContainer()
 * once. Another similar way is doing outside of the class, create instance by
 * ServiceContainer or other, then use setInstance() method to inject.
 *
 * In short, if Dependency Injection is skipped, get ServiceContainer instance
 * and assign use setter, you will got worked dependent object automatic,
 * without loose visual dependent relationship.
 *
 * In this way, test or inherit ServiceContainer is easy too, just assign
 * another container instance with setter.
 *
 * Usually only one container instance is need, so use Singleton pattern.
 *
 * When create class instance(Service) in container, it's also better to use
 * Dependency Injection: create dependent object and pass to constructor. If
 * necessary, assign ServiceContainer instance here for later usage.
 *
 * @package     Fwlib\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-11-07
 */
abstract class AbstractServiceContainer extends AbstractSingleton
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
     * Get service by name
     *
     * When $forcenew is true, it will ignore exists service and create a new
     * one-time use service object, which will not be stored in instance
     * array.
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
        $method = 'new' . ucfirst($name);

        if (method_exists($this, $method)) {
            return $this->$method();


        } elseif (isset($this->serviceClass[$name])) {
            $className = $this->serviceClass[$name];

            // Singleton or similar class's instantiate diffs
            if (method_exists($className, 'getInstance')) {
                return $className::getInstance();
            } else {
                return new $className;
            }


        } else {
            throw new \Exception("Invalid service '$name'.");
        }
    }


    /**
     * Register service class or instance
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
     * Register service class
     *
     * For class implement Fwlib\Base\AbstractSingleton or similar, can be
     * instanced simplely by find class name in serviceClass array and call
     * its getInstance() method, avoid define of newServiceXxx() method.
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
     * Register service instance
     *
     * Registered instance can directly use without newServiceXxx() method.
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
