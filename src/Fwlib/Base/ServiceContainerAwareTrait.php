<?php
namespace Fwlib\Base;

/**
 * Trait for service container client
 *
 * A ServiceContainerAwareInterface is not necessary, because using service
 * container is just a helper, not a feature, need not use interface to identify
 * that.
 *
 * In production, if ServiceContainer class is extended, should extend a trait
 * too, to change getServiceContainer() method with new default return value
 * and correct return type hint.
 * @see \FwlibTest\Aide\TestServiceContainerAwareTrait
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ServiceContainerAwareTrait
{
    /**
     * @var ServiceContainerInterface
     */
    protected $serviceContainer = null;


    /**
     * Getter of service container instance
     *
     * If no service container instance set, will get from default service
     * container and return, but keep $serviceContainer property null, for
     * clear var_dump() output.
     *
     * @return  ServiceContainer
     */
    protected function getServiceContainer()
    {
        return is_null($this->serviceContainer)
            ? ServiceContainer::getInstance()
            : $this->serviceContainer;
    }


    /**
     * Setter of service container instance
     *
     * Although getter can work with null service container property, this
     * setter can add an instance with higher priority over getter's default
     * return value, useful for dependence injection or test.
     *
     * @param   ServiceContainerInterface   $serviceContainer
     * @return  static
     */
    public function setServiceContainer(
        ServiceContainerInterface $serviceContainer
    ) {
        $this->serviceContainer = $serviceContainer;

        return $this;
    }
}
