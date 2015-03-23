<?php
namespace Fwlib\Cache;

/**
 * Shared code of cache handlers
 *
 * @property    string  $hashAlgorithm
 * @property    string  $emptyKeyReplacement
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
     * In some cache system, key may need hash or computed.
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

        return hash($this->hashAlgorithm, $key);
    }
}
