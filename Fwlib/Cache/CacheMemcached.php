<?php
namespace Fwlib\Cache;

use Fwlib\Cache\Cache;

/**
 * Key-value cache system, data store in memcached
 *
 * @package     Fwlib\Cache
 * @copyright   Copyright 2012-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2012-11-13
 */
class CacheMemcached extends Cache
{
    /**
     * Memcache object
     *
     * @var object
     */
    public $memcached = null;


    /**
     * Constructor
     *
     * @param   array   $config
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        // Unset for auto new
        unset($this->memcached);
    }


    /**
     * {@inheritdoc}
     *
     * @param   string  $key
     * @return  CacheMemcached
     */
    public function delete($key)
    {
        if (1 == $this->config->get('memcachedAutosplit')) {
            // Is value splitted ?
            $total = $this->memcached->get($this->key($key . '[split]'));
            if (false === $total) {
                // No split found
                $this->memcached->delete($this->key($key));

            } else {
                // Splitted string
                for ($i = 1; $i <= $total; $i ++) {
                    $this->memcached->delete(
                        $this->key($key . '[split-' . $i . '/' . $total . ']')
                    );
                }
                $this->memcached->delete($this->key($key . '[split]'));
            }
        } else {
            $this->memcached->delete($this->key($key));
        }

        return $this;
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
    protected function isExpired($key, $lifetime = null)
    {
        // Lifetime is handle by memcached

        $val = $this->memcached->get($this->key($key));

        // Unknown item size, try again for autosplit
        if ((\Memcached::RES_SUCCESS != $this->memcached->getResultCode())
            && (1 == $this->config->get('memcachedAutosplit'))
        ) {
            $val = $this->memcached->get($this->key($key . '[split]'));
        }

        if (\Memcached::RES_SUCCESS == $this->memcached->getResultCode()) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * Read cache and return value
     *
     * Lifetime setted when write cache.
     * Return null when fail or expire.
     *
     * @param   string  $key
     * @param   int     $lifetime
     * @return  mixed
     */
    public function get($key, $lifetime = null)
    {
        // Lifetime is handle by memcached

        if (1 == $this->config->get('memcachedAutosplit')) {
            // Is value splitted ?
            $keySplitted = $this->key($key . '[split]');
            $total = $this->memcached->get($keySplitted);
            $this->log[] = array(
                'key'   => $keySplitted,
                'success'   => \Memcached::RES_SUCCESS
                    == $this->memcached->getResultCode(),
            );
            if (false === $total) {
                // No split found
                $val = $this->memcached->get($this->key($key));
                $this->log[] = array(
                    'key'   => $this->key($key),
                    'success'   => \Memcached::RES_SUCCESS
                        == $this->memcached->getResultCode(),
                );
            } else {
                // Splited string
                $val = '';
                for ($i = 1; $i <= $total; $i++) {
                    $keySplitted = $this->key(
                        $key . '[split-' . $i . '/' . $total . ']'
                    );
                    $val .= $this->memcached->get($keySplitted);
                    $this->log[] = array(
                        'key'   => $keySplitted,
                        'success'   => \Memcached::RES_SUCCESS
                            == $this->memcached->getResultCode(),
                    );
                }
                // Convert to string in JSON format
                $val = '"' . $val . '"';
            }

        } else {
            // Direct get
            $val = $this->memcached->get($this->key($key));
            $this->log[] = array(
                'key'   => $this->key($key),
                'success'   => \Memcached::RES_SUCCESS
                    == $this->memcached->getResultCode(),
            );
        }

        if (\Memcached::RES_SUCCESS == $this->memcached->getResultCode()) {
            return $this->decodeValue($val);
        } else {
            return null;
        }
    }


    /**
     * Gen cache key
     *
     * Memcached limit key length 250, and no control char or whitespace.
     *
     * @param   string  $str
     * @return  string
     */
    public function key($str)
    {
        // Eliminate white space
        $str = preg_replace('/\s/m', '', $str);

        // Key can't be empty
        if (empty($str)) {
            $str = '[empty.key]';
        }

        // Length limit
        $prefix1 = $this->config->get(
            'memcachedOptionDefault.' . \Memcached::OPT_PREFIX_KEY
        );
        $prefix2 = $this->config->get(
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
     * New memcached property
     *
     * @return  object
     */
    protected function newInstanceMemcached()
    {
        $arSvr = $this->config->get('memcachedServer');

        if (!empty($arSvr)) {
            // Check server and remove dead
            foreach ($arSvr as $k => $svr) {
                $obj = new \Memcached();
                $obj->addServers(array($svr));
                // Do set test
                $obj->set($this->key('memcached server alive test'), true);

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
        }

        $obj = new \Memcached();
        $obj->addServers($arSvr);

        foreach ((array)$this->config->get('memcachedOptionDefault') as
            $k => $v) {
            $obj->setOption($k, $v);
        }

        // @codeCoverageIgnoreStart
        // This config is always empty, because create() is static call, no
        // instance, can't set this option.
        foreach ((array)$this->config->get('memcachedOption') as
            $k => $v) {
            $obj->setOption($k, $v);
        }
        // @codeCoverageIgnoreEnd


        return $obj;
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
        // Convert expiration time
        $lifetime = $this->expireTime($lifetime);

        // Auto split large string val
        if ((1 == $this->config->get('memcachedAutosplit'))
            && is_string($val) && (strlen($val)
            > $this->config->get('memcachedMaxitemsize'))
        ) {
            $ar = str_split(
                $val,
                $this->config->get('memcachedMaxitemsize')
            );
            $total = count($ar);

            // Set split total
            $rs = $this->memcached->set(
                $this->Key($key . '[split]'),
                $total,
                $lifetime
            );

            // Set split trunk
            for ($i = 1; $i <= $total; $i++) {
                $rs = $this->memcached->set(
                    $this->key($key . '[split-' . $i . '/' . $total . ']'),
                    $ar[$i - 1],
                    $lifetime
                );
            }

        } else {
            // Normal set
            $rs = $this->memcached->set(
                $this->Key($key),
                $this->encodeValue($val),
                $lifetime
            );
        }

        if (false == $rs) {
            // @codeCoverageIgnoreStart

            trigger_error(
                'Memcache set error '
                . $this->memcached->getResultCode() . ': '
                . $this->memcached->getResultMessage(),
                E_USER_WARNING
            );

            // @codeCoverageIgnoreEnd
        }

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


        // Memcache server
        // Default cache lifetime, 60s * 60m * 24h = 86400s(1d)
        $this->config->set('memcachedLifetime', 86400);

        // Auto split store item larger than max item size
        // 0/off, 1/on, when off, large item store will fail.
        $this->config->set('memcachedAutosplit', 0);

        // Max item size, STRING val exceed this will auto split
        //   and store automatic, user need only care other val type.
        $this->config->set('memcachedMaxitemsize', 1024000);

        // Memcached default option, set when new memcached obj
        $this->config->set(
            'memcachedOptionDefault',
            array(
                // Better for multi server
                \Memcached::OPT_DISTRIBUTION =>
                    \Memcached::DISTRIBUTION_CONSISTENT,
                // Better for multi app use one memcached
                \Memcached::OPT_PREFIX_KEY   => 'fw',
                // Better for debug
                \Memcached::OPT_SERIALIZER   =>
                    \Memcached::SERIALIZER_JSON,
            )
        );

        // Memcached option, user set, replace default above
        $this->config->set(
            'memcachedOption',
            array()
        );

        // After change server cfg, you should unset $oMemcached.
        // or use setConfigServer()
        $this->config->set(
            'memcachedServer',
            array()
        );


        return $this;
    }


    /**
     * Set cfg: memcached server
     *
     * @param   array   $arSvr      1 or 2 dim array of server(s)
     * @return  this
     */
    public function setConfigServer($arSvr = array())
    {
        if (empty($arSvr)) {
            return $this;
        }

        if (isset($arSvr[0]) && is_array($arSvr[0])) {
            // 2 dim array
            $this->config->set('memcachedServer', $arSvr);
        } else {
            // 1 dim array only
            $this->config->set('memcachedServer', array($arSvr));
        }

        unset($this->memcached);

        return $this;
    }
}
