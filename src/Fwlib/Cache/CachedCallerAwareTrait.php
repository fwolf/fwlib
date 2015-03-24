<?php
namespace Fwlib\Cache;

/**
 * Trait for class uses cache and can be called by {@see CachedCaller}
 *
 * @see CachedCallerAwareInterface
 *
 * @property    bool    $forceRefreshCache
 * @property    bool    $useCache
 *
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait CachedCallerAwareTrait
{
    /**
     * @see CachedCallerAwareInterface::getCacheKey()
     *
     * By default, generate key by class name, method name and params. This is
     * hard to read especially for complicate params, so child class should
     * have their own implement.
     *
     * Convert params to part of key various by each param's type.
     *
     * - array: Append '/key/value' to key. Index of params array will add to
     * key, include int index. Another solution is convert params to json
     * string, then replace special chars in json to '/'. this way is slower.
     *
     * - object: Append '/classNameOfParam/md5(json(paramValue))' to key. The
     * classNameOfParam does not include namespace.
     *
     * - other: Append '/stringValueOfParam' to key.
     *
     * @param   string  $method     Method being called
     * @param   array   $params     Method calling params
     * @return  string
     */
    public function getCacheKey($method, array $params = [])
    {
        $key = str_replace('\\', '/', get_class($this));
        $key .= "/$method";

        foreach ($params as $param) {
            if (is_array($param)) {
                foreach ($param as $k => $v) {
                    // In case value is not plain type
                    if (is_object($v) || is_array($v)) {
                        $v = substr(md5(json_encode($v)), 0, 8);
                    }
                    $key .= '/' . strval($k) . '/' . strval($v);
                }

            } elseif (is_object($param)) {
                $className = current(
                    array_slice(explode('\\', get_class($param)), -1)
                );
                $key .= "/$className/" . substr(md5(json_encode($param)), 0, 8);

            } else {
                $key .= '/' . strval($param);
            }
        }

        return $key;
    }


    /**
     * @see CachedCallerAwareInterface::getCacheLifetime()
     *
     * @param   string  $key
     * @return  int     By seconds normally
     */
    abstract public function getCacheLifetime($key);


    /**
     * @see CachedCallerAwareInterface::isForceRefreshCache()
     *
     * @return  boolean
     */
    public function isForceRefreshCache()
    {
        return $this->forceRefreshCache;
    }


    /**
     * @see CachedCallerAwareInterface::isUseCache()
     *
     * @return  boolean
     */
    public function isUseCache()
    {
        return $this->useCache;
    }


    /**
     * @see CachedCallerAwareInterface::setForceRefreshCache()
     *
     * @param   bool    $forceRefreshCache
     * @return  static
     */
    public function setForceRefreshCache($forceRefreshCache)
    {
        $this->forceRefreshCache = $forceRefreshCache;

        return $this;
    }


    /**
     * @see CachedCallerAwareInterface::setUseCache()
     *
     * @param   bool    $useCache
     * @return  static
     */
    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;

        return $this;
    }
}
