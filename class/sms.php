<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright © 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-11-23
 */


require_once(dirname(__FILE__) . '/fwolflib.php');
require_once(FWOLFLIB . 'func/uuid.php');


/**
 * SMS treat and send.
 *
 * Using gammu daemon to send sms.
 *
 * @deprecated  Use Fwlib\Net\Sms\SmsSender
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright © 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-11-23
 */
class Sms extends Fwolflib {

	/**
	 * Db object to connect sms stat db
	 * @var	object
	 */
	protected $oDb = null;


	/**
	 * construct
	 *
	 * @param	object	$o_db
	 */
	public function __construct ($o_db = null) {
		parent::__construct();

		if (!is_null($o_db))
			$this->oDb = &$o_db;
	} // end of class __construct


	/**
	 * Count sms will split to N part to send
	 *
	 * If only ascii chars include, 140 chars for 1 sms part,
	 * if has chinese chars, 70 chars for 1 sms part only.
	 *
	 * 1 chinese char will count as 1 char.
	 *
	 * @param	string	$s_sms
	 * @return	integer
	 */
	public function CountPart ($s_sms = '') {
		// Is there chinese in sms ?
		if (mb_strlen($s_sms, 'utf-8') == strlen($s_sms)) {
			// No chinese, 140 chars per part
			return (ceil(strlen($s_sms) / 140));
		}
		else {
			// Convert chinese char to ascii, for count
			return (ceil(mb_strlen($s_sms, 'utf-8') / 70));
		}
	} // end of func CountPart


	/**
	 * Parse dest/phone number string.
	 *
	 * Do:
	 * 	Split phone number.
	 * 	Format phone number.
	 * 	Remove duplicate number.
	 *
	 * @param	mixed	$s_dest
	 * @return	array
	 */
	public function DestParse ($s_dest) {
		// If array given, still need convert to string
		// to format and validate phone number.
		if (is_array($s_dest))
			$s_dest = implode(',', $s_dest);

		// Replace all special chars to ','
		$s_dest = str_replace('，', ',', $s_dest);
		$s_dest = preg_replace('/[ ,;\r\n\t]{1,}/'
			, ',', $s_dest);
		$ar_dest = explode(',', $s_dest);

		// Remove +86, 0086
		foreach ($ar_dest as &$dest) {
			if ('+86' == substr($dest, 0, 3))
				$dest = substr($dest, 3);
			elseif ('0086' == substr($dest, 0, 4))
				$dest = substr($dest, 4);
		}

		// Remove duplicate
		$ar = array();
		foreach ($ar_dest as &$dest) {
			// Invalid length
			if (11 != strlen($dest) && '10' != substr($dest, 0, 2))
				continue;

			if (false == in_array($dest, $ar))
				$ar[] = $dest;
		}

		return $ar;
	} // end of func DestParse


	/**
	 * Write sent sms stat information.
	 *
	 * @param	array	$ar_dest
	 * @param	string	$s_sms
	 * @param	integer	$i_cat
	 */
	protected function DestStatSet ($ar_dest, $s_sms, $i_cat) {
		if (is_null($this->oDb)) {
			$this->Log('No db connection.', 5);
			return;
		}

		// Gen data array
		$ar_data = array();
		$ar_data['uuid']	= Uuid();
		$ar_data['st']		= date('Y-m-d H:i:s');
		$ar_data['cat']		= $i_cat;
		$ar_data['cnt']		= count($ar_dest);
		$ar_data['cnt_cm']	= 0;
		$ar_data['cnt_cu']	= 0;
		$ar_data['cnt_ct']	= 0;
		$ar_data['dest']	= implode(',', $ar_dest);
		$ar_data['cnt_part']= $this->CountPart($s_sms);
		$ar_data['sms']		= $s_sms;

		// Which company's number ?
		$ar_cm = array(134, 135, 136, 137, 138, 139, 147
			, 150, 151, 152, 157, 158, 159, 187, 188);
		$ar_cu = array(130, 131, 132, 155, 156, 185, 186);
		$ar_ct = array(133, 153, 180, 189);
		foreach ($ar_dest as $dest) {
			$i = intval(substr($dest, 0, 3));
			if (in_array($i, $ar_cm))
				$ar_data['cnt_cm'] ++;
			elseif (in_array($i, $ar_cu))
				$ar_data['cnt_cu'] ++;
			elseif (in_array($i, $ar_ct))
				$ar_data['cnt_ct'] ++;
		}

		// Save to db
		$this->oDb->Write('sms_stat', $ar_data, 'I');
	} // end of func DestStatSet


	/**
	 * Detect and set path of gammu smsd inject cmd
	 *
	 * @param	$s_path		Manual additional path
	 * @return	string
	 */
	public function GetPathGammuSmsdInject ($s_path = '') {
		$ar_path = $this->aCfg['path_bin'];

		if (!empty($s_path)) {
			// Add to array
			array_unshift($ar_path, $s_path);
		}

		// Find a usable path
		$b_found = false;
		while (!$b_found && !empty($ar_path)) {
			$s_cmd = $ar_path[0] . 'gammu-smsd-inject';
			if (is_executable($s_cmd)) {
				$b_found = true;
				break;
			}
			array_shift($ar_path);
		}
		if ($b_found) {
			$this->Log('Got gammu smsd inject execute file: '
				. $s_cmd, 1);
			$this->aCfg['path_gammu_smsd_inject'] = $s_cmd;
		}
		else {
			$this->Log('Can\' find gammu smsd inject execute file.', 5);
			exit();
		}

		return $this->aCfg['path_gammu_smsd_inject'];
	} // end of func GetPathGammuSmsdInject


	/**
	 * Init config vars, give default value.
	 *
	 * @return	this
	 */
	public function Init () {
		parent::Init();

		// Possible bin path
		$this->aCfg['path_bin'] = array(
			'/usr/bin/',
			'/usr/local/bin/',
			'/bin/',
		);

		// Path of gammu-smsd-inject
		$this->aCfg['path_gammu_smsd_inject'] = '';

		// Cmd template of gammu-smsd-inject cmd
		$this->aCfg['cmd_gammu_smsd_inject']
			= '[cmd] TEXT [dest] -autolen 600 -report -validity MAX -unicode -textutf8 "[sms]"';

		return $this;
	} // end of func Init


	/**
	 * Send sms using gammu smsd inject method.
	 *
	 * Notice: On webserver, need assign www-data to gammu group,
	 * 	and make /var/log/gammu-smsd.log g+w .
	 * 	Modem server need not, only conn to db is required.
	 *
	 * @param	mixed	$ar_dest	Dest phone number array
	 * 								or string split by ' ,;，\r\n'.
	 * @param	string	$s_sms		Msg to send.
	 * @param	integer	$i_cat		Category of sms, for stat.
	 * @return	integer				Actual valid phone number sent.
	 */
	public function SendUsingGammuSmsdInject ($ar_dest, $s_sms, $i_cat = 0) {
		if (empty($this->aCfg['path_gammu_smsd_inject']))
			$this->GetPathGammuSmsdInject();

		$ar_dest = $this->DestParse($ar_dest);
		if (1 > count($ar_dest)) {
			$this->Log('No valid number to sent.', 4);
			return 0;
		}
		$this->DestStatSet($ar_dest, $s_sms, $i_cat);

		// Prepare sms to sent
		$s_sms = str_replace(array('[cmd]', '[sms]')
			, array($this->aCfg['path_gammu_smsd_inject'], $s_sms)
			, $this->aCfg['cmd_gammu_smsd_inject']);
		$i = strpos($s_sms, '[dest]');
		if (1 > $i) {
			$this->Log('Something wrong with gammu smsd inject cmd template'
				, 5);
			exit();
		}
		$s_sms1 = substr($s_sms, 0, $i);
		$s_sms2 = substr($s_sms, $i + 6);	// 6 is length of '[dest]'

		// Loop to sent
		foreach ($ar_dest as $dest) {
			$s_cmd = $s_sms1 . $dest . $s_sms2;
			//exec($s_cmd);
			$ar_output = array();
			$i_return = 0;
			exec($s_cmd, $ar_output, $i_return);
			if (0 != $i_return) {
				// Error occur
				$this->Log('Gammu inject error: ' . $ar_output[1], 5);
				return 0;
			}
		}

		return count($ar_dest);
	} // end of func SendUsingGammuSmsdInject


} // end of class Sms


/*
--
-- stat table
--
CREATE TABLE sms_stat (
	uuid				CHAR(36)			NOT NULL,
	-- Sent time
	st					DATETIME			NOT NULL,
	-- Cat of msg
	cat					INTEGER				NOT NULL DEFAULT 0,
	-- Total dest number count
	cnt					INTEGER				NOT NULL DEFAULT 0,
	-- Count of China Mobile
	cnt_cm				INTEGER				NOT NULL DEFAULT 0,
	-- Count of China Unicom
	cnt_cu				INTEGER				NOT NULL DEFAULT 0,
	-- Count of China Telecom
	cnt_ct				INTEGER				NOT NULL DEFAULT 0,
	-- Dest phone numbers
	dest				TEXT				NOT NULL,
	-- Will sms split to N part to send
	cnt_part			INTEGER				NOT NULL DEFAULT 0,
	-- Msg
	sms					TEXT				NOT NULL,
	ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (uuid),
	INDEX idx_sms_stat_1 (st)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
*/
?>
