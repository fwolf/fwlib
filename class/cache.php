<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-01-07
 */

require_once('fwolflib/func/filesystem.php');

/**
 * Key - value like cache system, data store in filesystem.
 *
 * Key is split by '/', just like URL.
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-01-07
 */
abstract class Cache{

	/**
	 * Dir where data file store
	 * @var	string
	 */
	public $sCacheDir = '';


	/**
	 * Cache file store rule
	 *
	 * Group by every 2-chars, their means:
	 * 10	first 2 char of md5 hash, 36 * 36 = 1296
	 * 11	3-4 char of md5 hash
	 * 20	last 2 char of md5 hash
	 * 30	first 2 char of key
	 * 40	last 2 char of key
	 *
	 * @var	string
	 */
	public $sCacheRule = '';


	/**
	 * Constructor
	 *
	 * @param	array	$ar_cfg
	 */
	public function __construct($ar_cfg = array()) {
		$this->CacheSetCfg($ar_cfg);
	} // end of func __construct


	/**
	 * Check cache data store dir valid and writable
	 * If error, return error msg, else return empty str.
	 *
	 * @param	string	$dir
	 * @return	string
	 */
	public function CacheCheckDir($dir) {
		if (empty($dir))
			return("Cache dir {$dir} is not defined.");

		if (!is_writable($dir))
			return("Cache dir {$dir} is not writable.");

		return '';
	} // end of func CacheCheckDir


	/**
	 * Check cache rule exist and valid
	 * If error, return error msg, else return empty str.
	 *
	 * @param	string	$rule
	 * @return	string
	 */
	public function CacheCheckRule($rule) {
		if (2 > strlen($rule))
			return("Cache rule is not defined or too short.");

		if (0 != (strlen($rule) % 2))
			return("Cache rule {$this->sCacheRule} may not right.");

		return '';
	} // end of func CacheCheckRule


	/**
	 * Gen and write cache data file
	 *
	 * @param	string	$key
	 */
	protected function CacheGen($key) {
		$s_cache = $this->CacheGenVal($key);
		$this->CacheWrite($key, $s_cache);
	} // end of func Gen


	/**
	 * Gen cache data file content
	 *
	 * @param	string	$key
	 * @return	string
	 */
	abstract protected function CacheGenVal($key);


	/**
	 * CacheLifetime of cache data file, meature by second
	 *
	 * @param	string	$key
	 * @return	int
	 */
	abstract public function CacheLifetime($key);


	/**
	 * Load cache data
	 *
	 * @param	string	$key
	 * @param	int		$flag	@see CacheRead()
	 * @return	mixed
	 */
	public function CacheLoad($key, $flag) {
		if ($this->CacheNeedUpdate($key))
			$this->CacheGen($key);

		return $this->CacheRead($key, $flag);
	} // end of func Load


	/**
	 * Is cache data file need update/create ?
	 *
	 * @param	string	$key
	 * @return	boolean
	 */
	protected function CacheNeedUpdate($key) {
		$s_file = $this->CachePath($key);

		// File doesn't exist
		if (!file_exists($s_file))
			return true;

		// Out of CacheLifetime
		if ($this->CacheLifetime($key)
			> (time() - filemtime($s_file)))
			return false;
		else
			return true;
	} // end of func CacheNeedUpdate


	/**
	 * Compute path of a key's data file
	 *
	 * @param	string	$key
	 * @return	string
	 */
	public function CachePath($key) {
		$s_path = $this->sCacheDir;

		$ar_rule = str_split($this->sCacheRule, 2);
		if (empty($ar_rule))
			return $s_path;

		foreach ($ar_rule as $rule)
			$s_path .= $this->CachePathSec($rule, $key) . '/';

		// Filename
		$s_path .= $this->CachePathFile($key);

		return $s_path;
	} // end of func Path


	/**
	 * Compute name of a key's data file
	 *
	 * @param	string	$key
	 * @return	string
	 */
	protected function CachePathFile($key) {
		return substr(md5($key), 0, 8);
	} // end of func CachePathFile


	/**
	 * Compute path of a key by a single rule section
	 *
	 * @param	string	$rule
	 * @param	string	$key
	 * @return	string
	 * @see	$sCacheRule
	 */
	protected function CachePathSec($rule, $key) {
		$i_len = 2;

		if ($i_len > strlen($rule))
			return '';

		$i = intval($rule{1});
		if (1 == $rule{0}) {
			$i_start = $i_len * $i;
			$s_seed = md5($key);
		} elseif (2 == $rule{0}) {
			$i_start = -1 * $i_len * ($i + 1);
			$s_seed = md5($key);
		} elseif (3 == $rule{0}) {
			$i_start = $i_len * $i;
			$s_seed = $key;
		} elseif (4 == $rule{0}) {
			$i_start = -1 * $i_len * ($i + 1);
			$s_seed = $key;
		}
		return(substr($s_seed, $i_start, 2));
	} // end of func CachePathSec


	/**
	 * Read cache file and return value
	 *
	 * @param	string	$key
	 * @param	int		$flag	Which type value shoud I return ?
	 * 							0=string, 1=array, 2=object
	 * @return	mixed
	 */
	protected function CacheRead($key, $flag = 0) {
		$s_file = $this->CachePath($key);
		$s_cache = file_get_contents($s_file);

		$rs = null;
		switch ($flag) {
			case 0:
				$rs = json_decode($s_cache, true);
				if (is_array($rs))
					$rs = $rs[0];
				break;
			case 1:
				$rs = json_decode($s_cache, true);
				break;
			case 2:
				$rs = json_decode($s_cache, false);
				break;
			default:
				$rs = &$s_cache;
		}

		return $rs;
	} // end of func Read


	/**
	 * Set config
	 *
	 * @param	array	$ar_cfg
	 */
	public function CacheSetCfg($ar_cfg) {
		if (empty($ar_cfg))
			return;

		if (isset($ar_cfg['dir'])) {
			$s = $this->CacheCheckDir($ar_cfg['dir']);
			if (empty($s))
				$this->sCacheDir = $ar_cfg['dir'];
			else
				die($s);
		}
		if (isset($ar_cfg['rule'])) {
			$s = $this->CacheCheckRule($ar_cfg['rule']);
			if (empty($s))
				$this->sCacheRule = $ar_cfg['rule'];
			else
				die($s);
		}
	} // end of func SetCfg


	/**
	 * Write data to cache file
	 *
	 * @param	string	$key
	 * @param	mixed	$val
	 */
	public function CacheWrite($key, $val) {
		$s_file = $this->CachePath($key);
		$s_cache = json_encode($val);

		// Create each level dir if not exists
		$s_dir = DirName1($s_file);
		if (!file_exists($s_dir))
			mkdir($s_dir, 0755, true);

		// Finally write file
		file_put_contents($s_file, $s_cache, LOCK_EX);
	} // end of func Write

} // end of class Cache

?>