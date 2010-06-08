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
 * - Make app read and write to server/remote db through this.
 * - When write to server/remote db and remote side not accessable,
 * cache it and can call re-write later.
 *
 *
 * When act as server, make config vars as less as possible,
 * db param can passed in through POST,
 * but crypt key must be assigned.
 *
 * When act as client, can connect to multi server,
 * each have difference config, job, crypt etc,
 * client comm with them one by one.
 *
 *
 * Roadmap:
 *
 * 1.4	Cache for write to remote db, and re-call them.
 * 1.3	Provide app read and write functional from/to remote db.
 * 1.2	Sync push to server.
 * 1.1	Auto call data convert func.
 * 1.0	[:TODO:] Sync pull from server.
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
	 * Default value of config
	 * array(
	 * 	url,
	 * 	db_client	=> array(
	 * 		type, host, user, pass, name, lang
	 * 	),
	 * 	db_server	=> array(
	 * 		type, host, user, pass, name, lang
	 * 	),
	 * pull		=> '' or array(),
	 * push		=> '' or array(),
	 * )
	 * @var	array
	 */
	public $aCfgDefault = array();

	/**
	 * Config of all server, empty val will fill by $aCfgDefault.
	 * @see	$aCfgDefault
	 * @var	array
	 */
	public $aCfgServer = array();

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

		// Server will auto start, manual start client
		if (empty($_POST))
			$this->StartClient();
	} // end of func __construct


	/**
	 * At server, conn to db before call action func.
	 *
	 * @param	array	$ar_req
	 * @return	array
	 */
	protected function CommReturn($ar_req) {
		// Db, use posted db config first.
		if (isset($ar_req['db_server']))
			$ar_db_prof = $ar_req['db_server'];
		else
			// Using default config
			// Notice, there is no server config array on the server.
			// so we only read default config.
			$ar_db_prof = $this->aCfgDefault['db_server'];
		$this->oDb = $this->NewObjDb($ar_db_prof);

		return parent::CommReturn($ar_req);
	} // end of func CommReturn


	/**
	 * Test db conn @ server side.
	 *
	 * @see		TestDb()
	 * @see		CommReturn()	Db is connected here.
	 * @param	array	$ar_req	Request msg array
	 * @return	array
	 */
	protected function CommReturnTestDb($ar_req = array()) {
		$ar = array();
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
	 * @param	array	$db_prof
	 * @return	object
	 * @see	$oDb
	 */
	protected function NewObjDb($db_prof) {
		$obj = new Adodb($db_prof);
		if (false == $obj->Connect()) {
			$this->Log('Db conn fail.', 5);
			return null;
		}
		else {
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
			$this->aCfgDefault = ArrayRead($ar_cfg, 'default', array());
			$this->aCfgServer = ArrayRead($ar_cfg, 'server', array());
		}
		return $this;
	} // end of func SetCfg


	/**
	 * Fill server config using default
	 *
	 * @return	$this
	 */
	protected function SetCfgServer() {
		// These keys in cfg default can assign to server.
		$ar_keys = array('url', 'db_client', 'db_server'
			, 'pull', 'push');

		if (!empty($this->aCfgServer)) {
			foreach ($this->aCfgServer as $server => &$cfg) {
				foreach ($ar_keys as $key) {
					if (empty($cfg[$key])
						&& !empty($this->aCfgDefault[$key]))
						$cfg[$key] = $this->aCfgDefault[$key];
				}
				if (empty($cfg)) {
					// Invalid server config
					$this->Log('Invalid server config: ' . $server, 4);
				}
			}
		}

		$this->Log('Got ' . count($this->aCfgServer)
			. ' server todo.', 1);

		return $this;
	} // end of func SetCfgServer


	/**
	 * Act as client
	 *
	 * @return	$this
	 */
	public function StartClient() {
		// Fill server config using default
		$this->SetCfgServer();

		if (empty($this->aCfgServer)) {
			$this->Log('Got no valid server config.', 5);
			return $this;
		}

		foreach ($this->aCfgServer as $server => $cfg) {
			$this->Log('Treat server "' . $server . '".', 3);
			$this->sUrlRemote = $cfg['url'];
			if (!empty($cfg['db_server']))
				$this->aMsgExtra['db_server'] = $cfg['db_server'];

			// Test curl connection
			if (0 != $this->CommSendTest()) {
				$this->Log('Error conn to server "' . $server
					. '" ', 4);
				return $this;
			}
			// Test db connection
			if (0 != $this->TestDb($cfg)) {
				$this->Log('Error when test db.', 5);
				return $this;
			}

			// Begin pull and push
		}

		return $this;
	} // end of func StartClient


	/**
	 * Test db connection
	 *
	 * @param	array	$cfg
	 * @return	int 0=ok, other=error.
	 */
	public function TestDb($cfg) {
		// Client db
		$this->oDb = $this->NewObjDb($cfg['db_client']);
		if (empty($this->oDb)) {
			$this->Log('Test client db conn fail.', 5);
			return 1;
		}
		$this->Log('Test local db conn ok.', 1);

		// Server db
		$ar = array('action' => 'test-db');
		$ar = $this->CommSend($ar);
		if (isset($ar['code'])) {
			if (0 == $ar['code']) {
				$this->Log('Test server db conn ok.', 1);
			} else {
				$this->Log('Test server db conn fail.', 5);
				return 2;
			}
		} else {
			$this->Log('Test server db conn fail, invalid response.', 5);
			return 3;
		}


		$this->Log('Db conn ok, both local and server.', 3);
		return 0;
	} // end of func TestDb


} // end of class SyncDbCurl
?>
