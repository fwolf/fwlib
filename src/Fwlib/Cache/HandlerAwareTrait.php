<?php
namespace Fwlib\Cache;

use Fwlib\Cache\HandlerInterface as CacheHandlerInterface;

/**
 * Trait for class directly use cache handler
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait HandlerAwareTrait
{
    /**
     * @var $cacheHandler
     */
    protected $cacheHandler = null;


    /**
     * Getter of cache handler instance
     *
     * @return  CacheHandlerInterface
     */
    protected function getCacheHandler()
    {
        return $this->cacheHandler;
    }


    /**
     * Setter of cache handler instance
     *
     * @param   CacheHandlerInterface $handler
     * @return  static
     */
    public function setCacheHandler(CacheHandlerInterface $handler)
    {
        $this->cacheHandler = $handler;

        return $this;
    }
}
