<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-05-25
 */

if (0 <= version_compare(phpversion(), '5.3.0')) {
	require_once(__DIR__ . '/adodb.php');
	require_once(__DIR__ . '/curl-comm.php');
} else {
	require_once(dirname(__FILE__) . '/adodb.php');
	require_once(dirname(__FILE__) . '/curl-comm.php');
}

/**
 * Sync db data via curl communication.
 *
 * Db schema of both side should be same,
 * Each data table should have timestamp and PK column.
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-05-25
 */
class SyncDbCurl extends CurlComm {

	/**
	 * Db conn profile
	 * array(type, host, user, pass, name, lang)
	 * @var	array
	 */
	protected $aDbProf = array();

	/**
	 * Db conn obj
	 * @var	object
	 */
	protected $oDb = null;


	/**
	 * Constructor
	 *
	 * @param	array	$ar_cfg
	 */
	public function __construct($ar_cfg = array()) {
		parent::__construct($ar_cfg);

		// For auto new obj
		unset($this->oDb);
	} // end of func __construct


	/**
	 * New db conn obj
	 *
	 * @return	object
	 * @see	$oDb
	 */
	protected function NewObjDb() {
		$obj = new Adodb($this->aDbProf);
		if (false == $obj->Connect()) {
			$this->Log('Db conn fail: ' . $obj->sErrorMsg, 5);
			return null;
		} else {
			$this->Log('New obj db.', 1);
			return $obj;
		}
	} // end of func NewObjDb


	/**
	 * Read and set config
	 *
	 * @param	array	$ar_cfg
	 * @return	$this
	 */
	public function SetCfg($ar_cfg = array()) {
		parent::SetCfg($ar_cfg);
		if (!empty($ar_cfg)) {
			$this->aDbProf = ArrayRead($ar_cfg, 'db_prof', array());
		}
		return $this;
	} // end of func SetCfg


	/**
	 * Test db connection
	 *
	 * @return	int 0=ok, other=error.
	 */
	public function TestDb() {
		// Active auto obj new
		$this->oDb;
		if (empty($this->oDb)) {
			return 1;
		}
		$this->Log('Db conn ok', 1);
		return 0;
	} // end of func TestDb


} // end of class SyncDbCurl
?>
