<?php
namespace Fwlib\Cache\Handler;

use Fwlib\Cache\Cache;

/**
 * Key-value cache system, data store in memcached
 *
 * @copyright   Copyright 2012-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Memcached extends Cache
{
    /**
     * Memcache instance
     *
     * @var object
     */
    protected $memcached = null;


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

        foreach ((array)$this->getConfig('memcachedOptionDefault') as
            $k => $v) {
            $memcached->setOption($k, $v);
        }

        // @codeCoverageIgnoreStart
        // This config is always empty, because create() is static call, no
        // instance, can't set this option.
        foreach ((array)$this->getConfig('memcachedOption') as
            $k => $v) {
            $memcached->setOption($k, $v);
        }
        // @codeCoverageIgnoreEnd


        return $memcached;
    }


    /**
     * {@inheritdoc}
     *
     * @param   string  $key
     * @return  Memcached
     */
    public function delete($key)
    {
        $memcached = $this->getMemcached();

        if (1 == $this->getConfig('memcachedAutoSplit')) {
            // Is value splitted ?
            $total = $memcached->get($this->hashKey($key . '[split]'));
            if (false === $total) {
                // No split found
                $memcached->delete($this->hashKey($key));

            } else {
                // Splitted string
                for ($i = 1; $i <= $total; $i ++) {
                    $memcached->delete(
                        $this->hashKey($key . '[split-' . $i . '/' . $total . ']')
                    );
                }
                $memcached->delete($this->hashKey($key . '[split]'));
            }
        } else {
            $memcached->delete($this->hashKey($key));
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
     * @return  mixed
     */
    public function get($key, $lifetime = null)
    {
        // Lifetime is handle by memcached

        $memcached = $this->getMemcached();

        if (1 == $this->getConfig('memcachedAutoSplit')) {
            // Is value splitted ?
            $keySplitted = $this->hashKey($key . '[split]');
            $total = $memcached->get($keySplitted);
            $this->log[] = [
                'key'   => $keySplitted,
                'success'   => \Memcached::RES_SUCCESS
                    == $memcached->getResultCode(),
            ];
            if (false === $total) {
                // No split found
                $val = $memcached->get($this->hashKey($key));
                $this->log[] = [
                    'key'   => $this->hashKey($key),
                    'success'   => \Memcached::RES_SUCCESS
                        == $memcached->getResultCode(),
                ];
            } else {
                // Splitted string
                $val = '';
                for ($i = 1; $i <= $total; $i++) {
                    $keySplitted = $this->hashKey(
                        $key . '[split-' . $i . '/' . $total . ']'
                    );
                    $val .= $memcached->get($keySplitted);
                    $this->log[] = [
                        'key'   => $keySplitted,
                        'success'   => \Memcached::RES_SUCCESS
                            == $memcached->getResultCode(),
                    ];
                }
                // Convert to string in JSON format
                $val = '"' . $val . '"';
            }

        } else {
            // Direct get
            $val = $memcached->get($this->hashKey($key));
            $this->log[] = [
                'key'   => $this->hashKey($key),
                'success'   => \Memcached::RES_SUCCESS
                    == $memcached->getResultCode(),
            ];
        }

        if (\Memcached::RES_SUCCESS == $memcached->getResultCode()) {
            return $this->decodeValue($val);
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


        // Memcached server

        $memcachedOptions = [
            // Better for multi server
            \Memcached::OPT_DISTRIBUTION    =>
                \Memcached::DISTRIBUTION_CONSISTENT,
            // Better for multi app use one memcached
            \Memcached::OPT_PREFIX_KEY  => 'fw',
        ];

        // @codeCoverageIgnoreStart
        // Use json is better for debug
        if (\Memcached::HAVE_JSON) {
            $memcachedOptions[\Memcached::OPT_SERIALIZER] =
                \Memcached::SERIALIZER_JSON;
        }
        // @codeCoverageIgnoreEnd

        // Default cache lifetime, 60s * 60m * 24h = 86400s(1d)
        $configs['memcachedLifetime'] = 86400;

        // Auto split store item larger than max item size
        // 0/off, 1/on, when off, large item store will fail.
        $configs['memcachedAutoSplit'] = 0;

        // Max item size, STRING val exceed this will auto split
        //   and store automatic, user need only care other val type.
        $configs['memcachedMaxItemSize'] = 1024000;

        // Memcached default option, set when new memcached obj
        $configs['memcachedOptionDefault'] = $memcachedOptions;

        // Memcached option, user set, replace default above
        $configs['memcachedOption'] = [];

        // After change server cfg, you should unset $oMemcached.
        // or use setConfigServer()
        $configs['memcachedServer'] = [];

        return $configs;
    }


    /**
     * Convert required key to actual key inner used
     *
     * Memcached limit key length 250, and no control char or whitespace.
     *
     * @param   string  $str
     * @return  string
     */
    protected function hashKey($str)
    {
        // Eliminate white space
        $str = preg_replace('/\s/m', '', $str);

        // Key can't be empty
        if (empty($str)) {
            $str = '[emptyKey]';
        }

        // Length limit
        $prefix1 = $this->getConfig(
            'memcachedOptionDefault.' . \Memcached::OPT_PREFIX_KEY
        );
        $prefix2 = $this->getConfig(
            'memcachedOption.' . \Memcached::OPT_PREFIX_KEY
        );
        $i = max(strlen($prefix1), strlen($prefix2));
        if (250 < ($i + strlen($str))) {
            $s = hash('crc32b', $str);
            $str = substr($str, 0, 250 - $i - strlen($s)) . $s;
        }

        return $str;
    }


    /**
     * Get memcached server connection
     *
     * @return  \Memcached
     */
    protected function getMemcached()
    {
        if (is_null($this->memcached)) {
            $serverList = $this->getValidMemcachedServer();
            $this->memcached = $this->connectMemcachedServer($serverList);
        }

        return $this->memcached;
    }


    /**
     * Read memcached server from config, test and return valid list
     *
     * @return  array
     */
    protected function getValidMemcachedServer()
    {
        $arSvr = $this->getConfig('memcachedServer');

        // Check server and remove dead
        foreach ((array)$arSvr as $k => $svr) {
            $obj = new \Memcached();
            $obj->addServers([$svr]);
            // Do set test
            $obj->set($this->hashKey('memcached server alive test'), true);

            // @codeCoverageIgnoreStart
            if (0 != $obj->getResultCode()) {
                // Got error server, log and remove it
                error_log(
                    'Memcache server ' . implode($svr, ':')
                    . ' test fail: ' . $obj->getResultCode()
                    . ', msg: ' . $obj->getResultMessage()
                );
                unset($arSvr[$k]);

            }
            // @codeCoverageIgnoreEnd

            unset($obj);
        }

        return $arSvr;
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

        $memcached = $this->getMemcached();

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
     * Write data to cache
     *
     * Lifetime is set when write.
     *
     * @param   string  $key
     * @param   mixed   $val
     * @param   int     $lifetime
     * @return  $this
     */
    public function set($key, $val, $lifetime = null)
    {
        $memcached = $this->getMemcached();

        // Convert expiration time
        $lifetime = $this->getExpireTime($lifetime);

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
                $lifetime
            );

            // Set split trunk
            for ($i = 1; $i <= $total; $i++) {
                $rs = $memcached->set(
                    $this->hashKey($key . '[split-' . $i . '/' . $total . ']'),
                    $ar[$i - 1],
                    $lifetime
                );
            }

        } else {
            // Normal set
            $rs = $memcached->set(
                $this->hashKey($key),
                $this->encodeValue($val),
                $lifetime
            );
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
     * Set cfg: memcached server
     *
     * @param   array   $arSvr      1 or 2 dim array of server(s)
     * @return  this
     */
    public function setConfigServer($arSvr = [])
    {
        if (empty($arSvr)) {
            return $this;
        }

        if (isset($arSvr[0]) && is_array($arSvr[0])) {
            // 2 dim array
            $this->setConfig('memcachedServer', $arSvr);
        } else {
            // 1 dim array only
            $this->setConfig('memcachedServer', [$arSvr]);
        }

        $this->memcached = null;

        return $this;
    }
}
