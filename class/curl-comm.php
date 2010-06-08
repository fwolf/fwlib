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
	require_once(__DIR__ . '/../func/crypt.php');
	require_once(__DIR__ . '/../func/ecl.php');
	require_once(__DIR__ . '/../func/string.php');
} else {
	require_once(dirname(__FILE__) . '/curl.php');
	require_once(dirname(__FILE__) . '/../func/crypt.php');
	require_once(dirname(__FILE__) . '/../func/ecl.php');
	require_once(dirname(__FILE__) . '/../func/string.php');
}

/**
 * Commucate with server via http using Curl.
 *
 * Msg commucated is json to string then encrypted.
 *
 * Msg send/post format(encrypted json string):
 * array(
 * 	action
 * 	msg		Various action may have diff type msg, array or string.
 * )
 *
 * Msg return format(after decrypted):
 * array(
 * 	code
 * 	msg
 * 		If code=0, no error, msg is data array.
 * 		If code<>0, error, msg is string error msg.
 * )
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-05-19
 */
class CurlComm extends Curl {

	/**
	 * Extra msg will be added when comm
	 * Notice: Avoid conflict with other msg.
	 * @var	array
	 */
	public $aMsgExtra = array();

	/**
	 * Algorithm of crypt
	 * @var	string
	 */
	public $sCryptAlgo = 'blowfish';

	/**
	 * Key of crypt
	 * @var	string
	 */
	public $sCryptKey = '';

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

		if (!empty($_POST) && isset($_POST['msg'])) {
			// Act as server
			$ar = $this->CommReceive();
		}
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
	 * Server receive msg from client, call treat func
	 * and return msg to client.
	 */
	protected function CommReceive() {
		// Init result array
		$rs = array(
			'code'	=> 0,
			'msg'	=> '',
		);

		if (empty($_POST) || empty($_POST['msg'])) {
			$rs['code'] = 1;
			$rs['msg'] = 'Empty input msg.';
		} else {
			$ar_request = $this->MsgDecrypt($_POST['msg']);
			// Check input msg format
			if (empty($ar_request['action'])) {
				$rs['code'] = 2;
				$rs['msg'] = 'Empty action.';
			} else {
				$rs = $this->CommReturn($ar_request);
			}
		}

		// Response to client
		echo $this->MsgEncrypt($rs);
	} // end of func CommReceive


	/**
	 * Call action func, return result
	 *
	 * @param	array	$ar_request
	 * @return	array
	 */
	protected function CommReturn($ar_request) {
		$s = 'CommReturn'
			. StrUnderline2Ucfirst($ar_request['action'], true);
		if (method_exists($this, $s)) {
			if (empty($ar_request['msg']))
				return $this->$s();
			else
				return $this->$s($ar_request['msg']);
		} else {
			$rs = array();
			$rs['code'] = 3;
			$rs['msg'] = 'Action "'	. $ar_request['action']
				. '" is not implemented.';
			return $rs;
		}
	} // end of func CommReturn


	/**
	 * Return hello msg to CommSendTest
	 *
	 * @see		CommSendTest()
	 * @return	array
	 */
	protected function CommReturnHello() {
		return array(
			'code'	=> 0,
			'msg'	=> json_encode(array(
				'math 1 + 1 = 2',
				'people 1 + 1 > 2',
				)),
			);
	} // end of func CommReturnHello


	/**
	 * Send msg to server, got result
	 *
	 * @param	array	$msg
	 * @return	array
	 */
	public function CommSend($msg) {
		// Adding msg extra
		if (!empty($this->aMsgExtra))
			$msg = array_merge($msg, $this->aMsgExtra);

		$s = $this->MsgEncrypt($msg);
		$s = $this->Post($this->sUrlRemote, array('msg' => $s));
		// Decrypt result
		if (!empty($s))
			$ar = $this->MsgDecrypt($s);
		else
			$ar = array();
		return $ar;
	} // end of func CommSend


	/**
	 * Send signal to server to test remote url readable
	 *
	 * @return	int	0/ok, other error.
	 */
	public function CommSendTest() {
		$this->Log('Say hello to server.', 1);
		$ar = array('action' => 'hello');
		$this->Log('Sending: ' . json_encode($ar), 1);
		$ar = $this->CommSend($ar);
		$this->Log('Server http code: '
			. $this->GetLastCode() . ', raw msg length '
			. strlen($this->mRs), 1);
		//$this->Log('Server raw msg: ' . $this->mRs);
		//$this->Log('Server raw msg decrypted: ' . var_export($ar, true));
		if (isset($ar['code'])) {
			$this->Log('Server code: ' . $ar['code'], 1);
			$this->Log('Server msg: ' . $ar['msg'], 1);
			$this->Log('Comm send test ok.', 3);
			return 0;
		} else {
			$this->Log('No valid server return msg.', 1);
			$this->Log('Comm send test fail.', 5);
			return 1;
		}
	} // end of func CommSendTest


	/**
	 * Decrypt msg, include json treat
	 *
	 * @param	string	$msg
	 * @return	string
	 */
	protected function MsgDecrypt($msg) {
		$s = McryptSmplIvDecrypt($msg, $this->sCryptKey, $this->sCryptAlgo);
		$ar = json_decode($s, true);
		return $ar;
	} // end of func MsgDecrypt


	/**
	 * Encrypt msg, include json treat
	 *
	 * @param	array	$ar_msg	Array
	 * @return	string
	 */
	protected function MsgEncrypt($ar_msg) {
		$s = json_encode($ar_msg);
		$s = McryptSmplIvEncrypt($s, $this->sCryptKey, $this->sCryptAlgo);
		return $s;
	} // end of func MsgEncrypt


	/**
	 * Read and set config
	 *
	 * @param	array	$ar_cfg
	 * @return	$this
	 */
	public function SetCfg($ar_cfg = array()) {
		if (!empty($ar_cfg)) {
			$this->sCryptAlgo = ArrayRead($ar_cfg, 'crypt_algo', $this->sCryptAlgo);
			$this->sCryptKey = ArrayRead($ar_cfg, 'crypt_key', '');
			$this->sUrlRemote = ArrayRead($ar_cfg, 'url_remote', '');
		}
		return $this;
	} // end of func SetCfg


} // end of class CurlComm
?>
