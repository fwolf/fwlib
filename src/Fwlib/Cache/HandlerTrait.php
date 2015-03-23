<?php
namespace Fwlib\Cache;

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
    use LoggerAwareTrait;


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
