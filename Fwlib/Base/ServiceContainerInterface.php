<?php
namespace Fwlib\Base;


/**
 * Service Container interface
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
 * When create class instance(Service) in container, it's also better to use
 * Dependency Injection: create dependent object and pass to constructor. If
 * necessary, assign ServiceContainer instance here for later usage.
 *
 * @copyright   Copyright 2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface ServiceContainerInterface
{
    /**
     * Get service instance by name
     *
     * When $forcenew is true, it will ignore exists service and create a new
     * one-time use service object, which will not be stored in instance
     * array.
     *
     * @param   string  $name
     * @param   boolean $forcenew
     * @return  object
     */
    public function get($name, $forcenew = false);


    /**
     * Register service class or instance
     *
     * @param   string  $name
     * @param   string|object   $service
     * @return  ServiceContainerInterface
     */
    public function register($name, $service);


    /**
     * Register service class
     *
     * For class implement Fwlib\Base\AbstractSingleton or similar, can be
     * instanced simplely by find class name in serviceClass array and call
     * its getInstance() method, avoid define of newServiceXxx() method.
     *
     * @param   string  $name
     * @param   string  $className  Full qualified name without leading '\'
     * @return  ServiceContainerInterface
     */
    public function registerClass($name, $className);


    /**
     * Register service instance
     *
     * Registered instance can directly use without newServiceXxx() method.
     *
     * @param   string  $name
     * @param   object  $instance
     * @return  ServiceContainerInterface
     */
    public function registerInstance($name, $instance);
}
