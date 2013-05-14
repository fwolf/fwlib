<?php
require_once(dirname(__FILE__) . '/cache.php');


/**
 * Key - value cache system, data store in memcached.
 *
 * @package		fwolflib
 * @subpackage	class.cache
 * @copyright	Copyright 2012, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class.cache@gmail.com>
 * @since		2012-11-13
 */
class CacheMemcached extends Cache {

	/**
	 * Memcache object
	 *
	 * @var	object
	 */
	public $oMemcached = NULL;


	/**
	 * Constructor
	 *
	 * @param	array	$ar_cfg
	 */
	public function __construct ($ar_cfg = array()) {
		parent::__construct($ar_cfg);

		// Unset for auto new
		unset($this->oMemcached);
	} // end of func __construct


	/**
	 * Delete cache data
	 * @param	string	$key
	 * @return	$this
	 */
	public function Del ($key) {
		if (1 == $this->aCfg['cache-memcached-autosplit']) {
			// Is value splitted ?
			$i_total = $this->oMemcached->get($this->Key($key
				. '[split]'));
			if (false === $i_total) {
				// No split found
				$this->oMemcached->delete($this->Key($key));
			} else {
				// Splitted string
				for ($i = 1; $i <= $i_total; $i++)
					$this->oMemcached->delete($this->Key($key
						. '[split-' . $i . '/' . $i_total . ']'));
				$this->oMemcached->delete($this->Key($key . '[split]'));
			}
		}
		else {
			$this->oMemcached->delete($this->Key($key));
		}

		return $this;
	} // end of func Del


	/**
	 * Is cache data expire ?
	 *
	 * Memcached expire when get fail.
	 * Usually use Get and check NULL is enough.
	 *
	 * @param	string	$key
	 * @param	int		$lifetime
	 * @return	boolean					True means it IS expired.
	 */
	public function Expire ($key, $lifetime = NULL) {
		// Lifetime is handle by memcached

		$val = $this->oMemcached->get($this->Key($key));
		// Unknown item size, try twice
		if ((Memcached::RES_SUCCESS !=
			$this->oMemcached->getResultCode())
			&& (1 == $this->aCfg['cache-memcached-autosplit'])) {
			$val = $this->oMemcached->get($this->Key($key . '[split]'));
		}

		if (Memcached::RES_SUCCESS == $this->oMemcached->getResultCode())
			return false;
		else
			return true;
	} // end of func Expire


	/**
	 * Read cache and return value
	 *
	 * Lifetime setted when write cache.
	 * Return NULL when fail or expire.
	 *
	 * @param	string	$key
	 * @param	int		$lifetime
	 * @return	mixed
	 */
	public function Get ($key, $lifetime = NULL) {
		// Lifetime is handle by memcached

		if (1 == $this->aCfg['cache-memcached-autosplit']) {
			// Is value splitted ?
			$s_key = $this->Key($key . '[split]');
			$i_total = $this->oMemcached->get($s_key);
			parent::$aLogGet[] = array(
				'key'	=> $s_key,
				'success'	=> Memcached::RES_SUCCESS
					== $this->oMemcached->getResultCode(),
			);
			if (false === $i_total) {
				// No split found
				$val = $this->oMemcached->get($this->Key($key));
				parent::$aLogGet[] = array(
					'key'	=> $this->Key($key),
					'success'	=> Memcached::RES_SUCCESS
						== $this->oMemcached->getResultCode(),
				);
			} else {
				// Splited string
				$val = '';
				for ($i = 1; $i <= $i_total; $i++) {
					$s_key = $this->Key($key
						. '[split-' . $i . '/' . $i_total . ']');
					$val .= $this->oMemcached->get($s_key);
					parent::$aLogGet[] = array(
						'key'	=> $s_key,
						'success'	=> Memcached::RES_SUCCESS
							== $this->oMemcached->getResultCode(),
					);
				}
				// Convert to string in JSON format
				$val = '"' . $val . '"';
			}
		}
		else {
			// Direct get
			$val = $this->oMemcached->get($this->Key($key));
			parent::$aLogGet[] = array(
				'key'	=> $this->Key($key),
				'success'	=> Memcached::RES_SUCCESS
					== $this->oMemcached->getResultCode(),
			);
		}

		if (Memcached::RES_SUCCESS == $this->oMemcached->getResultCode())
			return $this->ValDecode($val);
		else
			return NULL;
	} // end of func Get


	/**
	 * Gen cache key
	 *
	 * Memcached limit key length 250, and no control char or whitespace.
	 *
	 * @param	string	$str
	 * @return	string
	 */
	public function Key ($str) {
		// Eliminate white space
		$str = preg_replace('/\s/m', '', $str);

		// Key can't be empty
		if (empty($str))
			$str = 'empty-key';

		// Length limit
		$i = strlen($this->aCfg['cache-memcached-option-default']
			[Memcached::OPT_PREFIX_KEY]);
		if (isset($this->aCfg['cache-memcached-option']
			[Memcached::OPT_PREFIX_KEY]))
			$i = max($i, strlen($this->aCfg['cache-memcached-option']
				[Memcached::OPT_PREFIX_KEY]));
		if (250 < ($i + strlen($str))) {
			$s = hash('crc32b', $str);
			$str = substr($str, 0, 250 - $i - strlen($s)) . $s;
		}

		return $str;
	} // end of func Key


	/**
	 * New memcached object
	 *
	 * @return	object
	 */
	public function NewObjMemcached () {
		if (!empty($this->aCfg['cache-memcached-server'])) {
			// Check server and remove dead
			foreach ($this->aCfg['cache-memcached-server'] as $k => $svr) {
				$obj = new Memcached();
				$obj->addServers(array($svr));
				// Do set test
				$obj->set($this->Key('memcached server alive test'), true);
				if (0 != $obj->getResultCode()) {
					// Got error server
					$this->Log('Memcache server ' . implode($svr, ':')
						. ' test fail: ' . $obj->getResultCode()
						. ', msg: ' . $obj->getResultMessage()
						, 4);
					unset($this->aCfg['cache-memcached-server'][$k]);
				}
				unset($obj);
			}
		}

		$obj = new Memcached();
		$obj->addServers($this->aCfg['cache-memcached-server']);

		if (!empty($this->aCfg['cache-memcached-option-default'])) {
			foreach ($this->aCfg['cache-memcached-option-default']
				as $k => $v)
				$obj->setOption($k, $v);
		}

		if (!empty($this->aCfg['cache-memcached-option'])) {
			foreach ($this->aCfg['cache-memcached-option'] as $k => $v)
				$obj->setOption($k, $v);
		}

/*
		// Server error ?
		// getStats() return false when at least 1 server is dead.
		if (!empty($this->aCfg['cache-memcached-server'])
			&& false == $obj->getStats()) {
			$this->Log('One or more server is dead, code: '
				. $obj->getResultCode(). ', msg: '
				. $obj->getResultMessage()
				, 4);
		}
*/

		return $obj;
	} // end of func NewObjMemcached


	/**
	 * Write data to cache
	 *
	 * Lifetime setted when write cache.
	 *
	 * @param	string	$key
	 * @param	mixed	$val
	 * @param	int		$lifetime
	 * @return	$this
	 */
	public function Set ($key, $val, $lifetime = NULL) {
		// Convert expiration time
		$lifetime = $this->ExpireTime($lifetime);

		// Auto split large string val
		if ((1 == $this->aCfg['cache-memcached-autosplit'])
			&& is_string($val) && (strlen($val)
			> $this->aCfg['cache-memcached-maxitemsize'])) {
			$ar = str_split($val
				, $this->aCfg['cache-memcached-maxitemsize']);
			$i_total = count($ar);

			// Set split total
			$rs = $this->oMemcached->set($this->Key($key . '[split]')
				, $i_total, $lifetime);

			// Set split trunk
			for ($i = 1; $i <= $i_total; $i++) {
				$rs = $this->oMemcached->set($this->Key($key
					. '[split-' . $i . '/' . $i_total . ']')
					, $ar[$i - 1], $lifetime);
			}
		}
		else {
			// Normal set
			$rs = $this->oMemcached->set($this->Key($key)
				, $this->ValEncode($val), $lifetime);
		}

		if (false == $rs) {
			$this->Log('Memcache set error '
				. $this->oMemcached->getResultCode() . ': '
				. $this->oMemcached->getResultMessage()
				, 4);
		}

		return $this;
	} // end of func Set


	/**
	 * Set default config
	 *
	 * @return	this
	 */
	protected function SetCfgDefault () {
		parent::SetCfgDefault();

		// Cache type: memcached
		$this->aCfg['cache-type'] = 'memcached';


		// Memcache server
		// Default cache lifetime, 60s * 60m * 24h = 86400s(1d)
		$this->aCfg['cache-memcached-lifetime'] = 86400;

		// Auto split store item larger than max item size
		// 0/off, 1/on, when off, large item store will fail.
		$this->aCfg['cache-memcached-autosplit'] = 0;

		// Max item size, STRING val exceed this will auto split
		//   and store automatic, user need only care other val type.
		$this->aCfg['cache-memcached-maxitemsize'] = 1024000;

		// Memcached default option, set when new memcached obj
		$this->aCfg['cache-memcached-option-default'] = array(
			// Better for multi server
			Memcached::OPT_DISTRIBUTION	=>
				Memcached::DISTRIBUTION_CONSISTENT,
			// Better for multi app use one memcached
			Memcached::OPT_PREFIX_KEY	=> 'fw',
			// Better for debug
			Memcached::OPT_SERIALIZER	=>
				Memcached::SERIALIZER_JSON,
		);

		// Memcached option, user set, replace default above
		$this->aCfg['cache-memcached-option'] = array(
		);

		// After change server cfg, you should unset $oMemcached.
		// or use SetCfgServer()
		$this->aCfg['cache-memcached-server'] = array();


		return $this;
	} // end of func SetCfgDefault


	/**
	 * Set cfg: memcached server
	 *
	 * @param	array	$ar_svr			1 or 2 dim array of server(s)
	 * @return	this
	 */
	public function SetCfgServer ($ar_svr = array()) {
		if (empty($ar_svr))
			return $this;

		if (isset($ar_svr[0]) && is_array($ar_svr[0])) {
			// 2 dim array
			$this->aCfg['cache-memcached-server'] = $ar_svr;
		}
		else {
			// 1 dim array only
			$this->aCfg['cache-memcached-server'] = array($ar_svr);
		}

		unset($this->oMemcached);
		return $this;
	} // end of func SetCfgServer


} // end of class CacheMemcached
?>
