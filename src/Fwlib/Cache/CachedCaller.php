<?php
namespace Fwlib\Cache;

use Fwlib\Cache\HandlerInterface as CacheHandlerInterface;

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
     * @var CacheHandlerInterface
     */
    protected $handler = null;


    /**
     * {@inheritdoc}
     */
    public function call(
        CachedCallerAwareInterface $instance,
        $method,
        array $params = [],
        callable $readRenderer = null,
        callable $writeRenderer = null
    ) {
        $cache = $this->getHandler();

        if (!$instance->isUseCache()) {
            return call_user_func_array([$instance, $method], $params);
        }

        $key = $instance->getCacheKey($method, $params);
        $lifetime = $instance->getCacheLifetime($key);

        if ($instance->isForceRefreshCache() ||
            is_null($result = $cache->get($key, $lifetime))
        ) {
            $result = call_user_func_array([$instance, $method], $params);
            /** @type callable $writeRenderer */
            $cache->set(
                $key,
                is_null($writeRenderer) ? $result : $writeRenderer($result),
                $lifetime
            );

        } elseif (!is_null($readRenderer)) {
            /** @type callable $readRenderer */
            $result = $readRenderer($result);
        }

        return $result;
    }


    /**
     * Getter cache handler instance
     *
     * @return  HandlerInterface
     */
    protected function getHandler()
    {
        return $this->handler;
    }


    /**
     * {@inheritdoc}
     */
    public function setHandler(HandlerInterface $handler)
    {
        $this->handler = $handler;

        return $this;
    }
}
