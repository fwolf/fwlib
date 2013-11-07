<?php
namespace Fwlib\Base;


/**
 * Service Container
 *
 * For avoid of multiple create instance of some common class, such as Db,
 * Cache, make them as service object and store here to use in variant place.
 *
 * When create class instance in container, it maybe better use Dependency
 * Injection: create dependent object and pass to constructor.
 *
 * ServiceContainer can inject to object when construct with getInstance(),
 * easy for test or inherit parent container class, so only one container
 * instance is need, so use Singleton pattern.
 * @link http://www.phptherightway.com/pages/Design-Patterns.html
 *
 * @codeCoverageIgnore
 *
 * @package     Fwlib\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-11-07
 */
class ServiceContainer
{
    /**
     * Array to store service
     *
     * @var array
     */
    protected $service = array();


    /**
     * Prevent clone method
     */
    private function __clone()
    {
    }


    /**
     * Prevent 'new' operator
     */
    protected function __construct()
    {
    }


    /**
     * Prevent unserialize method
     */
    private function __wakeup()
    {
    }


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
     * Get instance of ServiceContainer itself
     *
     * @return  object
     */
    public static function getInstance()
    {
        static $container = null;

        if (is_null($container)) {
            $container = new static();
        }

        return $container;
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
