<?php
namespace FwlibTest\Aide;

use Fwlib\Base\ServiceContainerAwareTrait;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait TestServiceContainerAwareTrait
{
    use ServiceContainerAwareTrait;


    /**
     * @return  TestServiceContainer
     */
    protected function getServiceContainer()
    {
        return is_null($this->serviceContainer)
            ? TestServiceContainer::getInstance()
            : $this->serviceContainer;
    }
}
