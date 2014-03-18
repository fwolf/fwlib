<?php
namespace Fwlib\Base;

use Fwlib\Base\ServiceContainerAwareInterface;
use Fwlib\Base\ServiceContainerInterface;
use Fwlib\Test\ServiceContainerTest;

/**
 * Class uses ServiceContainer
 *
 * The ServiceContainerTest should be replaced in production environment.
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-03-18
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
            $this->serviceContainer = ServiceContainerTest::getInstance();
        } else {
            $this->serviceContainer = $serviceContainer;
        }

        return $this;
    }
}
