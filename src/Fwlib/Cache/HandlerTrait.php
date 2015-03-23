<?php
namespace Fwlib\Cache;

use Fwlib\Config\ConfigAwareTrait;

/**
 * Shared code of cache handlers
 *
 * @property    string  $emptyKeyReplacement
 * @property    string  $hashAlgorithm
 *
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait HandlerTrait
{
    use ConfigAwareTrait;
    use LoggerAwareTrait;


    /**
     * Compute expiration timestamp
     *
     * @param   int     $lifetime       Length, in seconds
     * @param   int     $startTime      Start timestamp, default use time()
     * @return  int                     End timestamp or 0 for never expire
     */
    protected function computeExpireTime($lifetime = null, $startTime = 0)
    {
        // If not set, read lifetime from config
        if (is_null($lifetime)) {
            $lifetime = $this->getLifetime();
        }

        // 0 means never expire
        if (0 == $lifetime) {
            return 0;
        }

        if (0 == $startTime) {
            $startTime = time();
        }

        return $startTime + $lifetime;
    }


    /**
     * Get lifetime length, in seconds
     *
     * For solid lifetime length, define them in configs.
     * For dynamic lifetime length, overwrite this method to compute it.
     *
     * This implement will return 0 if config not set, means never expire.
     *
     * @param   string  $key
     * @return  int
     */
    protected function getLifetime($key = null)
    {
        true || $key;

        return $this->getConfig('lifetime', 0);
    }


    /**
     * Convert required key to actual key inner used
     *
     * In some cache system, key may need manual hashed.
     *
     * @param   string  $key
     * @return  string
     */
    protected function hashKey($key)
    {
        $key = trim($key);

        // Key can not be empty
        if (empty($key)) {
            return $this->emptyKeyReplacement;
        }

        return empty($this->hashAlgorithm)
            ? $key
            : hash($this->hashAlgorithm, $key);
    }
}
