<?php
require_once(dirname(__FILE__) . '/../fwolflib.php');
require_once(dirname(__FILE__) . '/../../func/array.php');


/**
 * Parent class for Key - value cache system,
 * data store in various way.
 *
 * Main method:
 * -	Key(), hash or use original key.
 * -	Set(), write cache data
 * -	Get(), read cache data
 * -	Del(), delete cache data
 *
 * Parent class is also subclass creator, so not abstract.
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
		parent::__construct();

		// Unset for auto new
	} // end of func __construct


	/**
	 * Factory create method
	 *
	 * @param	string	$type		Cache type
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
	 * Load cache data
	 *
	 * @param	string	$key
	 * @return	mixed
	 */
	public function Get ($key) {
		return ArrayRead($this->aCache, $this->Key($key));
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
	 * @return	$this
	 */
	public function Set ($key, $val) {
		$this->aCache[$this->Key($key)] = $val;
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


		return $this;
	} // end of func SetCfgDefault


} // end of class Cache
?>
