<?php
namespace Fwlib\Html\ListView;

use Fwlib\Config\ConfigAwareInterface as ParentConfigAwareInterface;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface ConfigAwareInterface extends ParentConfigAwareInterface
{
    /**
     * Setter of Config instance
     *
     * @param   Config $instance
     * @return  $this
     */
    public function setConfigInstance(Config $instance);
}
