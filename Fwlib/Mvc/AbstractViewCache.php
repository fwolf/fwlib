<?php
namespace Fwlib\Mvc;

use Fwlib\Cache\CacheInterface;
use Fwlib\Mvc\AbstractView;

/**
 * View with Cache feature
 *
 * Cache is disabled default, need extend class and set $useCache to true or
 * call useCache() to enable it.
 *
 * @package     Fwlib\Mvc
 * @copyright   Copyright 2008-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-04-06
 */
abstract class AbstractViewCache extends AbstractView
{
    /**
     * @var Fwlib\Cache\CacheInterface
     */
    protected $cache = null;

    /**
     * Should it use Cache to store output for reuse
     *
     * @var bool
     */
    protected $useCache = false;


    /**
     * Contructor
     *
     * @param   string  $pathToRoot
     */
    public function __construct($pathToRoot = null)
    {
        // Unset for auto new
        unset($this->cache);

        parent::__construct($pathToRoot);
    }


    /**
     * Force to re-generate cache
     *
     * Sometimes we need temporary disable cache or refresh cache data
     * instantly, this can be done by set a special url or environment, then
     * extend this method to check and return true.
     *
     * @see     AbstractModel::forceRefreshCache()  Same
     * @return  bool
     */
    protected function forceRefreshCache()
    {
        return false;
    }


    /**
     * Gen key of cache by request uri
     *
     * $action is not used in this implement, but maybe other extended child
     * class need to use it.
     *
     * @param   string  $action
     * @return  string
     */
    protected function getCacheKey($action = null)
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $key = $_SERVER['REQUEST_URI'];
        } else {
            // Maybe cli mode, use argv array
            $key = implode('/', $_SERVER['argv']);
        }
        $key = str_replace(array('?', '&', '=', '//'), '/', $key);

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
     * @see     AbstractModel::getCacheLifetime()   Same
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
     * @param   string  $action
     * @return  string
     */
    public function getOutput($action = null)
    {
        if (!$this->useCache) {
            return parent::getOutput($action);
        }

        $key = $this->getCacheKey($action);
        $lifetime = $this->getCacheLifetime($key);

        if ($this->forceRefreshCache()) {
            $output = parent::getOutput($action);

            $this->cache->set($key, $output, $lifetime);

        } else {
            $output = $this->cache->get($key, $lifetime);

            if (empty($output)) {
                $output = parent::getOutput($action);

                $this->cache->set($key, $output, $lifetime);
            }
        }

        return $output;
    }


    /**
     * New Cache instance
     *
     * Child class need implement this method to create Cache instance, and
     * configure it if needed.
     *
     * @return  Fwlib\Cache\CacheInterface
     */
    abstract protected function newInstanceCache();


    /**
     * Setter of $useCache
     *
     * @see     AbstractModel::useCache()   Same
     * @param   bool    $useCache
     * @return  AbstractViewCache
     */
    public function useCache($useCache)
    {
        $this->useCache = $useCache;

        return $this;
    }
}
