<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-01-30
 */

if (0 <= version_compare(phpversion(), '5.3.0')) {
	//require_once(__DIR__ . '/fwolflib.php');
	require_once(__DIR__ . '/../func/env.php');
} else {
	//require_once(dirname(__FILE__) . '/fwolflib.php');
	require_once(dirname(__FILE__) . '/../func/env.php');
}

/**
 * Basic class of all.
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-01-30
 */
class Fwolflib {

	/**
	 * Log msg.
	 *
	 * array(
	 * 	i	=> array(
	 * 		level	=> 1/notice, 3/common, 5/imortant
	 * 			Can also be 2 or 4, even 1.5(not suggested),
	 * 			Default is 3.
	 * 		time	=>
	 * 		msg		=>
	 * 	)
	 * )
	 *
	 * @var	array
	 */
	public $aLog = array();


	/**
	 * Default time format
	 *
	 * @var	string
	 */
	public $sFormatTime = 'Y-m-d H:i:s';


	/**
	 * Record log msg.
	 *
	 * @param	string	$msg
	 * @param	int		$level
	 * @return	$this
	 */
	public function Log($msg, $level = 3) {
		$this->aLog[] = array(
			'level'	=> $level,
			'time'	=> date($this->sFormatTime),
			'msg'	=> $msg,
		);

		return $this;
	} // end of func Log


	/**
	 * Get all log msg.
	 *
	 * @param	$level	Only output log which's level >= $level
	 * @return	string
	 */
	public function LogGet($level = 3) {
		if (IsCli())
			$s_split = "\n";
		else
			$s_split = "<br />\n";

		$s = '';
		if (!empty($this->aLog))
			foreach ($this->aLog as $log) {
				if ($level <= $log['level'])
					$s .= "[${log['time']}] ${log['msg']}"
						. $s_split;
			}

		return $s;
	} // end of func LogGet


} // end of class Fwolflib
?>
