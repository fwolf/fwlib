<?php
require_once(dirname(__FILE__) . '/../fwolflib.php');


/**
 * Return value class
 *
 * @deprecated  Use Fwlib\Base\ReturnValue
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright © 2013, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2013-05-03
 */
class Rv extends Fwolflib {

	/**
	 * Return value info
	 *
	 * array(
	 * 	code,		// Normally 0=no error, c>0=info, c<0=error occur
	 * 	msg,
	 * 	data,
	 * )
	 *
	 * @var	array
	 */
	public $aInfo = array(
		'code'	=> 0,
		'msg'	=> null,
		'data'	=> null,
	);


	/**
	 * constructor
	 *
	 * @param	int		$i_code
	 * @param	string	$s_msg
	 * @param	mixed	$m_data
	 */
	public function __construct ($i_code = 0, $s_msg = null, $m_data = null) {
		parent::__construct();

		$this->aInfo = array(
			'code'	=> $i_code,
			'msg'	=> $s_msg,
			'data'	=> $m_data,
		);
	} // end of func __construct


	/**
	 * Get/set code
	 *
	 * @param	int		$i_code
	 * @param	boolean	$b_force		Force do value assign ignore null
	 * @return	int
	 */
	public function Code ($i_code = null, $b_force = false) {
		return $this->GetSetInfo('code', $i_code, $b_force);
	} // end of func Code


	/**
	 * Get/set data
	 *
	 * @param	mixed	$m_data
	 * @param	boolean	$b_force		Force do value assign ignore null
	 * @return	mixed
	 */
	public function Data ($m_data = null, $b_force = false) {
		return $this->GetSetInfo('data', $m_data, $b_force);
	} // end of func Data


	/**
	 * Is result means error ?
	 *
	 * @return	boolean
	 */
	public function Error () {
		return ($this->aInfo['code'] < 0);
	} // end of func Error


	/**
	 * Get error msg
	 *
	 * @return	string
	 */
	public function ErrorMsg () {
		return $this->aInfo['msg'];
	} // end of func ErrorMsg


	/**
	 * Get error no
	 *
	 * Do NOT do if error check.
	 *
	 * @return	int
	 */
	public function ErrorNo () {
		return $this->aInfo['code'];
	} // end of func ErrorNo


	/**
	 * Convert to array
	 *
	 * @return	array
	 */
	public function GetArray () {
		return $this->aInfo;
	} // end of func GetArray


	/**
	 * Get/set info array
	 *
	 * @param	string	$idx			Should be one of code/msg/data, but no check
	 * @param	mixed	$val
	 * @param	boolean	$b_force		Force do value assign ignore null
	 * @return	mixed
	 */
	protected function GetSetInfo ($idx, $val = null, $b_force = false) {
		if (!is_null($val) || ((is_null($val)) && $b_force))
			$this->aInfo[$idx] = $val;

		return $this->aInfo[$idx];
	} // end of func GetSetInfo


	/**
	 * Get/set msg
	 *
	 * @param	string	$s_msg
	 * @param	boolean	$b_force		Force do value assign ignore null
	 * @return	string
	 */
	public function Msg ($s_msg = null, $b_force = false) {
		return $this->GetSetInfo('msg', $s_msg, $b_force);
	} // end of func Msg


} // end of class Rv
?>
