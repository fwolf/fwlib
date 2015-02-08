<?php
namespace Fwlib\Base;

use Fwlib\Base\AbstractServiceContainer;
use Fwlib\Util\UtilAwareInterface;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\UtilContainerInterface;

/**
 * Auto new property instance using magic function __get
 *
 * Should change to use trait after upgrade to PHP 5.4.
 *
 * Auto new can also be skipped by call setInstance() method.
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractAutoNewInstance implements UtilAwareInterface
{
    /**
     * ServiceContainer to new instance
     *
     * @var AbstractServiceContainer
     */
    public $serviceContainer = null;

    /**
     * @var UtilContainer
     */
    protected $utilContainer = null;


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
        // For backward compatible
        $methodOld = 'newObj' . ucfirst($name);

        // @codeCoverageIgnoreStart
        if (method_exists($this, $method)) {
            $this->$name = $this->$method();
            return $this->$name;

        } elseif (method_exists($this, $methodOld)) {
            $trace = debug_backtrace();
            trigger_error(
                "$methodOld() should be replaced by $method() " .
                "in {$trace[0]['file']} on line {$trace[0]['line']}",
                E_USER_NOTICE
            );

            $this->$name = $this->$methodOld();
            return $this->$name;

        } else {
            $trace = debug_backtrace();
            trigger_error(
                'Undefined property via __get(): ' . $name .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
                E_USER_NOTICE
            );

            // trigger_error will terminate program run, below will not exec
            return null;

        }
        // @codeCoverageIgnoreEnd
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
     * Get service instance
     *
     * @param   string  $name
     * @return  object  Service instance
     */
    protected function getService($name)
    {
        $this->checkServiceContainer(true);

        return $this->serviceContainer->get($name);
    }


    /**
     * Get util instance
     *
     * Same with Fwlib\Util\AbstractUtilAware::getUtil()
     *
     * @param   string  $name
     * @return  object  Util instance
     */
    protected function getUtil($name)
    {
        if (is_null($this->utilContainer)) {
            $this->setUtilContainer(null);
        }

        return $this->utilContainer->get($name);
    }


    /**
     * {@inheritdoc}
     */
    public function getUtilContainer()
    {
        if (is_null($this->utilContainer)) {
            $this->utilContainer = UtilContainer::getInstance();
        }

        return $this->utilContainer;
    }


    /**
     * Set a property instance
     *
     * Keep for backward compatible, should use ServiceContainer::register().
     *
     * @param   mixed   $instance
     * @param   string  $className  Empty to auto-detect
     * @return  AbstractAutoNewInstance
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
     * Setter of ServiceContainer instance
     *
     * @param   AbstractServiceContainer    $serviceContainer
     * @return  AbstractAutoNewInstance
     */
    public function setServiceContainer(
        AbstractServiceContainer $serviceContainer = null
    ) {
        $this->serviceContainer = $serviceContainer;

        return $this;
    }


    /**
     * Setter of UtilContainer instance
     *
     * @param   UtilContainerInterface  $utilContainer
     * @return  AbstractAutoNewInstance
     */
    public function setUtilContainer(
        UtilContainerInterface $utilContainer = null
    ) {
        if (is_null($utilContainer)) {
            $this->utilContainer = UtilContainer::getInstance();
        } else {
            $this->utilContainer = $utilContainer;
        }

        return $this;
    }
}
