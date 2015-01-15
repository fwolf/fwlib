<?php
namespace Fwlib\Cache;

/**
 * Cached caller aware interface
 *
 * This interface's implement can be called by CachedCaller, simplify way to
 * build cached version of normal method.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface CachedCallerAwareInterface
{
    /**
     * Get key to get/set cache
     *
     * @param   string  $method     Method being called
     * @param   array   $params     Method calling params
     * @return  string
     */
    public function getCacheKey($method, array $params = array());


    /**
     * Get cache lifetime by key
     *
     * @param   string  $key
     * @return  int     By seconds normally
     */
    public function getCacheLifetime($key);


    /**
     * If to force refresh cache
     *
     * Return true to ignore exists cache, and re-set cache after call.
     *
     * @return  boolean
     */
    public function isForceRefreshCache();


    /**
     * If to use cache
     *
     * Return false to totally disable get/set of cache.
     *
     * @return  boolean
     */
    public function isUseCache();
}
