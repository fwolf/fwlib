<?php
namespace Fwlib\Cache\Handler;

use Fwlib\Cache\AbstractHandler;

/**
 * Key-value cache system, data store in memcached
 *
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Memcached extends AbstractHandler
{
    /**
     * Max length of key
     *
     * @var int
     */
    const MAX_KEY_LENGTH = 250;

    /**
     * Memcache instance
     *
     * @var \Memcached
     */
    protected $memcachedInstance = null;


    /**
     * Connect to memcached server
     *
     * @param   array   $serverList
     * @return  \Memcached
     */
    protected function connectMemcachedServer(array $serverList)
    {
        $memcached = new \Memcached();
        $memcached->addServers($serverList);

        foreach ($this->getConfig('memcachedOptions') as $k => $v) {
            $memcached->setOption($k, $v);
        }

        return $memcached;
    }


    /**
     * {@inheritdoc}
     *
     * @param   string  $key
     * @return  static
     */
    public function delete($key)
    {
        $memcached = $this->getMemcachedInstance();

        if (1 == $this->getConfig('memcachedAutoSplit')) {
            // Is value splitted ?
            $totalKey = $this->getTotalKey($key);
            $total = $memcached->get($this->hashKey($totalKey));
            if (false === $total) {
                // No split found
                $success = $memcached->delete($this->hashKey($key));
                $this->log('delete', $key, $success);

            } else {
                // Splitted string
                $total = intval($total);
                for ($i = 1; $i <= $total; $i ++) {
                    $partKey = $this->getPartKey($key, $i, $total);
                    $success = $memcached->delete($this->hashKey($partKey));
                    $this->log('delete', $partKey, $success);
                }

                $success = $memcached->delete($this->hashKey($totalKey));
                $this->log('delete', $totalKey, $success);
            }

        } else {
            $success = $memcached->delete($this->hashKey($key));
            $this->log('delete', $key, $success);
        }

        return $this;
    }


    /**
     * Read cache and return value
     *
     * Lifetime set when write cache.
     * Return null when fail or expire.
     *
     * @param   string  $key
     * @param   int     $lifetime
     * @return  string
     */
    public function get($key, $lifetime = null)
    {
        // Lifetime is handle by memcached

        $memcached = $this->getMemcachedInstance();

        if (1 == $this->getConfig('memcachedAutoSplit')) {
            // Is value splitted ?
            $totalKey = $this->getTotalKey($key);
            $total = $memcached->get($this->hashKey($totalKey));
            $success = \Memcached::RES_SUCCESS == $memcached->getResultCode();
            $this->log('get', $totalKey, $success);

            if (false === $total) {
                // No split found
                $val = $memcached->get($this->hashKey($key));
                $success = \Memcached::RES_SUCCESS ==
                    $memcached->getResultCode();
                $this->log('get', $key, $success);

            } else {
                // Splitted string
                $val = '';
                $total = intval($total);
                for ($i = 1; $i <= $total; $i++) {
                    $partKey = $this->getPartKey($key, $i, $total);
                    $val .= $memcached->get($this->hashKey($partKey));

                    $success = \Memcached::RES_SUCCESS
                        == $memcached->getResultCode();
                    $this->log('get', $partKey, $success);
                }
            }

        } else {
            // Direct get
            $val = $memcached->get($this->hashKey($key));
            $success = \Memcached::RES_SUCCESS == $memcached->getResultCode();
            $this->log('get', $key, $success);
        }

        if ($success) {
            return $val;
        } else {
            return null;
        }
    }


    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfigs()
    {
        $configs = parent::getDefaultConfigs();


        // Memcached server options
        $memcachedOptions = [
            // Better for multi server
            \Memcached::OPT_DISTRIBUTION    =>
                \Memcached::DISTRIBUTION_CONSISTENT,
            // Better for multi app use one memcached
            \Memcached::OPT_PREFIX_KEY  => 'fw',
        ];

        // Use json is better for debug
        if ($this->isMemcachedJsonEnabled()) {
            $memcachedOptions[\Memcached::OPT_SERIALIZER] =
                \Memcached::SERIALIZER_JSON;
        }


        // Default cache lifetime, 60s * 60m * 24h = 86400s(1d)
        $configs['lifetime'] = 86400;

        // Auto split store item larger than max item size
        // 0/off, 1/on, when off, large item store will fail.
        $configs['memcachedAutoSplit'] = 0;

        // Max item size, STRING val exceed this will auto split
        //   and store automatic, user need only care other val type.
        $configs['memcachedMaxItemSize'] = 1024000;

        $configs['memcachedOptions'] = $memcachedOptions;

        // After change server list, you should unset {@see $memcached}.
        // or use setMemcachedServers()
        $configs['memcachedServers'] = [];

        return $configs;
    }


    /**
     * Get memcached server connection
     *
     * @return  \Memcached
     */
    protected function getMemcachedInstance()
    {
        if (is_null($this->memcachedInstance)) {
            $serverList = $this->getValidMemcachedServers();
            $this->memcachedInstance =
                $this->connectMemcachedServer($serverList);
        }

        return $this->memcachedInstance;
    }


    /**
     * Get key of a part in auto split mode
     *
     * @param   string  $originalKey
     * @param   int     $partSequence
     * @param   int     $totalParts
     * @return  string
     */
    protected function getPartKey($originalKey, $partSequence, $totalParts)
    {
        return "{$originalKey}[split-{$partSequence}/{$totalParts}]";
    }


    /**
     * Get key of total in auto split mode
     *
     * @param   string  $originalKey
     * @return  string
     */
    protected function getTotalKey($originalKey)
    {
        return "{$originalKey}[split]";
    }


    /**
     * Read memcached server from config, test and return valid list
     *
     * Will check and remove dead server, by do a test set.
     *
     * Dead server will print in error log.
     *
     * @return  array
     */
    protected function getValidMemcachedServers()
    {
        $servers = $this->getConfig('memcachedServers');

        $memcached = new \Memcached();
        foreach ($servers as $k => $server) {
            $memcached->addServers([$server]);

            $memcached->set($this->hashKey('memcachedServerAliveTest'), '1');

            if (\Memcached::RES_SUCCESS != $memcached->getResultCode()) {
                error_log(
                    'Memcache server ' . implode($server, ':')
                    . ' test fail: ' . $memcached->getResultCode()
                    . ', message: ' . $memcached->getResultMessage()
                );
                unset($servers[$k]);
            }
        }

        return $servers;
    }


    /**
     * {@inheritdoc}
     *
     * Add key length limit, by limit of memcached. If exceed, will only leave
     * tailing part of original key.
     */
    protected function hashKey($key)
    {
        $key = parent::hashKey($key);
        $keyLength = strlen($key);

        $prefix = $this->getConfig(
            'memcachedOptions.' . \Memcached::OPT_PREFIX_KEY
        );
        $prefixLength = strlen($prefix);

        if (self::MAX_KEY_LENGTH < $prefixLength + $keyLength) {
            $validLength = self::MAX_KEY_LENGTH - $prefixLength;
            $key = substr($key, -1 * $validLength);
        }

        return $key;
    }


    /**
     * Is cache data expire ?
     *
     * Memcached expire when get fail, usually call get() and check if result
     * is null or check resultCode is enough.
     *
     * @param   string  $key
     * @param   int     $lifetime
     * @return  boolean                 True means it IS expired
     */
    public function isExpired($key, $lifetime = null)
    {
        // Lifetime is handle by memcached

        $memcached = $this->getMemcachedInstance();

        $val = $memcached->get($this->hashKey($key));

        // Unknown item size, try again for auto split
        if ((\Memcached::RES_SUCCESS != $memcached->getResultCode())
            && (1 == $this->getConfig('memcachedAutoSplit'))
        ) {
            $val = $memcached->get($this->hashKey($key . '[split]'));
        }

        if (\Memcached::RES_SUCCESS == $memcached->getResultCode()) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * Getter of \Memcached::HAVE_JSON
     *
     * @return  bool
     */
    protected function isMemcachedJsonEnabled()
    {
        return \Memcached::HAVE_JSON;
    }


    /**
     * Write data to cache
     *
     * Lifetime is set when write.
     *
     * @param   string  $key
     * @param   string  $val
     * @param   int     $lifetime
     * @return  static
     */
    public function set($key, $val, $lifetime = null)
    {
        $memcached = $this->getMemcachedInstance();

        // Convert expiration time
        $expireTime = $this->computeExpireTime($lifetime);

        // Auto split large string val
        if ((1 == $this->getConfig('memcachedAutoSplit'))
            && is_string($val) && (strlen($val)
            > $this->getConfig('memcachedMaxItemSize'))
        ) {
            $ar = str_split(
                $val,
                $this->getConfig('memcachedMaxItemSize')
            );
            $total = count($ar);

            // Set split total
            $rs = $memcached->set(
                $this->hashKey($key . '[split]'),
                $total,
                $expireTime
            );

            // Set split trunk
            for ($i = 1; $i <= $total; $i++) {
                $rs = $memcached->set(
                    $this->hashKey($key . '[split-' . $i . '/' . $total . ']'),
                    $ar[$i - 1],
                    $expireTime
                );
            }

        } else {
            // Normal set
            $rs = $memcached->set($this->hashKey($key), $val, $expireTime);
        }

        if (false == $rs) {
            // @codeCoverageIgnoreStart

            trigger_error(
                'Memcache set error '
                . $memcached->getResultCode() . ': '
                . $memcached->getResultMessage(),
                E_USER_WARNING
            );

            // @codeCoverageIgnoreEnd
        }

        return $this;
    }


    /**
     * Set memcached server config
     *
     * @param   array   $servers      1 or 2 dim array of server(s)
     * @return  static
     */
    public function setMemcachedServers($servers = [])
    {
        if (empty($servers)) {
            return $this;
        }

        if (isset($servers[0]) && is_array($servers[0])) {
            // 2 dim array
            $this->setConfig('memcachedServers', $servers);
        } else {
            // 1 dim array only
            $this->setConfig('memcachedServers', [$servers]);
        }

        $this->memcached = null;

        return $this;
    }
}
