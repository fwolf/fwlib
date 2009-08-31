<?php
/**
* @package      fwolflib
* @subpackage	class
* @copyright    Copyright 2009, Fwolf
* @author       Fwolf <fwolf.aide+fwolflib.class@gmail.com>
*/

require_once('fwolflib/func/string.php');
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
	 * array(
	 * 	action 	=> url
	 * 	enctype	=> useful when upload file
	 * 	method	=> POST or GET
	 * 	name	=> same as id
	 * )
	 * @var	array
	 * @see	Reset()
	 * @see	SetConfig()
	 * @see	SetConfigEnctype()
	 */
	protected $aConfig = array();

	/**
	 * Form element define, raw order
	 *
	 * First value of attrib is DEFAULT value.
	 * array(
	 * 	name => array(
	 * 		name,
	 * 		type,
	 * 		label,
	 * 		value,
	 * 		attrib = array(
	 * 		),
	 * 	)
	 * )
	 * @var	array
	 * @see	$aElementAttribDefault
	 */
	protected $aElement = array();

	/**
	 * Default value of element attrib, use if not defined
	 * @var	array
	 */
	public $aElementAttribDefault = array(
		// For textarea only
		'cols'		=> null,
		// Additional html define ?
		'html-add'	=> '',
		// Will following element stay in same row ?
		'keep_div'	=> false,
		// Label is before input or after it ?
		'label-pos'	=> 'before',
		// For select, multiple means multi-select, value is size
		'multiple'	=> null,
		// Selection or value list, usually be array
		'option'	=> null,
		// For date input(My97DatePicker) only
		'param'		=> '',
		// For textarea only
		'rows'		=> null,
		// Spacer between mutli item, eg: radio
		'spacer'	=> '',
		// Only image has src attrib
		'src'		=> '',
	);

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
		$this->Reset();
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
		if ('file' == $type)
			$this->SetConfigEnctype(1);
	} // end of func AddElement


	/**
	 * Add element attribe define
	 * @param	string	$name
	 * @param	mixed	$key
	 * @param	mixed	$val
	 * @see		$aElement
	 */
	public function AddElementAttrib($name, $key, $val = null) {
		if (isset($this->aElement[$name])) {
			if (is_array($key)) {
				foreach ($key as $k => $v)
					$this->aElement[$name]['attrib'][$k] = $v;
			}
			else
				$this->aElement[$name]['attrib'][$key] = $val;
		}
	} // end of func AddElementAttrib


	/**
	 * Add element value attrib
	 *
	 * If $name is an array, it's a name/value array,
	 * or only assign $v to single element $name.
	 * @param	mixed	$name
	 * @param	mixed	$v
	 */
	public function AddElementValue($name, $v = null) {
		if (is_array($name)) {
			foreach ($name as $key => $val)
				$this->AddElementValue($key, $val);
		}
		else {
			if (!empty($v) && isset($this->aElement[$name]))
				$this->aElement[$name]['value'] = $v;
		}
	} // end of func AddElementValue


	/**
	 * Example of Generate task edit form
	 * @return	string
	 */
	protected function EgGenFormTaskEdit() {
		$this->oForm->Reset();

		$this->oForm->SetConfig(array(
			'name'	=> 'frm_task_edit',
		));

		$this->oForm->AddElement('text', 'title', '任务名称'
			, array('html-add' => 'style="color: red;"'));

		$this->oForm->AddElement('fieldset', 'fs_1', '第一组');
		$this->oForm->AddElement('checkbox', 'cb_1', '选项1'
			, array('checked' => true, 'option' => 1));
		$this->oForm->AddElement('checkbox', 'cb_2', '选项2'
			, array('checked' => false, 'label_align' => 'after'));
		$this->oForm->AddElementAttrib('cb_2', 'option', 2);
		$this->oForm->AddElement('file', 'fu_1', '上传文件');
		$this->oForm->AddElement('fieldset_end', 'fse_1', '第一组');

		$this->oForm->AddElement('hidden', 'hid_1', 'Hide');
		$this->oForm->AddElementValue('hid_1', "I'm hidden.");
		$this->oForm->AddElement('image', 'img_1', '图片？'
			, array('src' => 'http://www.acronymfinder.com/~/st/i/deli.gif'));
		$this->oForm->AddElement('password', 'pwd_1', '密码'
			, array('html-add' => 'style="color: red;"'));
		$this->oForm->AddElement('radio', 'rad_1', '只能选一个：');
		$this->oForm->AddElementAttrib('rad_1', array(
			'spacer' => '<br />',
			'label_align' => 'after',
		));
		$this->oForm->AddElementAttrib('rad_1', 'option', array(
			array('label' => '选择1', 'option' => '1'),
			array('label' => '选择2', 'option' => '2'),
		));
		$this->oForm->AddElement('select', 'sel_1', '选一个');
		$this->oForm->AddElementAttrib('sel_1', 'option', array(
			array('label' => '选择11', 'option' => '11'),
			array('label' => '选择22', 'option' => '22'),
			array('label' => '选择33', 'option' => '33'),
			array('label' => '选择44', 'option' => '44'),
		));
		$this->oForm->AddElementAttrib('sel_1', 'multiple', 8);
		$this->oForm->AddElementValue('sel_1', array(22, 44));
		$this->oForm->AddElement('select', 'sel_2', '再选一个');
		$this->oForm->AddElementAttrib('sel_2', 'option', array(
			array('label' => '选择11', 'option' => '11'),
			array('label' => '选择22', 'option' => '22'),
			array('label' => '选择33', 'option' => '33'),
			array('label' => '选择44', 'option' => '44'),
		));
		$this->oForm->AddElementValue('sel_2', 22);
		$this->oForm->AddElement('textarea', 'tx_2', '详细说明'
			, array('rows' => 4, 'cols' => 50));
		$this->oForm->AddElement('date_my97', 'date_1', '时间'
			, array('param' => 'lang:\'zh-cn\''));

		$this->oForm->AddElement('html', 'html_1', '<strong> or </strong>');
		$this->oForm->AddElement('xsubmit', 'frm_save', '保存');
		$this->oForm->AddElementAttrib('frm_save', 'keep_div', true);
		$this->oForm->AddElement('htmlraw', 'html_2', '<strong> OR </strong>');
		$this->oForm->AddElement('xreset', 'frm_reset', '重置');

		return $this->oForm->GetHtml();
	} // end of func EgGenFormTaskEdit


	/**
	 * Get html of an element
	 * @param	array	$v
	 * @return	string
	 * @see AddElement()
	 */
	public function GetElement($elt) {
		// Apply element's default value
		foreach ($this->aElementAttribDefault as $k => $v) {
			if (!isset($elt['attrib'][$k]) && !empty($v))
				$elt['attrib'][$k] = $v;
		}

		$s_html = '';

		if (isset($elt['attrib']['label_align'])
			&& ('after' == $elt['attrib']['label_align']))
			$s_div = 'fl_frm_elt_lr';
		else
			$s_div = 'fl_frm_elt_ll';

		if (false == $this->iFlagKeepDiv)
			$s_html .= '<div class="' . $s_div . '" id="fl_frm_elt_'
				. $elt['name'] . '">' . "\n";

		// :TODO: autocomplete
		switch ($elt['type']) {
			case 'button':
			case 'reset':
			case 'submit':
				$s_html .= $this->GetElementButton($elt);
				break;
			case 'xbutton':
			case 'xreset':
			case 'xsubmit':
				$s_html .= $this->GetElementButtonX($elt);
				break;
			case 'checkbox':
				$s_html .= $this->GetElementCheckbox($elt);
				break;
			case 'datetime':
			case 'date_my97':
				$s_html .= $this->GetElementDateMy97($elt);
				break;
			case 'fieldset':
				return $this->GetElementFieldset($elt);
				break;
			case 'fieldset_end':
				return $this->GetElementFieldsetEnd($elt);
				break;
			case 'hidden':
				// Do not need outer div, so use return directly.
				return $this->GetElementHidden($elt);
				break;
			case 'html':
				$s_html .= $elt['label'] . "\n";
				break;
			case 'htmlraw':
				return $elt['label'] . "\n";
				break;
			case 'image':
				$s_html .= $this->GetElementImage($elt);
				break;
			case 'radio':
				$s_html .= $this->GetElementRadio($elt);
				break;
			case 'select':
				$s_html .= $this->GetElementSelect($elt);
				break;
			case 'file':
			case 'password':
			case 'text':
				$s_html .= $this->GetElementText($elt);
				break;
			case 'textarea':
				$s_html .= $this->GetElementTextarea($elt);
				break;
		}

		if (isset($elt['attrib']['keep_div'])
			&& (true == $elt['attrib']['keep_div']))
			$this->iFlagKeepDiv = true;
		else
			$this->iFlagKeepDiv = false;
		if (false == $this->iFlagKeepDiv)
			$s_html .= '</div>' . "\n\n";

		return $s_html;
	} // end of func GetElement


	/**
	 * Get html of element input/submit button
	 * @param	array	$elt
	 * @return	string
	 * @see	AddElement()
	 */
	protected function GetElementButton($elt) {
		$s_html = $this->GetHtmlInput($elt);
		// Label set as value
		$s_html = str_replace('/>', 'value="' . $elt['label'] . '" />'
			, $s_html);
		return $s_html;
	} // end of func GetElementButton


	/**
	 * Get html of element checkbox
	 * @param	array	$elt
	 * @return	string
	 * @see AddElement()
	 */
	protected function GetElementCheckbox($elt) {
		$s_label = $this->GetHtmlLabel($elt);
		$s_input = $this->GetHtmlInput($elt);

		// Attrib option as value
		if (isset($elt['attrib']['option']))
			$s_input = str_replace('/>'
				, 'value="' . $elt['attrib']['option'] . '" />'
				, $s_input);

		// Checked ?
		if (!empty($elt['attrib']['checked']))
			$s_input = str_replace('/>'
				, 'checked="checked" />'
				, $s_input);

		if (isset($elt['attrib']['label_align'])
			&& ('after' == $elt['attrib']['label_align']))
			$s_html = $s_input . $s_label;
		else
			$s_html = $s_label . $s_input;

		return $s_html;
	} // end of func GetElementCheckbox


	/**
	 * Get html of element date, using My97DatePicker
	 *
	 * Must include WdatePicker.js in tpl manually:
	 * <script type="text/javascript" src="/js/DatePicker/WdatePicker.js"></script>
	 * @param	array	$elt
	 * @return	string
	 * @see AddElement()
	 */
	protected function GetElementDateMy97($elt) {
		$s_html = $this->GetElementText($elt);
		// Make type for input right(input)
		$s_html = str_replace('<input type="date_my97"'
			, '<input type="input"', $s_html);

		// Value is already set in GetElementHidden()
/*
		if (isset($elt['value']))
			$s_html = str_replace('/>'
				, ' value="' . $elt['value'] . '" />'
				, $s_html);
*/

		// Add My97DatePicker part
		if (isset($elt['attrib']['param']))
			$s_param = $elt['attrib']['param'];
		else
			$s_param = $this->aElementAttribDefault['param'];
		$s_html = str_replace('/>'
			, 'class="Wdate" onfocus="WdatePicker({' . $s_param . '})" />'
			, $s_html);

		return $s_html;
	} // end of func GetElementDateMy97


	/**
	 * Get html of element fieldset(begin)
	 *
	 * @param	array	$elt
	 * @return	string
	 * @see AddElement()
	 */
	protected function GetElementFieldset($elt) {
		$s_html = $this->GetHtmlInput($elt);
		$s_html = str_replace('<input type="fieldset"'
			, '<fieldset', $s_html);

		// Label as legend
		if (isset($elt['label']))
			$s_html = str_replace('/>'
				, '>' . "\n	" . '<legend>' . $elt['label']
					. '</legend>' . "\n"
				, $s_html);
		else
			$s_html = str_replace('/>', ">\n", $s_html);

		return $s_html;
	} // end of func GetElementFieldset


	/**
	 * Get html of element fieldset(end)
	 * @param	array	$elt
	 * @return	string
	 * @see AddElement()
	 */
	protected function GetElementFieldsetEnd($elt) {
		return "</fieldset>\n\n";
	} // end of func GetElementFieldsetEnd


	/**
	 * Get html of element hidden
	 * @param	array	$elt
	 * @return	string
	 * @see AddElement()
	 */
	protected function GetElementHidden($elt) {
		$s_html = $this->GetHtmlInput($elt);
		if (isset($elt['value']))
			$s_html = str_replace('/>'
				, ' value="' . $elt['value'] . '" />'
				, $s_html);
		return $s_html;
	} // end of func GetElementHidden


	/**
	 * Get html of element image
	 * @param	array	$elt
	 * @return	string
	 * @see AddElement()
	 */
	protected function GetElementImage($elt) {
		// No label
		$s_html = $this->GetElementHidden($elt);

		if (isset($elt['attrib']['src']))
			$s_html = str_replace('/>'
				, 'src="' . $elt['attrib']['src'] . '" />'
				, $s_html);

		// Label is alt text
		if (isset($elt['label']))
			$s_html = str_replace('/>'
				, 'alt="' . $elt['label'] . '" />'
				, $s_html);

		return $s_html;
	} // end of func GetElementImage


	/**
	 * Get html of element radio
	 * @param	array	$elt
	 * @return	string
	 * @see AddElement()
	 */
	protected function GetElementRadio($elt) {
		$s_html = '';
		$t = $elt;
		$s_spacer = (isset($elt['attrib']['spacer']))
			? $elt['attrib']['spacer'] : '';
		$i_id = 1;
		// Option is an array like array('label' => , 'option' =>)
		foreach ($elt['attrib']['option'] as $v) {
			// Use input go get label and input html.
			$t['label'] = $v['label'];
			$t['value'] = $v['option'];
			$s_t = $this->GetElementText($t) . $s_spacer;

			// Id can't be same, so rename them
			$s_t = str_replace('for="' . $elt['name'] . '"'
				, 'for="' . $elt['name'] . '-' . $i_id . '"', $s_t);
			$s_t = str_replace('id="' . $elt['name'] . '"'
				, 'id="' . $elt['name'] . '-' . $i_id . '"', $s_t);

			$i_id ++;
			$s_html .= $s_t;
		}

		return $s_html;
	} // end of func GetElementRadio


	/**
	 * Get html of element select
	 * @param	array	$elt
	 * @return	string
	 * @see AddElement()
	 */
	protected function GetElementSelect($elt) {
		// Div, label, and input html
		$s_html = $this->GetElementText($elt);
		// Input -> select
		$s_html = str_replace('<input', '<select', $s_html);
		if (empty($elt['attrib']['multiple'])) {
			$s_html = str_replace('/>', '>', $s_html);
		}
		else {
			$s_html = str_replace('/>', 'multiple="multiple" size="'
			. $elt['attrib']['multiple'] . '">', $s_html);
			// Set name to array format
			$s_html = str_replace('<select type="select" name="'
				. $elt['name'] . '"'
				, '<select type="select" name="'
				. $elt['name'] . '[]"'
				, $s_html);
		}

		// Options
		$s_option = '';
		// Option is an array like array('label' => , 'option' =>)
		foreach ($elt['attrib']['option'] as $v) {
			// <option value="volvo">Volvo</option>
			$s_t = 'value="' . $v['option']
				. '">' . $v['label'] . '</option>' . "\n";

			// Selected ?
			// Value can be array if multiple is set
			// Array - in_array, and = for string
			$b_selected = false;
			if (isset($elt['value'])) {
				if (is_array($elt['value'])
					&& in_array($v['option'], $elt['value']))
					$b_selected = true;
				elseif ($elt['value'] == $v['option'])
					$b_selected = true;
			}
			if ($b_selected)
				$s_t = '<option selected="selected" ' . $s_t;
			else
				$s_t = '<option ' . $s_t;

			$s_html .= $s_t;
		}

		$s_html .= "</select>\n";
		return $s_html;
	} // end of func GetElementSelect


	/**
	 * Get html of element common input/text
	 * @param	array	$elt
	 * @return	string
	 * @see AddElement()
	 */
	protected function GetElementText($elt) {
		$s_label = $this->GetHtmlLabel($elt);
		// Plus str without label
		$s_input = $this->GetElementHidden($elt);

		if (isset($elt['attrib']['label_align'])
			&& ('after' == $elt['attrib']['label_align']))
			$s_html = $s_input . $s_label;
		else
			$s_html = $s_label . $s_input;

		return $s_html;
	} // end of func GetElementText


	/**
	 * Get html of element textarea
	 * @param	array	$elt
	 * @return	string
	 * @see AddElement()
	 */
	protected function GetElementTextarea($elt) {
		$s_row_col = '';
		if (isset($elt['attrib']['rows']))
			$s_row_col .= 'rows="' . $elt['attrib']['rows'] . '" ';
		if (isset($elt['attrib']['cols']))
			$s_row_col .= 'cols="' . $elt['attrib']['cols'] . '" ';

		// Div, label, and input html
		$s_html = $this->GetElementText($elt);
		// Input -> select
		$s_html = str_replace('<input', '<textarea ' . $s_row_col, $s_html);
		$s_html = str_replace('/>', '>', $s_html);

		// Textarea value
		if (isset($elt['value']))
			$s_html .= HtmlEncode($elt['value']);

		$s_html .= "</textarea>\n";
		return $s_html;
	} // end of func GetElementTextarea


	/**
	 * Get html of element button, not the input type=button one
	 * @param	array	$elt
	 * @return	string
	 * @see	AddElement()
	 */
	protected function GetElementButtonX($elt) {
		// Remove leading 'x'
		$elt['type'] = substr($elt['type'], 1);

		$s_html = $this->GetHtmlInput($elt);
		$s_html = str_replace('<input', '<button', $s_html);
		// Can have value, alough useless
		if (isset($elt['value']))
			$s_html = str_replace('/>', 'value="' . $elt['value'] . '" />'
				, $s_html);
		// Label set bteween tags
		if (isset($elt['label']))
			$s_html = str_replace('/>', '>' . $elt['label'] . '</button>'
				, $s_html);
		return $s_html;
	} // end of func GetElementButtonX


	/**
	 * Get form html
	 * @return	string
	 */
	public function GetHtml() {
		$s_html = '';
		// Form style, for typeset only
		// ll = label left, lr = label right
		$s_html .= '
		<style type="text/css" media="screen, print">
		<!--
		#' . $this->aConfig['name'] . ' .fl_frm_elt_ll {
			clear: left;
			padding-top: 0.2em;
		}
		#' . $this->aConfig['name'] . ' .fl_frm_elt_ll label {
			float: left;
			text-align: right;
			margin-right: 0.3em;
			padding-top: 0.2em;
		}
		#' . $this->aConfig['name'] . ' .fl_frm_elt_lr {
			/*clear: right;*/
			padding-top: 0.2em;
		}
		#' . $this->aConfig['name'] . ' .fl_frm_elt_lr label {
			/*float: right;*/
			text-align: left;
			margin-left: 0.3em;
			padding-top: 0.2em;
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
		$s_html .= ' class="fl_frm"' . " >\n";

		// Form body
		foreach ($this->aElement as $v) {
			$s_html .= $this->GetElement($v);
		}

		// Form state, to validate form is posted, and security.
		// Hidden element, not need id property.
		$s_html .= '<input type="hidden" name="'
			. $this->aConfig['name'] . '_fs" />'
			. "\n";

		// Form footer
		$s_html .= "</form>\n";
		return $s_html;
	} // end of func GetHtml


	/**
	 * Get html of element's label part
	 * @param	array	$elt
	 * @return	string
	 * @see GetElement()
	 */
	protected function GetHtmlLabel($elt) {
		$s_label = '';
		if (!empty($elt['label'])) {
			$s_label .= '<label for="' . $elt['name'] . '">';
			$s_label .= $elt['label'] . '</label>' . "\n";
		}
		return $s_label;
	} // end of func GetHtmlLabel


	/**
	 * Get html of element's input part
	 * @param	array	$elt
	 * @return	string
	 * @see AddElement()
	 */
	protected function GetHtmlInput($elt) {
		$s_input = '';
		$s_input .= '<input ';
		$s_input .= 'type="' . $elt['type'] . '" ';
		$s_input .= 'name="' . $elt['name'] . '" ';
		$s_input .= 'id="' . $elt['name'] . '" ';
		if (isset($elt['attrib']['html-add'])
			&& (true == $elt['attrib']['html-add']))
			$s_input .= $elt['attrib']['html-add'];
		$s_input .= '/>' . "\n";

		return $s_input;
	} // end of func GetHtmlInput


	/**
	 * Reset all data to default, prepare to create a new form
	 */
	public function Reset() {
		$this->aConfig = array(
			'action'	=> '',
			'enctype'	=> '',
			'method'	=> 'POST',
			'name'		=> 'fl_frm',
		);
		$this->aElement = array();
	} // end of func Reset


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
