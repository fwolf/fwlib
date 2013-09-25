<?php
namespace Fwlib\Base;

use Fwlib\Base\AbstractAutoNewObj;
use Fwlib\Config\Config;

/**
 * Base class for auto new $config object
 *
 * @package     Fwlib\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-09-25
 */
abstract class AbstractAutoNewConfig extends AbstractAutoNewObj
{
    /**
     * Config object
     *
     * @var Fwlib\Config\Config
     */
    public $config = null;


    /**
     * Constructor
     *
     * @param   array   $config
     */
    public function __construct($config = array())
    {
        // Unset for auto new
        unset($this->config);

        $this->setConfigDefault();

        if (!empty($config)) {
            $this->config->set($config);
        }
    }


    /**
     * New config object
     *
     * @return Fwlib\Config\Config
     */
    protected function newObjConfig()
    {
        return new Config;
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
        return $this->config->get($key, $default);
    }


    /**
     * Set config value
     *
     * @param   string  $key
     * @param   mixed   $val    Should not null except $key is array
     * @return  $this
     */
    public function setConfig($key, $val = null)
    {
        $this->config->set($key, $val);

        return $this;
    }


    /**
     * Set default config
     *
     * @return  $this
     */
    protected function setConfigDefault()
    {
        // Dummy, extend by child class

        return $this;
    }
}
