<?php
namespace Fwlib\Base;

/**
 * Service Container interface
 *
 * For avoid of multiple create instance of some common class, such as Db,
 * Cache, make them as service object and store here to use in variant place.
 *
 * Use Dependency Injection on constructor is good but not convenience, create
 * Factory for each class need inject is also complicate, so service container
 * can be substitution. If dependence is not passed in through construct or
 * setter, working class can retrieve them from service container. This is not
 * anti-pattern, because service container is the last choice, and should only
 * use to get common share instances.
 *
 * Test of service container client is easy too, just inject another container
 * instance with setter, or replace instance with mock and recover original
 * value after test using setUpBeforeClass() and tearDownAfterClass().
 *
 * When create class instance(Service) in container, it's also better to use
 * Dependency Injection: create dependent object and pass via constructor or
 * setter.
 *
 * @copyright   Copyright 2014-2015 Fwolf
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
     * instanced simply by find class name in serviceClass array and call
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
