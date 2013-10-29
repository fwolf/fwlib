<?php
namespace Fwlib\Cache;

use Fwlib\Base\AbstractAutoNewConfig;
use Fwlib\Cache\CacheFile;
use Fwlib\Cache\CacheMemcached;
use Fwlib\Util\ArrayUtil;
use Fwlib\Util\Json;

/**
 * Base class for k-v cache system
 *
 * Data store in various way, define in child class, call use create().
 * This class is also child subclass creator(Factory Mode), so not abstract.
 *
 * Main method:
 * - key(), hash or use original key,
 * - set(), write cache data,
 * - get(), read cache data,
 * - del(), delete cache data.
 *
 * @package     Fwlib\Cache
 * @copyright   Copyright 2012-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2012-09-14
 */
class Cache extends AbstractAutoNewConfig
{

    /**
     * Cache data for cache type ''
     *
     * @var array
     */
    protected $cacheData = array();

    /**
     * Error msg
     *
     * @var string
     */
    public $errorMsg = '';

    /**
     * Log for get() op's key and success flag
     *
     * [{key: string, success: boolean}]
     *
     * @var array
     */
    public static $log = array();

    /**
     * Supported cache type, must have corresponding child class defined
     *
     * @var array
     */
    private static $supportedType = array(
        '',
        'file',
        'memcached',
    );


    /**
     * Factory create method
     *
     * @param   string  $type           Cache type
     * @param   array   $config
     * @return  object
     */
    public static function create($type = '', $config = array())
    {
        // Supported cache type
        if (!in_array($type, self::$supportedType)) {
            // @codeCoverageIgnoreStart

            // $this is not allowed in static func
            //$this->Log('Cache type ' . $type . ' not supported.', 4);
            trigger_error(
                'Cache type ' . $type . ' not supported.',
                E_USER_ERROR
            );
            return null;

            // @codeCoverageIgnoreEnd
        }


        // ClassLoader will do include file, 'use' is defined in file header
        $classname = 'Fwlib\Cache\Cache' . ucfirst($type);
        return new $classname($config);
    }


    /**
     * Decode val stored in cache
     *
     * Lifetime get/set various by cache type, assign in subclass
     *
     * @param   string  $str            Str read from cache
     * @return  mixed
     */
    public function decodeVal($str)
    {
        if (1 == $this->config->get('cache-store-method')) {
            // Json to array
            return json_decode($str, true);

        } elseif (2 == $this->config->get('cache-store-method')) {
            // Json to object
            return json_decode($str, false);

        } else {
            // Cache store method = 0 or other, return raw.
            return $str;
        }
    }


    /**
     * Del cache data
     *
     * @param   string  $key
     * @return  $this
     */
    public function del($key)
    {
        unset($this->cacheData[$key]);
        return $this;
    }


    /**
     * Encode val to store in cache
     *
     * Lifetime get/set various by cache type, assign in subclass
     *
     * @param   mixed   $val
     * @return  string
     */
    public function encodeVal($val)
    {
        if (1 == $this->config->get('cache-store-method')
            || 2 == $this->config->get('cache-store-method')
        ) {
            return Json::encodeUnicode($val);

        } else {
            // Raw
            return $val;
        }
    }


    /**
     * Is cache data expire ?
     *
     * @param   string  $key
     * @return  boolean
     */
    public function expire($key)
    {
        // Inner var never expire,
        // Also, there is no good method to keep var set time.
        return false;
    }


    /**
     * Compute expiration time
     *
     * @param   int     $lifetime
     * @param   int     $starttime      Base start time, 0 use time()
     * @return  int                     In unix time
     */
    public function expireTime($lifetime = null, $starttime = 0)
    {
        // If not set, use config
        if (is_null($lifetime)) {
            $lifetime = $this->config->get('cache-lifetime');
        }

        // 0 means never expire
        if (0 == $lifetime) {
            return 0;
        }

        if (0 == $starttime) {
            $starttime = time();
        }

        // If smaller than 30 days
        if (2592000 >= $lifetime) {
            return $starttime + $lifetime;
        }

        // Larger than 30days, it's unix timestamp, ignore $starttime
        return $lifetime;
    }


    /**
     * Get cache data
     *
     * @param   string  $key
     * @param   int     $lifetime       Cache lifetime
     * @return  mixed
     */
    public function get($key, $lifetime = null)
    {
        $key = $this->key($key);

        // Ignored lifetime
        $val = $this->decodeVal(
            ArrayUtil::getIdx($this->cacheData, $key, null),
            0
        );

        self::$log[] = array(
            'key'   => $key,
            'success'   => !is_null($val),
        );
        return $val;
    }


    /**
     * Generate cache key
     *
     * In some cache system, key may need hash or computed.
     *
     * @param   string  $str
     * @return  string
     */
    public function key($str)
    {
        return $str;
    }


    /**
     * Write data to cache
     *
     * @param   string  $key
     * @param   mixed   $val
     * @param   int     $lifetime
     * @return  $this
     */
    public function set($key, $val, $lifetime = null)
    {
        // Lifetime is useless.
        $this->cacheData[$this->key($key)] = $this->encodeVal($val, 0);

        return $this;
    }


    /**
     * Set default config
     *
     * @return  this
     */
    protected function setConfigDefault()
    {
        // Cache type: file, memcached
        // Empty means parent cache class.
        $this->config->set('cache-type', '');

        // Cache store method
        // 0: Raw string or other value.
        //  User should determine the value DO suite cache type.
        // 1: Json, decode to array.
        // 2: Json, decode to object.
        $this->config->set('cache-store-method', 1);

        // Default cache lifetime, in second
        // Can be overwrite by param when get/set.
        // Default/Max 30days:
        //   60sec * 60min = 3600s * 24h = 86400s * 30 = 2592000s
        // Larger than 30days, must assign unix time like memcached,
        //   which is number of seconds since 1970-1-1 as an integer.
        // 0 means forever.
        $this->config->set('cache-lifetime', 2592000);

        return $this;
    }


    /**
     * Get or increase version number
     *
     * Mostly used in memcached for batch items delete.
     *
     * @param   string  $key
     * @param   int     $increment
     * @param   int     $max
     * @return  int
     */
    public function ver($key, $increment = 0, $max = 65535)
    {
        $i = $this->get($key);
        if (empty($i)) {
            $i = 1;
            $this->set($key, $i, 0);
        }

        if (0 != $increment) {
            $i += $increment;
            if ($max < $i) {
                $i = 1;
            }
            $this->set($key, $i, 0);
        }

        return $i;
    }
}