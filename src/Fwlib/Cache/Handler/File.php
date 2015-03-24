<?php
namespace Fwlib\Cache\Handler;

use Fwlib\Cache\AbstractHandler;
use Fwlib\Cache\OperateType;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * Key-value cache system, data store in file
 *
 * Notice: Expired cache file is not deleted automatic.
 *
 * @copyright   Copyright 2010-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class File extends AbstractHandler
{
    use UtilContainerAwareTrait;


    /**
     * Error in config check
     *
     * @var string[]
     */
    protected $errorMessages = [];


    /**
     * Check if cache is ready for use
     *
     * @return  boolean
     */
    public function checkConfig()
    {
        $pass = true;
        $this->errorMessages = [];

        $dir = $this->getConfig('fileDir');
        if (empty($dir)) {
            $this->errorMessages[] = 'No cache file dir defined';
            $pass = false;

        } else {
            $message = $this->checkFileDirConfig($dir);
            if (!empty($message)) {
                $this->errorMessages[] =
                    'Cache file directory config error: ' . $message;
                $pass = false;
            }
        }

        $rule = $this->getConfig('fileRule');
        if (empty($rule)) {
            $this->errorMessages[] = 'No cache file rule defined';
            $pass = false;

        } else {
            $message = $this->checkFileRuleConfig($rule);
            if (!empty($message)) {
                $this->errorMessages[] =
                    'Cache file rule config error: ' . $message;
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
    protected function checkFileDirConfig($dir)
    {
        $message = '';

        if (!file_exists($dir)) {
            if (false == mkdir($dir, 0755, true)) {
                $message = "Fail to create cache directory \"{$dir}\"";
            }

        } else {
            if (!is_writable($dir)) {
                $message = "Cache directory \"{$dir}\" is not writable.";
            }
        }

        return $message;
    }


    /**
     * Check cache rule exist and valid
     *
     * If error, return error msg, else return empty str.
     *
     * @param   string  $rule
     * @return  string
     */
    protected function checkFileRuleConfig($rule)
    {
        if (2 > strlen($rule)) {
            return('Cache rule is not defined or too short');
        }

        if (0 != (strlen($rule) % 2)) {
            return("Cache rule \"$rule\" may not right");
        }

        return '';
    }


    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $file = $this->getFilePath($key);

        if (file_exists($file)) {
            $success = unlink($file);
            $this->log(OperateType::DELETE, $key, $success);
        }

        $this->log(OperateType::DELETE, $key, true);

        return $this;
    }


    /**
     * {@inheritdoc}
     *
     * File cache should check lifetime when get, return null when fail.
     */
    public function get($key, $lifetime = null)
    {
        if ($this->isExpired($key, $lifetime)) {
            $this->log(OperateType::GET, $key, false);
            return null;
        }

        // Read from file and parse it.
        $file = $this->getFilePath($key);
        $content = file_get_contents($file);
        $this->log(OperateType::GET, $key, !(false === $content));

        return $content;
    }


    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfigs()
    {
        $configs = parent::getDefaultConfigs();


        // Dir where data file store
        $configs['fileDir'] = '/tmp/cache/';

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
        $configs['fileRule'] = '10';


        return $configs;
    }


    /**
     * @return  string[]
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }


    /**
     * Compute data file name of a key
     *
     * @param   string  $key
     * @return  string
     */
    protected function getFileName($key)
    {
        return substr(md5($key), 0, 8);
    }


    /**
     * Compute data file path of a key
     *
     * @param   string  $key
     * @return  string
     */
    protected function getFilePath($key)
    {
        $path = $this->getConfig('fileDir');

        $rules = str_split($this->getConfig('fileRule'), 2);

        foreach ($rules as $rule) {
            // Path section may be empty
            $pathSection = $this->getFilePathSection($rule, $key);
            if (!empty($pathSection)) {
                $pathSection .= '/';
            }

            $path .= $pathSection;
        }

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

        $seed = $key;
        $start = 0;
        $seq = intval($rule{1});
        if (1 == $rule{0}) {
            // md5 from start
            $start = $len * $seq;
            $seed = md5($key);

        } elseif (2 == $rule{0}) {
            // md5 from end
            $start = -1 * $len * ($seq + 1);
            $seed = md5($key);

        } elseif (3 == $rule{0}) {
            // raw from start
            $start = $len * $seq;
            $seed = $key;

        } elseif (4 == $rule{0}) {
            // raw from end
            $start = -1 * $len * ($seq + 1);
            $seed = $key;

        } elseif (5 == $rule{0}) {
            // crc32
            if (3 < $seq) {
                $seq = $seq % 3;
            }
            $start = $len * $seq;
            $seed = hash('crc32', $key);
        }

        return substr($seed, $start, 2);
    }


    /**
     * {@inheritdoc}
     *
     * File cache does not keep lifetime in cache, so it need a lifetime from
     * outside, or use default lifetime config.
     */
    public function isExpired($key, $lifetime = null)
    {
        $file = $this->getFilePath($key);

        // File doesn't exist
        if (!file_exists($file)) {
            return true;
        }

        if (0 == $lifetime) {
            return false;
        }

        $expireTime = $this->computeExpireTime($lifetime, filemtime($file));

        return (time() > $expireTime);
    }


    /**
     * {@inheritdoc}
     */
    public function set($key, $val, $lifetime = null)
    {
        $file = $this->getFilePath($key);

        // Create each level dir if not exists
        $fileSystemUtil = $this->getUtilContainer()->getFileSystem();
        $dir = $fileSystemUtil->getDirName($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        // Finally write file
        $result = file_put_contents($file, $val, LOCK_EX);

        $this->log(OperateType::SET, $key, false !== $result);

        return $this;
    }
}
