<?php
namespace Fwlib\Cache;

/**
 * Cached caller
 *
 * Before call given method, will try read from cache first.
 *
 * Notice: If called method return object originally, after read from cache it
 * will be an array, and need manually convert to object with proper class.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class CachedCaller implements CachedCallerInterface
{
    /**
     * Cache handler instance
     *
     * @type    CacheInterface
     */
    protected $handler = null;


    /**
     * {@inheritdoc}
     */
    public function call(
        CachedCallerAwareInterface $instance,
        $method,
        array $params = array()
    ) {
        $cache = $this->getHandler();

        if (!$instance->isUseCache()) {
            return call_user_func_array(array($instance, $method), $params);
        }

        $key = $instance->getCacheKey($method, $params);
        $lifetime = $instance->getCacheLifetime($key);

        if ($instance->isForceRefreshCache() ||
            is_null($result = $cache->get($key, $lifetime))
        ) {
            $result = call_user_func_array(array($instance, $method), $params);
        }

        $cache->set($key, $result, $lifetime);

        return $result;
    }


    /**
     * Getter cache handler instance
     *
     * @return  CacheInterface
     */
    protected function getHandler()
    {
        return $this->handler;
    }


    /**
     * {@inheritdoc}
     */
    public function setHandler(CacheInterface $handler)
    {
        $this->handler = $handler;

        return $this;
    }
}
