<?php
namespace Fwlib\Cache\Handler;

use Fwlib\Cache\AbstractHandler;
use Fwlib\Cache\OperateType;

/**
 * Cache with PHP array as storage
 *
 * Usually this is a simulator of cache, migrate from old Cache base class,
 * kept for back compatible.
 *
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class PhpArray extends AbstractHandler
{
    /**
     * Format: {key: {value, expire}}
     *
     * Field expire is end timestamp.
     *
     * @var array
     */
    protected $cacheData = [];


    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        unset($this->cacheData[$this->hashKey($key)]);

        $this->log(OperateType::DELETE, $key, true);

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function get($key, $lifetime = null)
    {
        $hashedKey = $this->hashKey($key);

        if (array_key_exists($hashedKey, $this->cacheData) &&
            $this->cacheData[$hashedKey]['expire'] >= time()
        ) {
            $result = $this->cacheData[$hashedKey]['value'];
            $this->log(OperateType::GET, $key, true);

        } else {
            $result = null;
            $this->log(OperateType::GET, $key, false);
        }

        return $result;
    }


    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfigs()
    {
        $configs = parent::getDefaultConfigs();

        // Default cache lifetime, in second
        // Default 30days:
        //   60sec * 60min = 3600s * 24h = 86400s * 30 = 2592000s
        // 0 means forever.
        $configs['lifetime'] = 2592000;

        return $configs;
    }


    /**
     * {@inheritdoc}
     */
    public function isExpired($key, $lifetime = null)
    {
        $hashedKey = $this->hashKey($key);

        if (array_key_exists($hashedKey, $this->cacheData)) {
            return $this->cacheData[$hashedKey]['expire'] < time();

        } else {
            return true;
        }
    }


    /**
     * {@inheritdoc}
     */
    public function set($key, $val, $lifetime = null)
    {
        $expireTime = $this->computeExpireTime($lifetime);

        $this->cacheData[$this->hashKey($key)] = [
            'value'  => $val,
            'expire' => $expireTime,
        ];

        $this->log(OperateType::SET, $key, true);

        return $this;
    }
}
