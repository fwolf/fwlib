<?php
namespace Fwlib\Cache;

use Fwlib\Cache\CacheFile;
use Fwlib\Cache\CacheMemcached;
use Fwlib\Cache\HandlerInterface as CacheHandlerInterface;
use Fwlib\Config\ConfigAwareTrait;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * Base class for k-v cache system
 *
 * Data store in various way, define in child class, call use create().
 * This class is also child subclass creator(Factory Mode), so not abstract.
 *
 * Main method:
 * - getKey(), generate/hash or use original key as key in cache system,
 * - set(), write cache data,
 * - get(), read cache data,
 * - delete(), delete cache data.
 *
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Cache implements CacheHandlerInterface
{
    use ConfigAwareTrait;
    use UtilContainerAwareTrait;


    /**
     * Cache data for cache type ''
     *
     * @var array
     */
    protected $cacheData = [];

    /**
     * Error message
     *
     * @var string
     */
    protected $errorMessage = '';

    /**
     * Log for get() operate
     *
     * Format:
     * [{key: string, success: bool}]
     *
     * @var array
     */
    protected $log = [];

    /**
     * Supported cache type, must have corresponding child class defined
     *
     * @var array
     */
    private static $supportedType = [
        '',
        'file',
        'memcached',
    ];


    /**
     * Factory create method
     *
     * @param   string  $type           Cache type
     * @param   array   $config
     * @return  HandlerInterface
     */
    public static function create($type = '', $config = [])
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
    protected function decodeValue($str)
    {
        if (1 == $this->getConfig('storeMethod')) {
            // Json to array
            return json_decode($str, true);

        } elseif (2 == $this->getConfig('storeMethod')) {
            // Json to object
            return json_decode($str, false);

        } else {
            // Cache store method = 0 or other, return raw.
            return $str;
        }
    }


    /**
     * {@inheritdoc}
     *
     * @param   string  $key
     * @return  Cache
     */
    public function delete($key)
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
    protected function encodeValue($val)
    {
        if (1 == $this->getConfig('storeMethod')
            || 2 == $this->getConfig('storeMethod')
        ) {
            return $this->getUtilContainer()->getJson()->encodeUnicode($val);

        } else {
            // Raw
            return $val;
        }
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
        $key = $this->getKey($key);

        // Ignored lifetime
        $arrayUtil = $this->getUtilContainer()->getArray();
        $val = $this->decodeValue(
            $arrayUtil->getIdx($this->cacheData, $key, null),
            0
        );

        $this->log[] = [
            'key'   => $key,
            'success'   => !is_null($val),
        ];
        return $val;
    }


    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfigs()
    {
        $configs = [];

        // Cache store method
        // 0: Raw string or other value.
        //  User should determine the value DO suite cache type.
        // 1: Json, decode to array.
        // 2: Json, decode to object.
        $configs['storeMethod'] =  1;

        // Default cache lifetime, in second
        // Can be overwrite by param when get/set.
        // Default/Max 30days:
        //   60sec * 60min = 3600s * 24h = 86400s * 30 = 2592000s
        // Larger than 30days, must assign unix time like memcached,
        //   which is number of seconds since 1970-1-1 as an integer.
        // 0 means forever.
        $configs['lifetime'] = 2592000;

        return $configs;
    }


    /**
     * Getter of $errorMessage
     *
     * @return  string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }


    /**
     * Compute expiration time
     *
     * @param   int     $lifetime
     * @param   int     $startTime      Base start time, 0 use time()
     * @return  int                     In unix time
     */
    protected function getExpireTime($lifetime = null, $startTime = 0)
    {
        // If not set, use config
        if (is_null($lifetime)) {
            $lifetime = $this->getConfig('lifetime');
        }

        // 0 means never expire
        if (0 == $lifetime) {
            return 0;
        }

        if (0 == $startTime) {
            $startTime = time();
        }

        // If smaller than 30 days
        if (2592000 >= $lifetime) {
            return $startTime + $lifetime;
        }

        // Larger than 30days, it's unix timestamp, ignore $startTime
        return $lifetime;
    }


    /**
     * {@inheritdoc}
     *
     * @param   string  $str
     * @return  string
     */
    public function getKey($str)
    {
        return $str;
    }


    /**
     * Getter of $log
     *
     * @return  array
     */
    public function getLog()
    {
        return $this->log;
    }


    /**
     * Get version number
     *
     * Mostly used in memcached for batch items delete.
     *
     * @param   string  $key
     * @return  int
     * @see     increaseVersion()
     */
    public function getVersion($key)
    {
        $i = $this->get($key);
        if (empty($i)) {
            $i = 1;
            $this->set($key, $i, 0);
        }

        return $i;
    }


    /**
     * Increase version number
     *
     * Mostly used in memcached for batch items delete.
     *
     * @param   string  $key
     * @param   int     $increment
     * @param   int     $max
     * @return  int
     * @see     getVersion()
     */
    public function increaseVersion($key, $increment = 1, $max = 65535)
    {
        $i = $this->getVersion($key);

        $i += $increment;

        if ($max < $i) {
            $i = 1;
        }

        $this->set($key, $i, 0);

        return $i;
    }


    /**
     * Is cache data expire ?
     *
     * @param   string  $key
     * @param   int     $lifetime
     * @return  boolean
     */
    public function isExpired($key, $lifetime = null)
    {
        // Inner var never expire,
        // Also, there is no good method to keep var set time.
        return false;
    }


    /**
     * Write data to cache
     *
     * @param   string  $key
     * @param   mixed   $val
     * @param   int     $lifetime
     * @return  Cache
     */
    public function set($key, $val, $lifetime = null)
    {
        // Lifetime is useless.
        $this->cacheData[$this->getKey($key)] = $this->encodeValue($val, 0);

        return $this;
    }
}
