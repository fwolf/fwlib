<?php
namespace Fwlib\Config;

/**
 * Trait for class use Config as property
 *
 * @see         ConfigAwareInterface
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ConfigAwareTrait
{
    /**
     * Config instance
     *
     * This property used to named as $config, and as it implements
     * ArrayAccess, it can used same style of array. When reform to trait, the
     * constructor is removed, and loading of default configs also changed, from
     * load in constructor, to trigger by getter of Config instance. If keep
     * using Config instance directly, the loading can not be triggered.
     *
     * So, do NOT use this property to retrieve config value, use getConfig()
     * instead, which will call Config instance getter and check/load default
     * configs.
     *
     * @var Config
     */
    protected $configInstance = null;


    /**
     * @see ConfigAwareInterface::getConfig()
     *
     * @param   string $key
     * @param   mixed  $default Return this if key not exists
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
     * @see ConfigAwareInterface::getConfigs()
     *
     * @return  array
     */
    public function getConfigs()
    {
        return $this->getConfigInstance()->getAll();
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
     * @see ConfigAwareInterface::setConfig()
     *
     * @param   string $key
     * @param   mixed  $val
     * @return  $this
     */
    public function setConfig($key, $val)
    {
        $configInstance = $this->getConfigInstance();

        $configInstance->set($key, $val);

        return $this;
    }


    /**
     * @see ConfigAwareInterface::setConfigs()
     *
     * @param   array $configs
     * @return  $this
     */
    public function setConfigs(array $configs)
    {
        $configInstance = $this->getConfigInstance();

        $configInstance->set($configs);

        return $this;
    }
}
