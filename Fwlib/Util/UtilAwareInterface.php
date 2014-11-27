<?php
namespace Fwlib\Util;

use Fwlib\Util\UtilContainerInterface;

/**
 * Interface of class uses Util
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
interface UtilAwareInterface
{
    /**
     * Setter of UtilContainer
     *
     * Assign value to $this->utilContainer. If given param is null, should
     * get util container instance from UtilContainer or ServiceContainer.
     *
     * @param   UtilContainerInterface  $utilContainer
     * @return  UtilAwareInterface
     */
    public function setUtilContainer(
        UtilContainerInterface $utilContainer = null
    );
}
