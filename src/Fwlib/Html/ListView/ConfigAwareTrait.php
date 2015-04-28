<?php
namespace Fwlib\Html\ListView;

/**
 * Trait for use shared Config instance between {@see ListView} and components
 *
 * Different with {@see \Fwlib\Config\ConfigAwareTrait}, this trait use
 * another Config child instance {@see \Fwlib\Html\ListView\Config}, with
 * default config value defined.
 *
 * Although used in components, these setter methods should only be used in
 * {@see ListView}, because its main entrance.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ConfigAwareTrait
{
    use \Fwlib\Config\ConfigAwareTrait;


    /**
     * @return Config
     */
    protected function getConfigInstance()
    {
        if (is_null($this->configInstance)) {
            $config = new Config;

            // Config already contains default configs

            $this->configInstance = $config;
        }

        return $this->configInstance;
    }


    /**
     * Set config instance from {@ListView} to components
     *
     * @param   Config  $instance
     * @return  static
     */
    public function setConfigInstance(Config $instance)
    {
        $this->configInstance = $instance;

        return $this;
    }
}
