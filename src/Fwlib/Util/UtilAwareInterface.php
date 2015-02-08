<?php
namespace Fwlib\Util;

/**
 * Interface of class uses UtilContainer
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface UtilAwareInterface
{
    /**
     * Getter of UtilContainer instance
     *
     * @return  UtilContainerInterface
     */
    public function getUtilContainer();


    /**
     * Setter of UtilContainer instance
     *
     * @param   UtilContainerInterface  $utilContainer
     * @return  static
     */
    public function setUtilContainer(
        UtilContainerInterface $utilContainer = null
    );
}
