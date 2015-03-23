<?php
namespace Fwlib\Web;

use Fwlib\Cache\HandlerInterface as CacheHandlerInterface;

/**
 * View with Cache feature
 *
 * Cache is disabled default, need extend class and set $useCache property to
 * true or call setUseCache() to enable it.
 *
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractViewCache extends AbstractView
{
    /**
     * Should it use Cache to store output for reuse
     *
     * @var bool
     */
    protected $useCache = false;


    /**
     * Force to re-generate cache
     *
     * Sometimes we need temporary disable cache or refresh cache data
     * instantly, this can be done by set a special url or environment, then
     * extend this method to check and return true.
     *
     * @return  bool
     */
    protected function forceRefreshCache()
    {
        return false;
    }


    /**
     * Get Cache instance
     *
     * @return CacheHandlerInterface
     */
    abstract protected function getCache();


    /**
     * Gen key of cache by request uri
     *
     * @return  string
     */
    protected function getCacheKey()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $key = $_SERVER['REQUEST_URI'];
        } else {
            // Maybe cli mode, use argv array
            $key = implode('/', $_SERVER['argv']);
        }
        $key = str_replace(['?', '&', '=', '//'], '/', $key);

        // If a special url parameter is used to force refresh cache, it may
        // need to remove it from key here.

        // Remove tailing '/'
        if ('/' == substr($key, -1)) {
            $key = substr($key, 0, strlen($key) - 1);
        }

        return $key;
    }


    /**
     * Got cache lifetime, by second
     *
     * This implement only return a solid lifetime, child class should extend
     * to fit application demand.
     *
     * @param   string  $key
     * @return  int
     */
    protected function getCacheLifetime($key = null)
    {
        // Default 60s * 60m = 3600s
        return 3600;
    }


    /**
     * Get output content with cache
     *
     * @return  string
     */
    public function getOutput()
    {
        $cache = $this->getCache();

        if (!$this->useCache) {
            return parent::getOutput();
        }

        $key = $this->getCacheKey();
        $lifetime = $this->getCacheLifetime($key);

        if ($this->forceRefreshCache()) {
            $output = parent::getOutput();

            $cache->set($key, $output, $lifetime);

        } else {
            $output = $cache->get($key, $lifetime);

            if (empty($output)) {
                $output = parent::getOutput();

                $cache->set($key, $output, $lifetime);
            }
        }

        return $output;
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
     * @param   boolean $useCache
     * @return  AbstractViewCache
     */
    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;

        return $this;
    }
}
