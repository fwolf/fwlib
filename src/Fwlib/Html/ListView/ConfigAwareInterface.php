<?php
namespace Fwlib\Html\ListView;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface ConfigAwareInterface
{
    /**
     * Setter of Config instance
     *
     * @param   Config  $instance
     * @return  static
     */
    public function setConfigInstance(Config $instance);
}
