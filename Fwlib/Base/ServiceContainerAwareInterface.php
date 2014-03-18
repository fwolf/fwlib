<?php
namespace Fwlib\Base;

use Fwlib\Base\ServiceContainerInterface;

/**
 * Interface of class uses ServiceContainer
 *
 * @copyright   Copyright 2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2014-03-18
 */
interface ServiceContainerAwareInterface
{
    /**
     * Setter of ServiceContainerContainer
     *
     * Assign value to $this->serviceContainer. If given param is null, should
     * automatic get instance from ServiceContainer.
     *
     * @param   ServiceContainerInterface   $serviceContainer
     * @return  ServiceContainerAwareInterface
     */
    public function setServiceContainer(
        ServiceContainerInterface $serviceContainer = null
    );
}
