<?php
/**
* @package      fwolflib
* @subpackage	class
* @copyright    Copyright 2009, Fwolf
* @author       Fwolf <fwolf.aide+fwolflib.class@gmail.com>
*/

require_once('fwolflib/func/validate.php');

/**
 * Form operate class
 *
 * Generate html of form,
 * plus operate like validate, data recieve etc.
 *
 * Reference:
 * <http://pear.php.net/package/HTML_QuickForm/docs>
 * Form format:
 * <http://www.52css.com/article.asp?id=238>
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2009-07-26
 * @version		$Id$
 */
class Form
{
	/**
	 * Configuration
	 * @var	array
	 */
	protected $aConfig = array(
		'action'	=> '',
		// enctype default 'application/x-www-form-urlencoded'
		'enctype'	=> '',
		// Default id=name, so only define one
		//'id'		=> 'fwolflib_form',
		'method'	=> 'POST',
		'name'		=> 'fwolflib_form',
	);

	/**
	 * Form element define, raw order
	 *
	 * array(
	 * 	name => array(
	 * 		type
	 * 		label
	 * 		attrib = array()
	 * 	)
	 * )
	 * @var	array
	 */
	protected $aElement = array();

	/**
	 * Flag control <div> generate when doing element
	 * 0 not setuped
	 * 1 cur element will not end <div>
	 * can be recursive.
	 * @var	boolean
	 */
	protected $iFlagKeepDiv = false;


	/**
	 * contruct
	 */
	public function __construct() {

	} // end of func __construct


	/**
	 * Add an element define
	 * @param	string	$type
	 * @param	string	$name	Must not be empty or duplicate
	 * @param	string	$label
	 * @param	array	$attrib	Additional html attributes.
	 * @see		$aElement
	 */
	public function AddElement($type, $name, $label = '', $attrib = array()) {
		$this->aElement[$name] = array(
			'name'		=> $name,
			'type'		=> $type,
			'label'		=> $label,
			'attrib'	=> $attrib,
		);
	} // end of func AddElement


	/**
	 * Add config to element define
	 * @param	string	$name
	 * @param	string	$key
	 * @param	mixed	$val
	 * @see		$aElement
	 */
	public function AddElementConfig($name, $key, $val = null) {
		if (isset($this->aElement[$name]))
			$this->aElement[$name][$key] = $val;
	} // end of func AddElementConfig


	/**
	 * Get html of an element
	 * @param	array
	 * @return	string
	 * @see AddElement()
	 */
	public function GetElement($v) {
		$s_html = '';

		if (false == $this->iFlagKeepDiv)
			$s_html .= '<div class="fwolflib_form_input_div">' . "\n";

		$ar_input = array(
			'checkbox',
			'file',
			'hidden',
			'image',
			'password',
			'radio',
			'reset',
			'submit',
			'text',
		);
		if (in_array($v['type'], $ar_input))
			$s_html .= $this->GetElementInput($v);

		$ar_button = array(
			'button',
			'submit',
		);
		if (in_array($v['type'], $ar_button))
			$s_html .= $this->GetElementButton($v);

		if (array_key_exists('keep_div', $v))
			$this->iFlagKeepDiv = true;
		else
			$this->iFlagKeepDiv = false;
		if (false == $this->iFlagKeepDiv)
			$s_html .= '</div>' . "\n";

		return $s_html;
	} // end of func GetElement


	/**
	 * Get html of element input/submit button
	 * @param	array
	 * @return	string
	 * @see	AddElement()
	 */
	protected function GetElementButton($v) {
		$s_html = '';
		$s_html .= '<input ';
		$s_html .= 'type="' . $v['type'] . '" ';
		$s_html .= 'name="' . $v['name'] . '" ';
		$s_html .= 'id="' . $v['name'] . '" ';
		$s_html .= 'value="' . $v['label'] . '" ';
		if (!empty($v['attrib'])) {
			foreach ($v['attrib'] as $k => $v)
				$s_html .= $k . '="' . $v . '" ';
		}
		$s_html .= '/>' . "\n";
		return $s_html;
	} // end of func GetElementButton


	/**
	 * Get html of element input
	 * @param	array
	 * @return	string
	 * @see AddElement()
	 */
	protected function GetElementInput($v) {
		$s_html = '';
		if (!empty($v['label'])) {
			$s_html .= '<label for="' . $v['name'] . '">';
			$s_html .= $v['label'] . '</label>' . "\n";
		}
		$s_html .= '<input ';
		$s_html .= 'type="' . $v['type'] . '" ';
		$s_html .= 'name="' . $v['name'] . '" ';
		$s_html .= 'id="' . $v['name'] . '" ';
		if (!empty($v['attrib'])) {
			foreach ($v['attrib'] as $k => $v)
				$s_html .= $k . '="' . $v . '" ';
		}
		$s_html .= '/>' . "\n";
		return $s_html;
	} // end of func GetElementInput


	/**
	 * Get form html
	 * @return	string
	 */
	public function GetHtml() {
		$s_html = '';
		// Form style, for typeset only
		$s_html .= '
		<style type="text/css" media="screen, print">
		<!--
		#' . $this->aConfig['name'] . ' label {
			float: left;
			text-align: right;
			margin-right: 0.3em;
			padding-top: 0.2em;
		}
		#' . $this->aConfig['name'] . ' .fwolflib_form_input_div {
			clear: left;
			margin-top: 0.2em;
		}
		-->
		</style>
		';

		// Form head
		$s_html .= '<form ';
		foreach ($this->aConfig as $k => $v) {
			if (!empty($v))
				$s_html .= $k . '="' . $v . '" ';
		}
		if (!empty($this->aConfig['name']))
			$s_html .= 'id="' . $this->aConfig['name'] . '" ';
		$s_html .= " >\n";

		// Form body
		foreach ($this->aElement as $v) {
			$s_html .= $this->GetElement($v);
		}

		// Form footer
		$s_html .= "</form>\n";
		return $s_html;
	} // end of func GetHtml


	/**
	 * Set configuration
	 * @param	array|string	$c	Config array or name/value pair.
	 * @param	string			$v	Config value
	 * @see	$aConfig
	 */
	public function SetConfig($c, $v = '') {
		if (is_array($c)) {
			if (!empty($c))
				foreach ($c as $idx => $val)
					$this->SetConfig($idx, $val);
		}
		else
			$this->aConfig[$c] = $v;
	} // end of func SetConfig


	/**
	 * Set configuration enctype
	 * @param	int	$type	0:application/x-www-form-urlencoded
	 * 						1:multipart/form-data
	 * 						other value will empty the setting
	 */
	public function SetConfigEnctype($type = 0) {
		if (0 == $type)
			$this->aConfig['enctype'] = 'application/x-www-form-urlencoded';
		else if (1 == $type)
			$this->aConfig['enctype'] = 'multipart/form-data';
		else
			$this->aConfig['enctype'] = '';
	} // end of func SetConfigEnctype
} // end of class Form
?>
