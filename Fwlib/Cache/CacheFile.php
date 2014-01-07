<?php
namespace Fwlib\Cache;

use Fwlib\Cache\Cache;

/**
 * Key-value cache system, data store in file
 *
 * Notice: Expired cache file is not deleted automatic.
 *
 * @package     Fwlib\Cache
 * @copyright   Copyright 2010-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2010-01-07
 */
class CacheFile extends Cache
{
    /**
     * Constructor
     *
     * @param   array   $config
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $this->checkConfig();
    }


    /**
     * Check if cache is ready for use
     *
     * @return  boolean
     */
    public function checkConfig()
    {
        $pass = true;

        $dir = $this->config->get('fileDir');
        if (empty($dir)) {
            $this->errorMessage = 'No cache file dir defined.';
            $pass = false;

        } else {
            $s = $this->checkConfigFileDir($dir);
            if (!empty($s)) {
                $this->errorMessage = 'Cache file cfg dir error: ' . $s;
                $pass = false;
            }
        }

        $rule = $this->config->get('fileRule');
        if (empty($rule)) {
            $this->errorMessage = 'No cache file rule defined.';
            $pass = false;

        } else {
            $s = $this->checkConfigFileRule($rule);
            if (!empty($s)) {
                $this->errorMessage = 'Cache file cfg rule error: ' . $s;
                $pass = false;
            }
        }

        return $pass;
    }


    /**
     * Check config/cache store dir valid and writable
     *
     * If error, return error msg, else return empty str.
     *
     * @param   string  $dir
     * @return  string
     */
    public function checkConfigFileDir($dir)
    {
        $s = '';

        if (!file_exists($dir)) {
            if (false == mkdir($dir, 0755, true)) {
                $s = "Fail to create cache dir {$dir}.";
            }

        } else {
            if (!is_writable($dir)) {
                $s = "Cache dir {$dir} is not writable.";
            }
        }

        return $s;
    }


    /**
     * Check cache rule exist and valid
     *
     * If error, return error msg, else return empty str.
     *
     * @param   string  $rule
     * @return  string
     */
    public function checkConfigFileRule($rule)
    {
        if (2 > strlen($rule)) {
            return('Cache rule is not defined or too short.');
        }

        if (0 != (strlen($rule) % 2)) {
            return("Cache rule $rule may not right.");
        }

        return '';
    }


    /**
     * {@inheritdoc}
     *
     * @param   string  $key
     * @return  CacheFile
     */
    public function delete($key)
    {
        $file = $this->getFilePath($key);

        if (file_exists($file)) {
            unlink($file);
        }

        return $this;
    }


    /**
     * Is cache data expire ?
     *
     * File cache does not keep lifetime in cache, so it need a lifetime from
     * outside, or use default lifetime config.
     *
     * @param   string  $key
     * @param   int     $lifetime   Cache lifetime, in second.
     * @return  boolean             True means it IS expired.
     */
    protected function isExpired($key, $lifetime = null)
    {
        $file = $this->getFilePath($key);

        // File doesn't exist
        if (!file_exists($file)) {
            return true;
        }

        if (0 == $lifetime) {
            return false;
        }

        // Check file expire time
        $expireTime = $this->expireTime($lifetime, filemtime($file));

        return (time() > $expireTime);
    }


    /**
     * Read cache and return value
     *
     * File cache should check lifetime when get, return null when fail.
     *
     * @param   string  $key
     * @param   int     $lifetime       Cache lifetime, 0/no check, null/cfg
     * @return  mixed
     */
    public function get($key, $lifetime = null)
    {
        if ($this->isExpired($key, $lifetime)) {
            $this->log[] = array(
                'key'   => $key,
                'success'   => false,
            );
            return null;
        }

        // Read from file and parse it.
        $file = $this->getFilePath($key);
        $cacheContent = file_get_contents($file);
        $this->log[] = array(
            'key'   => $key,
            'success'   => !(false === $cacheContent),
        );

        return $this->decodeValue($cacheContent);
    }


    /**
     * Compute path of a key's data file
     *
     * @param   string  $key
     * @return  string
     */
    public function getFilePath($key)
    {
        $path = $this->config->get('fileDir');

        $ar_rule = str_split($this->config->get('fileRule'), 2);

        foreach ($ar_rule as $rule) {
            // Path section may be empty
            $pathSection = $this->getFilePathSection($rule, $key);
            if (!empty($pathSection)) {
                $pathSection .= '/';
            }

            $path .= $pathSection;
        }

        // Filename
        $path .= $this->getFileName($key);

        return $path;
    }


    /**
     * Compute path of a key by a single rule section
     *
     * @param   string  $rule
     * @param   string  $key
     * @return  string
     */
    protected function getFilePathSection($rule, $key)
    {
        $len = 2;

        if ($len > strlen($rule)) {
            return '';
        }

        $i = intval($rule{1});
        if (1 == $rule{0}) {
            // md5 from start
            $start = $len * $i;
            $seed = md5($key);

        } elseif (2 == $rule{0}) {
            // md5 from end
            $start = -1 * $len * ($i + 1);
            $seed = md5($key);

        } elseif (3 == $rule{0}) {
            // raw from start
            $start = $len * $i;
            $seed = $key;

        } elseif (4 == $rule{0}) {
            // raw from end
            $start = -1 * $len * ($i + 1);
            $seed = $key;

        } elseif (5 == $rule{0}) {
            // crc32
            if (3 < $i) {
                $i = $i % 3;
            }
            $start = $len * $i;
            $seed = hash('crc32', $key);
        }

        return substr($seed, $start, 2);
    }


    /**
     * Compute name of a key's data file
     *
     * @param   string  $key
     * @return  string
     */
    protected function getFileName($key)
    {
        return substr(md5($key), 0, 8);
    }


    /**
     * Write data to cache
     *
     * Lifetime is checked when get().
     *
     * @param   string  $key
     * @param   mixed   $val
     * @param   int     $lifetime
     * @return  $this
     */
    public function set($key, $val, $lifetime = null)
    {
        $file = $this->getFilePath($key);
        $cache = $this->encodeValue($val);

        // Create each level dir if not exists
        $dir = $this->getUtil('FileSystem')->getDirName($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        // Finally write file
        file_put_contents($file, $cache, LOCK_EX);

        return $this;
    }


    /**
     * Set default config
     *
     * @return  this
     */
    protected function setConfigDefault()
    {
        parent::setConfigDefault();


        // Dir where data file store
        $this->config->set('fileDir', '/tmp/cache/');

        /**
         * Cache file store rule
         *
         * Group by every 2-chars, their means:
         * 10   first 2 char of md5 hash, 16 * 16 = 256
         * 11   3-4 char of md5 hash
         * 20   last 2 char of md5 hash
         * 30   first 2 char of key
         * 40   last 2 char of key
         * 5n   crc32, n=0..3, 16 * 16 = 256
         * Join these str with '/', got full path of cache file.
         */
        $this->config->set('fileRule', '10');


        return $this;
    }
}
