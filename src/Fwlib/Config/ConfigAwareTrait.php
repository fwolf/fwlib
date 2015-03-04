<?php
namespace Fwlib\Config;

/**
 * Trait for class use Config as property
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ConfigAwareTrait
{
    /**
     * @var Config
     */
    protected $configInstance = null;


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
     * @return Config
     */
    protected function getConfigInstance()
    {
        if (is_null($this->configInstance)) {
            $config = new Config;

            $config->set($this->getDefaultConfigs());

            $this->configInstance = $config;
        }

        return $this->configInstance;
    }


    /**
     * Get default configs
     *
     * Will be loaded when get config instance, child class can extend to add
     * more default configs.
     *
     * @return  array
     */
    protected function getDefaultConfigs()
    {
        return [];
    }


    /**
     * Set single config value
     *
     * @param   string       $key
     * @param   mixed        $val
     * @return  static
     */
    public function setConfig($key, $val)
    {
        $configInstance = $this->getConfigInstance();

        $configInstance->set($key, $val);

        return $this;
    }


    /**
     * Batch set config values
     *
     * @param   array   $configs
     * @return  static
     */
    public function setConfigs(array $configs)
    {
        $configInstance = $this->getConfigInstance();

        $configInstance->set($configs);

        return $this;
    }
}
