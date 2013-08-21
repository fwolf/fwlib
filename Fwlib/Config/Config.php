<?php
namespace Fwlib\Config;

use Fwlib\Util\ArrayUtil;

/**
 * Config class
 *
 * Use as other class's property.
 *
 * @package     Fwlib\Config
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib.Config@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-20
 */
class Config
{
    /**
     * Config data array
     */
    public $config = array();

    /**
     * Separator for config key
     * @var string
     */
    public $separator = '.';


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
            return ArrayUtil::getIdx($this->config, $key, $default);
        } else {
            // Recoginize separator
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
     * Set config value
     *
     * Multi-dimensional array style setting supported,
     * If $key include separator, will converte to array by it recurrently.
     *
     * eg: system.format.time => $this->config['system']['format']['time']
     *
     * @param   string  $key
     * @param   mixed   $val
     */
    public function set($key, $val)
    {
        if (false === strpos($key, $this->separator)) {
            $this->config[$key] = $val;
        } else {
            // Recoginize separator
            $ar = explode($this->separator, $key);
            $j = count($ar) - 1;
            $c = &$this->config;

            // Check and create middle level for mutli-dimension array
            // $c change every loop, goes deeper to sub array
            for ($i = 0; $i < $j; $i ++) {
                $currentKey = $ar[$i];

                // 'a.b.c', if b is not set, create it as an empty array
                if (!isset($c[$currentKey])) {
                    $c[$currentKey] = array();
                }

                // Go down to next level
                $c = &$c[$currentKey];
            }

            // At last level, set the value
            $c[$ar[$j]] = $val;
        }
    }
}
