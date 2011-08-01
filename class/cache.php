<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010-2011, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-01-07
 */


require_once(dirname(__FILE__) . '/fwolflib.php');
require_once(FWOLFLIB . 'func/filesystem.php');


/**
 * Key - value like cache system, data store in filesystem.
 *
 * Key is split by '/', just like URL.
 *
 * Workflow:
 * -	CacheKey(), hash user request key.
 * -	CacheGet()
 * 		-	Try read, CacheGetType()
 * 		-	If fail, gen cache, CacheGen(), CacheSet()
 * -	CacheSet()
 * -	CacheDel()
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010-2011, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-01-07
 */
abstract class Cache extends Fwolflib {


	/**
	 * Compute path of a key's data file
	 *
	 * @param	string	$key
	 * @return	string
	 */
	public function CacheFilePath($key) {
		$s_path = $this->aCfg['cache-file-dir'];

		$ar_rule = str_split($this->aCfg['cache-file-rule'], 2);
		if (empty($ar_rule))
			return $s_path;

		foreach ($ar_rule as $rule)
			$s_path .= $this->CacheFilePathSec($rule, $key) . '/';

		// Filename
		$s_path .= $this->CacheFilePathFile($key);

		return $s_path;
	} // end of func Path


	/**
	 * Compute name of a key's data file
	 *
	 * @param	string	$key
	 * @return	string
	 */
	protected function CacheFilePathFile($key) {
		return substr(md5($key), 0, 8);
	} // end of func CacheFilePathFile


	/**
	 * Compute path of a key by a single rule section
	 *
	 * @param	string	$rule
	 * @param	string	$key
	 * @return	string
	 * @see	$sCacheRule
	 */
	protected function CacheFilePathSec($rule, $key) {
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
	} // end of func CacheFilePathSec


	/**
	 * Gen and write cache data file
	 *
	 * @param	string	$key
	 * @return	mixed		// Generated cache data.
	 */
	protected function CacheGen($key) {
		$val = $this->CacheGenVal($key);
		$this->CacheSet($key, $val);
		return $val;
	} // end of func CacheGen


	/**
	 * Gen cache data, implement by child class
	 *
	 * @param	string	$key
	 * @return	string
	 */
	abstract protected function CacheGenVal($key);


	/**
	 * Load cache data
	 *
	 * @param	string	$key
	 * @param	int		$flag	May used by type func.
	 * @return	mixed
	 */
	public function CacheGet($key, $flag) {
		// Error check
		if (empty($this->aCfg['cache-type'])) {
			$this->Log('Cache type is not set.', 5);
			return NULL;
		}

		$s = 'CacheGet' . ucfirst($this->aCfg['cache-type']);
		if (method_exists($this, $s))
			return $this->{$s}($key, $flag);
		else {
			$this->Log('Cache get method for type '
				. $this->aCfg['cache-type'] . ' not implement.', 5);
			return NULL;
		}
	} // end of func CacheGet


	/**
	 * Read cache file and return value
	 *
	 * @param	string	$key
	 * @param	int		$flag	Which type value shoud I return ?
	 * 							0=string, 1=array, 2=object
	 * 							3=raw string
	 * @return	mixed
	 */
	protected function CacheGetFile($key, $flag = 0) {
		if ($this->CacheNeedUpdate($key))
			return $this->CacheGen($key);
		else {
			// Read from file and parse it.
			$s_file = $this->CacheFilePath($key);
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
				case 3:
				default:
					$rs = &$s_cache;
			}
			return $rs;
		}
	} // end of func CacheGetFile


	/**
	 * CacheLifetime of cache data, meature by second
	 *
	 * @param	string	$key
	 * @return	int
	 */
	abstract public function CacheLifetime($key);


	/**
	 * Is cache data file need update/create ?
	 *
	 * @param	string	$key
	 * @return	boolean
	 */
	protected function CacheNeedUpdate($key) {
		$s_file = $this->CacheFilePath($key);

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
	 * Write data to cache
	 *
	 * @param	string	$key
	 * @param	mixed	$val
	 * @return	$this
	 */
	public function CacheSet ($key, $val) {
		// Error check
		if (empty($this->aCfg['cache-type'])) {
			$this->Log('Cache type is not set.', 5);
			return this;
		}

		$s = 'CacheSet' . ucfirst($this->aCfg['cache-type']);
		if (method_exists($this, $s)) {
			$this->{$s}($key, $val);
		}
		else {
			$this->Log('Cache set method for type '
				. $this->aCfg['cache-type'] . ' not implement.', 5);
		}
		return $this;
	} // end of func CacheSet


	/**
	 * Write data to cache, type file
	 *
	 * @param	string	$key
	 * @param	mixed	$val
	 * @return	$this
	 */
	public function CacheSetFile ($key, $val) {
		$s_file = $this->CacheFilePath($key);
		$s_cache = json_encode($val);

		// Create each level dir if not exists
		$s_dir = DirName1($s_file);
		if (!file_exists($s_dir))
			mkdir($s_dir, 0755, true);

		// Finally write file
		file_put_contents($s_file, $s_cache, LOCK_EX);
		return $this;
	} // end of func CacheSetFile


	/**
	 * Check config/cache store dir valid and writable
	 * If error, return error msg, else return empty str.
	 *
	 * @param	string	$dir
	 * @return	string
	 */
	public function ChkCfgFileDir($dir) {
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

		if (!empty($s))
			$this->Log($s, 5);
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
	 * Init config vars
	 *
	 * @return	object
	 */
	protected function Init () {
		parent::Init();

		// Cache type: file, memcached
		$this->aCfg['cache-type'] = '';

		// Type file: dir where data file store
		$this->aCfg['cache-file-dir'] = '';
		/**
		 * Type file: cache file store rule
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
		$this->aCfg['cache-file-rule'] = '';

		return $this;
	} // end of func Init


	/**
	 * Set config
	 *
	 * @param	array	$ar_cfg
	 */
	public function SetCfg($ar_cfg) {
		parent::SetCfg($ar_cfg);

		// Check config
		if (!empty($this->aCfg['cache-file-dir'])) {
			$s = $this->ChkCfgFileDir($this->aCfg['cache-file-dir']);
			if (!empty($s))
				die($s);
		}
		if (!empty($this->aCfg['cache-file-rule'])) {
			$s = $this->ChkCfgFileRule($this->aCfg['cache-file-rule']);
			if (!empty($s))
				die($s);
		}
	} // end of func SetCfg


} // end of class Cache

?>
