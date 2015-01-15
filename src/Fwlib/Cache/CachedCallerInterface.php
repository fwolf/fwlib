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
interface CachedCallerInterface
{
    /**
     * Call given method
     *
     * After data is read from cache, read renderer can be used to convert
     * data to format/type same as call user function directly.
     *
     * Before write data to cache, write renderer can be used to convert data
     * to proper format for storing with cache.
     *
     * @param   CachedCallerAwareInterface $instance
     * @param   string                     $method
     * @param   array                      $params
     * @param   callable                   $readRenderer
     * @param   callable                   $writeRenderer
     * @return  mixed
     */
    public function call(
        CachedCallerAwareInterface $instance,
        $method,
        array $params = array(),
        $readRenderer = null,
        $writeRenderer = null
    );


    /**
     * Setter of cache handler instance
     *
     * @param   CacheInterface $handler
     * @return  static
     */
    public function setHandler(CacheInterface $handler);
}
