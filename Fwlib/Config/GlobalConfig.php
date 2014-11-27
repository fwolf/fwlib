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
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
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
     * Disabled because conflict with parent class.
     */
    /*
    protected function __construct()
    {
    }
    */


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


    /**
     * Limit program can only run on prefered server
     *
     * Server is identify by config key.
     *
     * @param   mixed   $id     Server id allowed, string|int or array of them
     * @param   boolean $exit   If true, exit() when check fail
     * @param   string  $key    Config key of server id
     * @return  boolean
     */
    public function limitServerId($id, $exit = true, $key = 'server.id')
    {
        $message = '';

        $serverId = $this->get($key);
        if (empty($serverId)) {
            $message = 'Server id not set.';

        } elseif (is_array($id) && !(in_array($serverId, $id))) {
            $message = 'This program can only run on these servers: ' .
                implode(', ', $id) . '.';

        } elseif (!is_array($id) && ($serverId != $id)) {
            $message = 'This program can only run on server ' . $id . '.';
        }


        if (empty($message)) {
            return true;

        } else {
            // Check fail
            return (true == $exit) ? exit($message) : false;
        }
    }
}
