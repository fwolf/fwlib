<?php
namespace Fwlib\Base;

use Fwlib\Base\AbstractAutoNewInstance;
use Fwlib\Config\Config;

/**
 * Base class for auto new $config property
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractAutoNewConfig extends AbstractAutoNewInstance
{
    /**
     * Config object
     *
     * @var \Fwlib\Config\Config
     */
    public $config = null;


    /**
     * Constructor
     *
     * @param   array   $config
     */
    public function __construct($config = array())
    {
        $this->setConfigDefault();

        if (!empty($config)) {
            $this->getConfigInstance()->set($config);
        }
    }


    /**
     * Get config value
     *
     * @param   string  $key
     * @param   mixed   $default        Return this if key not exists
     * @return  mixed
     */
    public function getConfig($key, $default = null)
    {
        $configInstance = $this->getConfigInstance();

        return $configInstance->get($key, $default);
    }


    /**
     * Set config value
     *
     * @param   string  $key
     * @param   mixed   $val    Should not null except $key is array
     * @return  AbstractAutoNewConfig
     */
    public function setConfig($key, $val = null)
    {
        $configInstance = $this->getConfigInstance();

        $configInstance->set($key, $val);

        return $this;
    }


    /**
     * Set default config
     *
     * @return  AbstractAutoNewConfig
     */
    protected function setConfigDefault()
    {
        // Dummy, extend by child class

        return $this;
    }


    /**
     * Get Config instance
     *
     * @return Config
     */
    protected function getConfigInstance()
    {
        if (is_null($this->config)) {
            $this->config = new Config;
        }

        return $this->config;
    }
}
