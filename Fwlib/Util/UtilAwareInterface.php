<?php
namespace Fwlib\Util;


/**
 * Interface of class uses Util
 *
 * @package     Fwlib\Util
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-19
 */
interface UtilAwareInterface
{
    /**
     * Setter of UtilContainer
     *
     * Assign value to $this->utilContainer. If given param is null, should
     * get util container instance from UtilContainer or ServiceContainer.
     *
     * @param   UtilContainer   $utilContainer
     * @return  UtilAwareInterface
     */
    public function setUtilContainer(UtilContainer $utilContainer = null);
}
