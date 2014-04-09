<?php
namespace Fwlib\Mvc;

use Fwlib\Cache\CacheInterface;

/**
 * Model in MVC
 *
 * Dependence of ServiceContainer has been removed, this class is mostly
 * useless, just leave here for at least a template for project base model
 * class.
 *
 * To make this a little usefull, the feature of cached method call is kept.
 *
 * @copyright   Copyright 2008-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-04-06
 */
abstract class AbstractModel
{
    /**
     * Should it use cache or not
     *
     * @var bool
     */
    protected $useCache = false;


    /**
     * Call method with cache
     *
     * By using this method, it need not to apply cache check/get/set to each
     * method using cache anymore. This is less trick than rely on magic
     * method __call and check prefix of method name to determine if to use
     * cache, although the change of client code between with/without cache
     * cost a little more work.
     *
     * If $useCache is false, this will skip cache get and set.
     *
     * If forceRefreshCache() return true, this will skip cache get, but still
     * write result to cache.
     *
     * @param   string  $method
     * @param   array   $paramArray
     * @return  mixed
     */
    public function cachedCall($method, array $paramArray = null)
    {
        $cache = $this->getCache();

        if (!$this->useCache) {
            return call_user_func_array(array($this, $method), $paramArray);
        }

        $key = $this->getCacheKey($method, $paramArray);
        $lifetime = $this->getCacheLifetime($key);

        if ($this->forceRefreshCache()) {
            $result = call_user_func_array(array($this, $method), $paramArray);

            $cache->set($key, $result, $lifetime);

        } else {
            $result = $cache->get($key, $lifetime);

            if (is_null($result)) {
                $result = call_user_func_array(
                    array($this, $method),
                    $paramArray
                );

                $cache->set($key, $result, $lifetime);
            }
        }

        return $result;
    }


    /**
     * Force to re-generate cache
     *
     * Sometimes we need temporary disable cache or refresh cache data
     * instantly, this can be done by set a special url or environment, then
     * extend this method to check and return true.
     *
     * @see     AbstractViewCache::forceRefreshCache()  Same
     * @return  bool
     */
    protected function forceRefreshCache()
    {
        return false;
    }


    /**
     * Get Cache instance
     *
     * @return CacheInterface
     */
    abstract protected function getCache();


    /**
     * Gen key of cache by method name and argument
     *
     * This is a default implement, child class can change as needed.
     *
     * @param   string  $method
     * @param   array   $paramArray
     * @return  string
     */
    protected function getCacheKey($method, array $paramArray = null)
    {
        $key = str_replace('\\', '/', get_class($this));
        $key .= "/$method";

        foreach ((array)$paramArray as $param) {
            if (is_array($param)) {
                // Index of array param will add to key, include int index.
                // Another solution, $param can convert to json string and
                // then replace special chars in json to '/' to got $key
                // suffix. But when $param doesn't contain many data,
                // json_encode() + str_replace() should cost more time than
                // foreach.
                foreach ($param as $k => $v) {
                    $key .= '/' . (string)$k . '/' . (string)$v;
                }

            } elseif (is_object($param)) {
                // Object param will convert to string with format:
                // classNameWithoutNamespace/md5OfJsonEncodedObject
                $className = current(
                    array_slice(explode('\\', get_class($param)), -1)
                );
                $key .= "/$className/" . md5(json_encode($param));

            } else {
                $key .= '/' . (string)$param;
            }
        }

        return $key;
    }


    /**
     * Got cache lifetime, by second
     *
     * This implement only return a solid lifetime, child class should extend
     * to fit application demand.
     *
     * @see     AbstractViewCache::getCacheLifetime()   Same
     * @param   string  $key
     * @return  int
     */
    protected function getCacheLifetime($key = null)
    {
        // Default 60s * 60m = 3600s
        return 3600;
    }


    /**
     * Getter of $useCache
     *
     * @return  boolean
     */
    public function getUseCache()
    {
        return $this->useCache;
    }


    /**
     * Setter of $useCache
     *
     * @see     AbstractViewCache::setUseCache()    Same
     * @param   boolean $useCache
     * @return  AbstractViewCache
     */
    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;

        return $this;
    }
}
