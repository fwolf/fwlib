<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-05-19
 */

if (0 <= version_compare(phpversion(), '5.3.0')) {
	require_once(__DIR__ . '/curl.php');
} else {
	require_once(dirname(__FILE__) . '/curl.php');
}

/**
 * Commucate with server via http using Curl.
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-05-19
 */
class CurlComm extends Curl {

	/**
	 * Url of remote site
	 * @var	string
	 */
	public $sUrlRemote = '';


	/**
	 * Constructor
	 *
	 * @param	array	$ar_cfg
	 */
	public function __construct($ar_cfg = array()) {
		// For auto-call of func NewObjXXX()
		//unset($this->oCurl);
		parent::__construct();

		$this->Log('Begin', 3);
		if (!empty($ar_cfg))
			$this->SetCfg($ar_cfg);
	} // end of func __construct


	/**
	 * Destructor
	 */
	public function __destruct() {
		// Useless, dummy item, log will not be recorded.
		$this->Log('End', 3);

		parent::__destruct();
	} // end of func __destruct


	/**
	 * Read and set config
	 *
	 * @param	array	$ar_cfg
	 * @return	$this
	 */
	public function SetCfg($ar_cfg = array()) {
		if (empty($ar_cfg))
			return $this;

		$this->sUrlRemote = ArrayRead($ar_cfg, 'url_remote', '');
	} // end of func SetCfg


	/**
	 * Test remote url readable
	 */
	public function TestRemote() {
		$this->Log('Client: Say hello to server.', 1);
		$s = $this->Post($this->sUrlRemote
			, array('action' => 'hello'));
		$this->Log('Server: http code ' . $this->GetLastCode(), 1);
		$this->Log('Server: ' . $s, 1);
	} // end of func TestRemote


} // end of class CurlComm
?>
