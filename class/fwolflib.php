<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-01-30
 */


require_once(dirname(__FILE__) . '/../fwolflib.php');
require_once(FWOLFLIB . 'func/env.php');


/**
 * Basic class of all.
 *
 * Log included.
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-01-30
 */
class Fwolflib {

	/**
	 * Configuation
	 *
	 * Notice: re-define var in sub-class will overwrite var in parent class.
	 * @var	array
	 */
	public $aConfig = array();

	/**
	 * Default time format
	 * @var	string
	 */
	public $sFormatTime = 'Y-m-d H:i:s';

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
	 * Dummy constructor
	 */
	public function __construct() {
	} // end of func __construct


	/**
	 * Dummy destructor
	 */
	public function __destruct() {
	} // end of func __destruct


	/**
	 * Auto new obj if not set, for some special var only
	 *
	 * @param	string	$name
	 * @return	object
	 */
	public function __get($name) {
		if ('o' == $name{0}) {
			$s_func = 'NewObj' . substr($name, 1);
			if (method_exists($this, $s_func)) {
				// New object
				$this->$name = $this->$s_func();
				return $this->$name;
			}
		}

		return null;
	} // end of func __get


	/**
	 * Record log msg.
	 *
	 * @param	string	$msg
	 * @param	int		$level
	 * @return	$this
	 */
	public function Log ($msg, $level = 3) {
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


	/**
	 * Set config array
	 *
	 * @see		$aConfig
	 * @param	array	$k
	 * @param	mixed	$v
	 */
	public function SetConfig ($k, $v = null) {
		if (is_array($k)) {
			// Use array $k only, ignore $v
			if (!empty($k)) {
				foreach ($k as $key => $val)
					$this->aConfig[$key] = $val;
			}
		}
		else {
			// Use array $k => $v
			$this->aConfig[$k] = $v;
		}
	} // end of func SetConfig


} // end of class Fwolflib
?>
