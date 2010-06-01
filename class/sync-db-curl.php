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
 *
 * If sync in two-way, that need server ID and write ID to every row,
 * which is 'functional' but not fast and need change many things
 * on app side, like readonly option.
 *
 * So I think make this a one-way sync tools, but it can do pull also
 * push, that is, for 1 table, it's one-way, but for the whole db,
 * it's somehow 'two-way' sync.
 *
 * Also I have some additional thought:
 * - Make app read and write to remote db through this.
 * - When write to remote db and remote side not accessable,
 * cache it and can call re-write later.
 *
 *
 * Roadmap:
 *
 * 0.4	Cache for write to remote db, and re-call them.
 * 0.3	Provide app read and write functional from/to remote db.
 * 0.2	Sync push to remote.
 * 0.11	Auto call data convert func.
 * 0.1	[:TODO:] Sync pull from remote.
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-05-25
 * @version		0.0
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
		// For auto new obj
		unset($this->oDb);

		parent::__construct($ar_cfg);
	} // end of func __construct


	/**
	 * Test db conn @ server side.
	 *
	 * @see		TestDb()
	 * @return	array
	 */
	protected function CommReturnTestDb() {
		$ar = array();

		// Active auto obj new
		$this->oDb;

		if (empty($this->oDb)) {
			$ar['code'] = 1;
			$ar['msg'] = 'Test server db conn fail.';
		} else {
			$ar['code'] = 0;
			$ar['msg'] = 'Test server db conn ok.';
		}
		return $ar;
	} // end of func CommReturnTestDb


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

		// Local db
		if (empty($this->oDb)) {
			$this->Log('Test local db conn fail.', 5);
			return 1;
		}
		$this->Log('Test local db conn ok.', 1);

		// Remote db
		$ar = array('action' => 'test-db');
		$ar = $this->CommSend($ar);
		if (isset($ar['code'])) {
			if (0 == $ar['code']) {
				$this->Log('Test remote db conn ok.', 1);
			} else {
				$this->Log('Test remote db conn fail, msg: '
					. $ar['msg'], 5);
				return 2;
			}
		} else {
			$this->Log('Test remote db conn fail, invalid response.', 5);
			return 3;
		}


		$this->Log('Db conn ok, both local and remote.', 3);
		return 0;
	} // end of func TestDb


} // end of class SyncDbCurl
?>
