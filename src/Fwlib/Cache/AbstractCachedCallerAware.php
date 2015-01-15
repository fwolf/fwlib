<?php
namespace Fwlib\Cache;

/**
 * Class uses cache and can be called by CachedCaller
 *
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractCachedCallerAware implements CachedCallerAwareInterface
{
    /**
     * @type    boolean
     */
    protected $forceRefreshCache = false;

    /**
     * @type    boolean
     */
    protected $useCache = true;


    /**
     * {@inheritdoc}
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
     */
    public function getCacheKey($method, array $params = array())
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
     * {@inheritdoc}
     */
    abstract public function getCacheLifetime($key);


    /**
     * {@inheritdoc}
     */
    public function isForceRefreshCache()
    {
        return $this->forceRefreshCache;
    }


    /**
     * {@inheritdoc}
     */
    public function isUseCache()
    {
        return $this->useCache;
    }


    /**
     * Setter of $forceRefreshCache
     *
     * @param   boolean $forceRefreshCache
     * @return  static
     */
    public function setForceRefreshCache($forceRefreshCache)
    {
        $this->forceRefreshCache = $forceRefreshCache;

        return $this;
    }


    /**
     * Setter of $useCache
     *
     * @param   mixed   $useCache
     * @return  static
     */
    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;

        return $this;
    }
}
