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
 * The get() method is removed, because it can not hint return type. For Foo
 * class, there should be a getFoo() method in implement with correct return
 * type marked, for IDE friendly.
 *
 * As this interface lacks of actual getFoo() methods, use it as type hint
 * with caution. A working plan is use interface to hint service container
 * setter parameter, while hint getter return value with actual container
 * instance class name. If service container is used as property in trait, it
 * can only hint with interface, as trait property can not be re-declared.
 * @see ServiceContainerAwareTrait
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface ServiceContainerInterface
{
    /**
     * Register service class or instance
     *
     * @param   string        $name
     * @param   string|object $service
     * @return  static
     */
    public function register($name, $service);


    /**
     * Register service class with static factory method
     *
     * Eg: For class uses Fwlib\Base\SingleInstanceTrait, can be instanced
     * with its getInstance() method.
     *
     * @param   string  $name
     * @param   string  $className  Full qualified name without leading '\'
     * @return  static
     */
    public function registerClass($name, $className);


    /**
     * Register service instance
     *
     * Registered instance can directly use, not need container to create it.
     *
     * @param   string  $name
     * @param   object  $instance
     * @return  static
     */
    public function registerInstance($name, $instance);
}
