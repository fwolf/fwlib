<?php
require_once(dirname(__FILE__) . '/../fwolflib.php');
require_once(dirname(__FILE__) . '/../../func/array.php');
require_once(dirname(__FILE__) . '/../../func/string.php');


/**
 * Parent class for Key - value cache system
 *
 * Data store in various way, define in sub class, call use Create().
 * As factory This class is also subclass creator, so not abstract.
 *
 * Main method:
 * -	Key(), hash or use original key.
 * -	Set(), write cache data
 * -	Get(), read cache data
 * -	Del(), delete cache data
 *
 * @package		fwolflib
 * @subpackage	class.cache
 * @copyright	Copyright 2012, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class.cache@gmail.com>
 * @since		2012-09-14
 */
class Cache extends Fwolflib {

	/**
	 * Cache data for cache type ''
	 *
	 * @var	array
	 */
	protected $aCache = array();


	/**
	 * Constructor
	 *
	 * @param	array	$ar_cfg
	 */
	public function __construct ($ar_cfg = array()) {
		parent::__construct($ar_cfg);

		// Unset for auto new
	} // end of func __construct


	/**
	 * Factory create method
	 *
	 * @param	string	$type			Cache type
	 * @param	array	$ar_cfg
	 * @return	object
	 */
	public static function Create ($type = '', $ar_cfg = array()) {
		// Supported cache type
		if (!in_array($type, array('',
			'file',
			'memcached',
			))) {
			// $this is not allowed in static func
			//$this->Log('Cache type ' . $type . ' not supported.', 4);
			error_log('Cache type ' . $type . ' not supported.');
			return NULL;
		}


		// Include file, new obj
		$s_filename = 'cache'
			. (empty($type) ? '' : '-') . $type;
		$s_classname = StrUnderline2Ucfirst($s_filename, true);
		$s_filename .= '.php';

		require_once(dirname(__FILE__) . '/' . $s_filename);
		return (new $s_classname($ar_cfg));
	} // end of func Create


	/**
	 * Del cache data
	 *
	 * @param	string	$key
	 * @return	$this
	 */
	public function Del ($key) {
		unset($this->aCache[$key]);
		return $this;
	} // end of func Del


	/**
	 * Is cache data expire ?
	 *
	 * @param	string	$key
	 * @return	boolean
	 */
	public function Expire ($key) {
		// Inner var never expire,
		// Also, there is no good method to keep var set time.
		return false;
	} // end of func Expire


	/**
	 * Compute expiration time
	 *
	 * @param	int		$lifetime
	 * @param	int		$t_base			Base start time, 0 use time().
	 * @return	int						In unix time.
	 */
	public function ExpireTime ($lifetime = NULL, $t_base = 0) {
		// If not set, use config
		if (is_null($lifetime))
			$lifetime = $this->aCfg['cache-lifetime'];

		// 0 means never expire
		if (0 == $lifetime)
			return 0;

		if (0 == $t_base)
			$t_base = time();

		// If smaller than 30days
		if (2592000 >= $lifetime) {
			return $t_base + $lifetime;
		}

		// Larger than 30days, it's unix timestamp, ignore $t_base
		return $lifetime;
	} // end of func ExpireTime


	/**
	 * Load cache data
	 *
	 * @param	string	$key
	 * @param	int		$lifetime		Cache lifetime
	 * @return	mixed
	 */
	public function Get ($key, $lifetime = 0) {
		// Ignored lifetime
		return $this->ValDecode(
			ArrayRead($this->aCache, $this->Key($key))
			, 0);
	} // end of func Get


	/**
	 * Gen cache key
	 *
	 * In some cache system, key may need hash or computed.
	 *
	 * @param	string	$str
	 * @return	string
	 */
	public function Key ($str) {
		return $str;
	} // end of func Key


	/**
	 * Write data to cache
	 *
	 * @param	string	$key
	 * @param	mixed	$val
	 * @param	int		$lifetime
	 * @return	$this
	 */
	public function Set ($key, $val, $lifetime = 0) {
		// Lifetime is useless.
		$this->aCache[$this->Key($key)] = $this->ValEncode($val, 0);
		return $this;
	} // end of func Set


	/**
	 * Set default config
	 *
	 * @return	this
	 */
	protected function SetCfgDefault () {
		parent::SetCfgDefault();

		// Cache type: file, memcached
		// Empty means parent cache class.
		$this->aCfg['cache-type'] = '';

		// Cache store method
		// 0: Raw string or other value.
		//	User should determine the value DO suite cache type.
		// 1: Json, decode to array.
		// 2: Json, decode to object.
		$this->aCfg['cache-store-method'] = 0;

		// Default cache lifetime, in second
		// Can be overwrite by param when get/set.
		// Default/Max 30days:
		//   60sec * 60min = 3600s * 24h = 86400s * 30 = 2592000s
		// Larger than 30days, must assign unix time like memcached,
		//   which is number of seconds since 1970-1-1 as an integer.
		// 0 means forever.
		$this->aCfg['cache-lifetime'] = 2592000;

		return $this;
	} // end of func SetCfgDefault


	/**
	 * Decode val stored in cache
	 *
	 * Lifetime get/set various by cache type, assign in subclass
	 *
	 * @param	string	$str			Str read from cache
	 * @return	mixed
	 */
	public function ValDecode ($str) {
		if (1 == $this->aCfg['cache-store-method']) {
			// Json to array
			return json_decode($str, true);
		}
		elseif (2 == $this->aCfg['cache-store-method']) {
			// Json to object
			return json_decode($str, false);
		}
		else {
			// Cache store method = 0 or other, return raw.
			return $str;
		}
	} // end of func ValDecode


	/**
	 * Encode val to store in cache
	 *
	 * Lifetime get/set various by cache type, assign in subclass
	 *
	 * @param	mixed	$val
	 * @return	string
	 */
	public function ValEncode ($val) {
		if (1 == $this->aCfg['cache-store-method']
			|| 2 == $this->aCfg['cache-store-method']) {
			return JsonEncodeUnicode($val);
		}
		else {
			// Raw
			return $val;
		}
	} // end of func ValEncode


} // end of class Cache
?>
