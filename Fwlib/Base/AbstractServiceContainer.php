<?php
namespace Fwlib\Base;

use Fwlib\Base\AbstractSingleton;

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
