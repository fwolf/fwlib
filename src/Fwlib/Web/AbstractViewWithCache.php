<?php
namespace Fwlib\Web;

use Fwlib\Cache\CachedCallerAwareInterface;
use Fwlib\Cache\CachedCallerAwareTrait;
use Fwlib\Cache\HandlerAwareTrait as CacheHandlerAwareTrait;
use Fwlib\Util\UtilContainer;

/**
 * View with cache feature
 *
 * The {@see getOutput()} is adapted to work with cache on its own. But this
 * class can also work with {@see CachedCaller}, let {@see getOutput()} simply
 * return output content.
 *
 * Cache is disabled default, can be enabled by change {@see $useCache}
 * property, or call {@see setUseCache()}.
 *
 * Sometimes we need temporary disable cache or refresh cache data instantly,
 * this can be done by overwrite {@see isForceRefreshCache()}, read a special
 * url or environment variable, then return true for that.
 *
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractViewWithCache extends AbstractView implements
    CachedCallerAwareInterface
{
    use CachedCallerAwareTrait;
    use CacheHandlerAwareTrait;


    /**
     * @var bool
     */
    protected $forceRefreshCache = false;

    /**
     * @var bool
     */
    protected $useCache = false;


    /**
     * {@inheritdoc}
     *
     * Generate key from request uri
     */
    public function getCacheKey($method, array $params = [])
    {
        $envUtil = UtilContainer::getInstance()->getEnv();
        $requestUri = $envUtil->getServer('REQUEST_URI');

        $key = str_replace(['?', '&', '=', '//'], '/', $requestUri);

        // If a special url parameter is used to force refresh cache, it may
        // need to remove it from key here.

        $key = rtrim($key, '/');

        return $key;
    }


    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        $cache = $this->getCacheHandler();

        if (!$this->isUseCache()) {
            return parent::getOutput();
        }

        $key = $this->getCacheKey('getOutput');
        $lifetime = $this->getCacheLifetime($key);

        if ($this->isForceRefreshCache()) {
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
}
