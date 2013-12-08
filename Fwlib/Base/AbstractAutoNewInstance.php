<?php
namespace Fwlib\Base;


/**
 * Auto new property instance using magic function __get
 *
 * Should change to use trait after upgrade to PHP 5.4.
 *
 * Auto new can also be skipped by call setInstance() method.
 *
 * @package     Fwlib\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-22
 */
abstract class AbstractAutoNewInstance
{
    /**
     * ServiceContainer to new instance
     *
     * @var object
     */
    public $serviceContainer = null;


    /**
     * Auto new property instance if not set
     *
     * Need corresponding method newInstanceFoo() defined.
     *
     * @param   string  $name
     * @return  object
     */
    public function __get($name)
    {
        $method = 'newInstance' . ucfirst($name);
        // For backward compative
        $methodOld = 'newObj' . ucfirst($name);

        if (method_exists($this, $method)) {
            $this->$name = $this->$method();
            return $this->$name;

        } elseif (method_exists($this, $methodOld)) {
            $this->$name = $this->$methodOld();
            return $this->$name;

        } else {
            // @codeCoverageIgnoreStart

            $trace = debug_backtrace();
            trigger_error(
                'Undefined property via __get(): ' . $name .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
                E_USER_NOTICE
            );

            // trigger_error will terminate program run, below will not exec
            return null;

            // @codeCoverageIgnoreEnd
        }
    }


    /**
     * Check if ServiceContainer instance is valid
     *
     * @param   boolean $throwExceptionWhenFail
     * @return  boolean
     */
    public function checkServiceContainer($throwExceptionWhenFail = true)
    {
        if (is_null($this->serviceContainer)) {
            if ($throwExceptionWhenFail) {
                throw new \Exception('Need valid ServiceContainer.');
            } else {
                return false;
            }
        } else {
            return true;
        }
    }


    /**
     * Set a property instance
     *
     * A bit like dependence injection.
     *
     * @param   mixed   $instance
     * @param   string  $className  Empty to auto-detect
     * @return  $this
     */
    public function setInstance($instance, $className = null)
    {
        if (empty($className)) {
            $className = get_class($instance);
            $className = implode('', array_slice(explode('\\', $className), -1));
        }

        $className = lcfirst($className);

        $this->$className = $instance;

        return $this;
    }


    /**
     * Set ServiceContainer instance
     *
     * @param   object  $serviceContainer
     */
    public function setServiceContainer($serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }
}
