<?php
require_once(dirname(__FILE__) . '/cache.php');
require_once(FWOLFLIB . 'func/filesystem.php');


/**
 * Key - value cache system, data store in file.
 *
 * @package		fwolflib
 * @subpackage	class.cache
 * @copyright	Copyright 2010-2012, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class.cache@gmail.com>
 * @since		2010-01-07
 */
class CacheFile extends Cache {


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
	 * Check if cache is ready for use.
	 *
	 * @return	boolean
	 */
	public function ChkCfg () {
		$b_pass = true;

		if (empty($this->aCfg['cache-file-dir']))
			$b_pass = false;
		else {
			$s = $this->ChkCfgFileDir($this->aCfg['cache-file-dir']);
			if (!empty($s)) {
				$this->Log('Cache file cfg dir error: ' . $s, 4);
				$b_pass = false;
			}
		}

		if (empty($this->aCfg['cache-file-rule']))
			$b_pass = false;
		else {
			$s = $this->ChkCfgFileRule($this->aCfg['cache-file-rule']);
			if (!empty($s)) {
				$this->Log('Cache file cfg rule error: ' . $s, 4);
				$b_pass = false;
			}
		}

		return $b_pass;
	} // end of func ChkCfg


	/**
	 * Check config/cache store dir valid and writable
	 * If error, return error msg, else return empty str.
	 *
	 * @param	string	$dir
	 * @return	string
	 */
	public function ChkCfgFileDir ($dir) {
		$s = '';

		if (empty($dir))
			$s = "Cache dir {$dir} is not defined.";

		if (!file_exists($dir)) {
			if (false == mkdir($dir, 0755, true))
				$s = "Fail to create cache dir {$dir}.";
		}
		else {
			if (!is_writable($dir))
				$s = "Cache dir {$dir} is not writable.";
		}

		return $s;
	} // end of func ChkCfgFileDir


	/**
	 * Check cache rule exist and valid
	 * If error, return error msg, else return empty str.
	 *
	 * @param	string	$rule
	 * @return	string
	 */
	public function ChkCfgFileRule($rule) {
		if (2 > strlen($rule))
			return("Cache rule is not defined or too short.");

		if (0 != (strlen($rule) % 2))
			return("Cache rule {$this->aCfg['cache-file-rule']} may not right.");

		return '';
	} // end of func ChkCfgFileRule


	/**
	 * Is cache data expire ?
	 *
	 * File cache does not keep lifetime in cache,
	 * so it need a lifetime from outside,
	 * or use default lifetime config.
	 *
	 * @param	string	$key
	 * @param	int		$lifetime	Cache lifetime, in second.
	 * @return	boolean				True means it IS expired.
	 */
	public function Expire ($key, $lifetime = NULL) {
		$s_file = $this->FilePath($key);

		// File doesn't exist
		if (!file_exists($s_file))
			return true;

		if (0 == $lifetime)
			return false;

		// Check file expire time
		$t_expire = $this->ExpireTime($lifetime, filemtime($s_file));
		if (time() > $t_expire)
			return true;
		else
			return false;
	} // end of func Expire


	/**
	 * Compute path of a key's data file
	 *
	 * @param	string	$key
	 * @return	string
	 */
	public function FilePath ($key) {
		$s_path = $this->aCfg['cache-file-dir'];

		$ar_rule = str_split($this->aCfg['cache-file-rule'], 2);
		if (empty($ar_rule))
			return $s_path;

		foreach ($ar_rule as $rule)
			$s_path .= $this->FilePathSec($rule, $key) . '/';

		// Filename
		$s_path .= $this->FilePathFilename($key);

		return $s_path;
	} // end of func FilePath


	/**
	 * Compute name of a key's data file
	 *
	 * @param	string	$key
	 * @return	string
	 */
	protected function FilePathFilename($key) {
		return substr(md5($key), 0, 8);
	} // end of func FilePathFilename


	/**
	 * Compute path of a key by a single rule section
	 *
	 * @param	string	$rule
	 * @param	string	$key
	 * @return	string
	 * @see	$sCacheRule
	 */
	protected function FilePathSec($rule, $key) {
		$i_len = 2;

		if ($i_len > strlen($rule))
			return '';

		$i = intval($rule{1});
		if (1 == $rule{0}) {
			// md5 from start
			$i_start = $i_len * $i;
			$s_seed = md5($key);
		} elseif (2 == $rule{0}) {
			// md5 from end
			$i_start = -1 * $i_len * ($i + 1);
			$s_seed = md5($key);
		} elseif (3 == $rule{0}) {
			// raw from start
			$i_start = $i_len * $i;
			$s_seed = $key;
		} elseif (4 == $rule{0}) {
			// raw from end
			$i_start = -1 * $i_len * ($i + 1);
			$s_seed = $key;
		} elseif (5 == $rule{0}) {
			// crc32
			if (3 < $i)
				$i = $i % 3;
			$i_start = $i_len * $i;
			$s_seed = hash('crc32', $key);
		}
		return(substr($s_seed, $i_start, 2));
	} // end of func FilePathSec


	/**
	 * Read cache and return value
	 *
	 * File cache should check lifetime when get,
	 * return NULL when fail.
	 *
	 * @param	string	$key
	 * @param	int		$lifetime		Cache lifetime, 0/no check, NULL/cfg
	 * @return	mixed
	 */
	public function Get ($key, $lifetime = NULL) {
		if ($this->Expire($key, $lifetime)) {
			return NULL;
		}

		// Read from file and parse it.
		$s_file = $this->FilePath($key);
		$s_cache = file_get_contents($s_file);

		return $this->ValDecode($s_cache);
	} // end of func Get


	/**
	 * Init treatment
	 *
	 * @param	array	$ar_cfg
	 * @return	object
	 */
	public function Init () {
		parent::Init();

		$this->ChkCfg();

		return $this;
	} // end of func Init


	/**
	 * Write data to cache
	 *
	 * Lifetime check when get.
	 *
	 * @param	string	$key
	 * @param	mixed	$val
	 * @return	$this
	 */
	public function Set ($key, $val) {
		$s_file = $this->FilePath($key);
		$s_cache = $this->ValEncode($val);

		// Create each level dir if not exists
		$s_dir = DirName1($s_file);
		if (!file_exists($s_dir))
			mkdir($s_dir, 0755, true);

		// Finally write file
		file_put_contents($s_file, $s_cache, LOCK_EX);

		return $this;
	} // end of func Set


	/**
	 * Set default config
	 *
	 * @return	this
	 */
	protected function SetCfgDefault () {
		parent::SetCfgDefault();

		// Cache type: file
		$this->aCfg['cache-type'] = 'file';


		// Dir where data file store
		$this->aCfg['cache-file-dir'] = '/tmp/cache/';

		/**
		 * Cache file store rule
		 *
		 * Group by every 2-chars, their means:
		 * 10	first 2 char of md5 hash, 16 * 16 = 256
		 * 11	3-4 char of md5 hash
		 * 20	last 2 char of md5 hash
		 * 30	first 2 char of key
		 * 40	last 2 char of key
		 * 5n	crc32, n=0..3, 16 * 16 = 256
		 * Join these str with '/', got full path of cache file.
		 */
		$this->aCfg['cache-file-rule'] = '10';


		return $this;
	} // end of func SetCfgDefault


} // end of class CacheFile
?>
