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
 * using these object will trigger newObjXxx() method in AbstractAutoNewObj
 * class, here we can use ServiceContainer to provide real object instance,
 * the only work needed is do setServiceContainer() once.
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
 * @codeCoverageIgnore
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
     * Array to store service
     *
     * @var array
     */
    protected $service = array();


    /**
     * Get service by name
     *
     * When $forcenew is true, it will ignore exists service and create a new
     * one-time use service object, which will not be stored in container.
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
            if (!isset($this->service[$name])) {
                $this->service[$name] = $this->newService($name);
            }

            return $this->service[$name];
        }
    }


    /**
     * New service object by name
     *
     * @param   string  $name
     * @return  object
     */
    protected function newService($name)
    {
        $method = 'new' . $name;

        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            throw new \Exception("Instance method for $name is not defined.");
        }
    }
}
