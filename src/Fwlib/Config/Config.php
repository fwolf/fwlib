<?php
namespace Fwlib\Config;

use Fwlib\Util\UtilContainerAwareTrait;

/**
 * Config class
 *
 * Use as other class's property.
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Config implements \ArrayAccess
{
    use UtilContainerAwareTrait;


    /**
     * Config data array
     *
     * @var array
     */
    protected $configs = [];

    /**
     * Separator for config key sections
     *
     * @var string
     */
    protected $separator = '.';


    /**
     * Get config value
     *
     * String with separator store as multi-dimensional array.
     *
     * PHP has copy on write feature, so readonly pointer need use '&'.
     *
     * @param   string  $key
     * @param   mixed   $default        Return this if key not exists
     * @return  mixed
     */
    public function get($key, $default = null)
    {
        if (false === strpos($key, $this->separator)) {
            $arrayUtil = $this->getUtilContainer()->getArray();
            return $arrayUtil->getIdx($this->configs, $key, $default);

        } else {
            // Recognize separator
            $sections = explode($this->separator, $key);
            $configPointer = $this->configs;

            // Loop match value
            // Each loop will go deeper in multi-dimension array
            foreach ($sections as $section) {
                if (isset($configPointer[$section])) {
                    $configPointer = $configPointer[$section];
                } else {
                    return $default;
                }
            }

            return($configPointer);
        }
    }


    /**
     * Reset all config and set new
     *
     * @param   array   $configData
     * @return  static
     */
    public function load($configData)
    {
        $this->configs = [];

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
     * @return  static
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
            $this->configs[$key] = $val;
        } else {
            // Recognize separator
            $sections = explode($this->separator, $key);
            $parentLevels = count($sections) - 1;
            $configPointer = &$this->configs;

            // Check and create middle level for multi-dimension array
            // Pointer change every loop, goes deeper to sub array
            for ($i = 0; $i < $parentLevels; $i ++) {
                $currentKey = $sections[$i];

                // 'a.b.c', if b is not set, create it as an empty array
                if (!isset($configPointer[$currentKey])) {
                    $configPointer[$currentKey] = [];
                }

                // Go down to next level
                $configPointer = &$configPointer[$currentKey];
            }

            // At last level, set the value
            $configPointer[$sections[$parentLevels]] = $val;
        }

        return $this;
    }
}
