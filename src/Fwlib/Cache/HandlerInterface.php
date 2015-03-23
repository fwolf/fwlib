<?php
namespace Fwlib\Cache;

/**
 * Cache handler interface
 *
 * The unit of lifetime is second.
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
     * @return  static
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
     * Is cache data expire ?
     *
     * @param   string  $key
     * @param   int     $lifetime
     * @return  bool                True means it IS expired.
     */
    public function isExpired($key, $lifetime = null);


    /**
     * Write cache data
     *
     * If cache type not support auto-expire(eg: file), $lifetime can omit.
     *
     * @param   string  $key
     * @param   mixed   $val
     * @param   int     $lifetime
     * @return  static
     */
    public function set($key, $val, $lifetime = null);
}
