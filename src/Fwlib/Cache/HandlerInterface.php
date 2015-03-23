<?php
namespace Fwlib\Cache;

/**
 * Cache handler interface
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface HandlerInterface
{
    /**
     * Delete cache data
     *
     * @param   string  $key
     * @return  HandlerInterface
     */
    public function delete($key);


    /**
     * Get cache data
     *
     * If $lifetime given, will check if cache data exceeds its lifetime, this
     * is needed for some cache type without auto-expire(eg: file) feature.
     *
     * @param   string  $key
     * @param   int     $lifetime
     * @return  mixed
     */
    public function get($key, $lifetime = null);


    /**
     * Convert param string to key used in cache system
     *
     * In some cache system, key may need hash or computed.
     *
     * @param   string  $str
     * @return  string
     */
    public function getKey($str);


    /**
     * Write cache data
     *
     * If cache type not support auto-expire(eg: file), $lifetime can omit.
     *
     * @param   string  $key
     * @param   mixed   $val
     * @param   int     $lifetime
     * @return  HandlerInterface
     */
    public function set($key, $val, $lifetime = null);
}
