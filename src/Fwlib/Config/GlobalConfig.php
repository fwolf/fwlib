<?php
namespace Fwlib\Config;

use Fwlib\Config\Exception\ServerIdNotSet;
use Fwlib\Config\Exception\ServerProhibited;

/**
 * Config class for store global setting
 *
 * This is a Singleton class, should getInstance() then use, it will return a
 * special instance to store global config. These config data should be set at
 * beginning(eg: config.default.php), then they are readable anywhere.
 *
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
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
     * Check current server id in allowed list
     *
     * Server is identify by config key.
     *
     * @param   string|int|string[]|int[]   $allowedId
     * @param   string                      $key    Config key of server id
     * @return  boolean
     * @throws  ServerIdNotSet
     */
    public function checkServerId($allowedId, $key = 'server.id')
    {
        $serverId = $this->get($key);

        if (empty($serverId)) {
            throw new ServerIdNotSet('Server id not set');
        }

        if (!is_array($allowedId)) {
            $allowedId = [$allowedId];
        }

        return in_array($serverId, $allowedId);
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
     * Limit program can only run on preferred server
     *
     * Server is identify by config key.
     *
     * @param   string|int|string[]|int[]   $allowedId
     * @param   string                      $key    Config key of server id
     * @return  static
     * @throws  ServerProhibited
     */
    public function limitServerId($allowedId, $key = 'server.id')
    {
        if (!$this->checkServerId($allowedId, $key)) {
            $message = 'This program can only run on ' .
                (is_array($allowedId)
                    ? 'servers: ' . implode(', ', $allowedId)
                    : 'server ' . $allowedId);

            throw new ServerProhibited($message);
        }

        return $this;
    }
}
