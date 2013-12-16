<?php
namespace Fwlib\Config;

use Fwlib\Config\Config;

/**
 * Config class for store global setting
 *
 * This is a Singleton class, should getInstance() then use, it will return a
 * special instance to store global config. These config data should be set at
 * beginning(eg: config.default.php), then they are readable anywhere.
 *
 * @codeCoverageIgnore
 *
 * @package     Fwlib\Config
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib.Config@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-22
 */
class GlobalConfig extends Config
{
    /**
     * Prevent clone method
     */
    private function __clone()
    {
    }


    /**
     * Prevent 'new' operator
     */
    protected function __construct()
    {
    }


    /**
     * Prevent unserialize method
     */
    private function __wakeup()
    {
    }


    /**
     * Get instance of Singleton itself
     *
     * @return  object
     */
    public static function getInstance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new static();
        }

        return $instance;
    }
}
