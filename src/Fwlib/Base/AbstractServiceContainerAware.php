<?php
namespace Fwlib\Base;

use FwlibTest\Aide\TestServiceContainer;

/**
 * Class uses ServiceContainer
 *
 * The TestServiceContainer should be replaced in production environment.
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class AbstractServiceContainerAware implements ServiceContainerAwareInterface
{
    /**
     * @var ServiceContainerInterface
     */
    protected $serviceContainer = null;


    /**
     * Get service instance
     *
     * Same with Fwlib\Base\AbstractAutoNewInstance::getService()
     *
     * @param   string  $name
     * @return  object  Service instance
     */
    protected function getService($name)
    {
        if (is_null($this->serviceContainer)) {
            $this->setServiceContainer(null);
        }

        return $this->serviceContainer->get($name);
    }


    /**
     * {@inheritdoc}
     *
     * @param   ServiceContainerInterface   $serviceContainer
     * @return  AbstractServiceContainerAware
     */
    public function setServiceContainer(
        ServiceContainerInterface $serviceContainer = null
    ) {
        if (is_null($serviceContainer)) {
            $this->serviceContainer = TestServiceContainer::getInstance();
        } else {
            $this->serviceContainer = $serviceContainer;
        }

        return $this;
    }
}
