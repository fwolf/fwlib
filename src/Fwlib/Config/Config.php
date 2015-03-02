<?php
namespace Fwlib\Config;

use Fwlib\Util\UtilAwareInterface;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\UtilContainerInterface;

/**
 * Config class
 *
 * Use as other class's property.
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Config implements \ArrayAccess, UtilAwareInterface
{
    /**
     * Config data array
     *
     * @var array
     */
    public $config = [];

    /**
     * Separator for config key
     *
     * @var string
     */
    public $separator = '.';

    /**
     * @var UtilContainer
     */
    protected $utilContainer = null;


    /**
     * Get config value
     *
     * String with separator store as multi-dimensional array.
     *
     * @param   string  $key
     * @param   mixed   $default        Return this if key not exists
     * @return  mixed
     */
    public function get($key, $default = null)
    {
        if (false === strpos($key, $this->separator)) {
            $arrayUtil = $this->getUtilContainer()->getArray();
            return $arrayUtil->getIdx($this->config, $key, $default);

        } else {
            // Recognize separator
            $ar = explode($this->separator, $key);
            $c = &$this->config;

            // Loop match value
            // Each loop will go deeper in multi-dimension array
            foreach ($ar as $val) {
                if (isset($c[$val])) {
                    $c = &$c[$val];
                } else {
                    return $default;
                }
            }
            return($c);
        }
    }


    /**
     * {@inheritdoc}
     */
    public function getUtilContainer()
    {
        if (is_null($this->utilContainer)) {
            $this->utilContainer = UtilContainer::getInstance();
        }

        return $this->utilContainer;
    }


    /**
     * Reset all config and set new
     *
     * @param   array   $configData
     * @return  Config
     */
    public function load($configData)
    {
        $this->config = [];

        if (!is_null($configData)) {
            $this->set($configData);
        }

        return $this;
    }


    /**
     * Whether a offset exists
     *
     * @param   string  $offset
     * @return  boolean
     */
    public function offsetExists($offset)
    {
        return !is_null($this->get($offset, null));
    }


    /**
     * Offset to retrieve
     *
     * @param   string  $offset
     * @return  mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }


    /**
     * Offset to set
     *
     * @param   string  $offset
     * @param   mixed   $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }


    /**
     * Offset to unset
     *
     * @param   string  $offset
     */
    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }


    /**
     * Set config value
     *
     * Multi-dimensional array style setting supported, if $key include
     * separator, will convert to array by it recurrently.
     *
     * eg: system.format.time => $this->config['system']['format']['time']
     *
     * If $key is array, it should be indexed by config key, and $val param is
     * ignored.
     *
     * @param   string|array    $key
     * @param   mixed   $val    Should not null except $key is array
     * @return  $this
     */
    public function set($key, $val = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }

            return $this;
        }


        if (false === strpos($key, $this->separator)) {
            $this->config[$key] = $val;
        } else {
            // Recognize separator
            $ar = explode($this->separator, $key);
            $j = count($ar) - 1;
            $c = &$this->config;

            // Check and create middle level for multi-dimension array
            // $c change every loop, goes deeper to sub array
            for ($i = 0; $i < $j; $i ++) {
                $currentKey = $ar[$i];

                // 'a.b.c', if b is not set, create it as an empty array
                if (!isset($c[$currentKey])) {
                    $c[$currentKey] = [];
                }

                // Go down to next level
                $c = &$c[$currentKey];
            }

            // At last level, set the value
            $c[$ar[$j]] = $val;
        }

        return $this;
    }


    /**
     * Setter of UtilContainer instance
     *
     * @param   UtilContainerInterface  $utilContainer
     * @return  static
     */
    public function setUtilContainer(
        UtilContainerInterface $utilContainer = null
    ) {
        $this->utilContainer = $utilContainer;

        return $this;
    }
}
