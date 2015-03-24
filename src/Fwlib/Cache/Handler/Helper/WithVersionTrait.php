<?php
namespace Fwlib\Cache\Handler\Helper;

/**
 * Trait for cache store data with version, for batch delete
 *
 * Eg: A multiple page list is stored in cache, its key is 'foo', at beginning
 * its version is 1, this version number is also stored in cache, with key
 * 'foo-ver'. When retrieve every page of this list, we should use key
 * 'foo-ver-1-p-1' for page 1. Now we want to change all page of this list, so
 * we increase version of this list to 2, and then the first page should get by
 * key 'foo-ver-2-p-1'. Need not delete all page data of version 1, and some
 * cache system does not support this(memcached).
 *
 * @property    string  $versionSuffix  Append this to key, got key for version
 * @method      string  get($key)
 * @method      static  set($key, $value)
 *
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait WithVersionTrait
{
    /**
     * Get current version number
     *
     * @param   string  $key
     * @return  int
     */
    public function getVersion($key)
    {
        $versionKey = $key . $this->versionSuffix;
        $version = $this->get($versionKey);

        if (empty($version)) {
            $version = 1;
            $this->set($versionKey, $version, 0);
        }

        return intval($version);
    }


    /**
     * Increase version and return new version number
     *
     * @param   string  $key
     * @param   int     $increment
     * @param   int     $max
     * @return  int
     */
    public function increaseVersion($key, $increment = 1, $max = 65535)
    {
        $version = $this->getVersion($key);

        $version += $increment;

        if ($max < $version) {
            $version = 1;
        }

        $versionKey = $key . $this->versionSuffix;
        $this->set($versionKey, $version, 0);

        return $version;
    }
}
