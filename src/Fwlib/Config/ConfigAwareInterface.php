<?php
namespace Fwlib\Config;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface ConfigAwareInterface
{
    /**
     * Get config value
     *
     * @param   string  $key
     * @param   mixed   $default        Return this if key not exists
     * @return  mixed
     */
    public function getConfig($key, $default = null);


    /**
     * Get all configs
     *
     * @return  array
     */
    public function getConfigs();


    /**
     * Set single config value
     *
     * @param   string       $key
     * @param   mixed        $val
     * @return  static
     */
    public function setConfig($key, $val);


    /**
     * Batch set config values
     *
     * @param   array   $configs
     * @return  static
     */
    public function setConfigs(array $configs);
}
