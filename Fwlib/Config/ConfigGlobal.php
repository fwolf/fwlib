<?php
namespace Fwlib\Config;

use Fwlib\Config\Config;

/**
 * Config class for store global setting
 *
 * For easy usage like ConfigGlobal::get(), this class use static, which make
 * it can't be child class of Config(non-static). So use Config instance as a
 * property, and map static method get/set to it.
 *
 * @package     Fwlib\Config
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib.Config@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-22
 */
class ConfigGlobal
{
    /**
     * Config object instance
     * @var object
     */
    public static $config = null;


    /**
     * Get config value
     *
     * @param   string  $key
     * @param   mixed   $default        Return this if key not exists
     * @return  mixed
     */
    public static function get($key, $default = null)
    {
        self::newInstanceConfig();
        return self::$config->get($key, $default);
    }


    /**
     * Limit program can only run on prefered server
     *
     * @param   mixed   $id     Server id allowed, string|int or array of them
     * @param   boolean $exit   If true, exit() when check fail
     * @param   string  $key    Config key of server id
     * @return  boolean
     */
    public static function limitServerId($id, $exit = true, $key = 'server.id')
    {
        self::newInstanceConfig();
        return self::$config->limitServerId($id, $exit, $key);
    }


    /**
     * Load config array
     *
     * {key: val}
     *
     * @param   array   $configData
     */
    public static function load($configData)
    {
        self::newInstanceConfig(true);
        self::$config->set($configData);
    }


    /**
     * New Config instance
     *
     * @param   boolean $forcenew
     * @return  object
     */
    protected static function newInstanceConfig($forcenew = false)
    {
        if (is_null(self::$config) || $forcenew) {
            self::$config = new Config;
        }
    }


    /**
     * Set config value
     *
     * @param   string  $key
     * @param   mixed   $val
     */
    public static function set($key, $val)
    {
        self::newInstanceConfig();
        self::$config->set($key, $val);
    }
}
