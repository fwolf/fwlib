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
     * @param   CachedCallerAwareInterface $instance
     * @param   string                     $method
     * @param   array                      $params
     * @return  int|string|array
     */
    public function call(
        CachedCallerAwareInterface $instance,
        $method,
        array $params = array()
    );


    /**
     * Setter of cache handler instance
     *
     * @param   CacheInterface $handler
     * @return  static
     */
    public function setHandler(CacheInterface $handler);
}
