<?php
/**
* @package      fwolflib
* @subpackage	class
* @copyright    Copyright 2009-2010, Fwolf
* @author       Fwolf <fwolf.aide+fwolflib.class@gmail.com>
*/


require_once(dirname(__FILE__) . '/fwolflib.php');
require_once(FWOLFLIB . 'func/string.php');
require_once(FWOLFLIB . 'func/validate.php');


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
 * @copyright	Copyright 2009-2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2009-07-26
 */
class Form extends Fwolflib {
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
	 * @see	SetCfg()
	 * @see	SetCfgEnctype()
	 */
	public $aCfg = array();

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
			$this->SetCfgEnctype(1);
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
	 * @param	object	&$form
	 * @return	string
	 */
	protected function EgGenFormTaskEdit(&$form) {
		if (empty($form))
			$form = $this->oForm;
		$form->Reset();

		$form->SetCfg(array(
			'name'	=> 'frm_task_edit',
		));

		$form->AddElement('text', 'title', '任务名称'
			, array('html-add' => 'style="color: red;"'));

		$form->AddElement('fieldset', 'fs_1', '第一组');
		$form->AddElement('checkbox', 'cb_1', '选项1'
			, array('checked' => true, 'option' => 1));
		$form->AddElement('checkbox', 'cb_2', '选项2'
			, array('checked' => false, 'label_align' => 'after'));
		$form->AddElementAttrib('cb_2', 'option', 2);
		$form->AddElement('file', 'fu_1', '上传文件');
		$form->AddElement('fieldset_end', 'fse_1', '第一组');

		$form->AddElement('hidden', 'hid_1', 'Hide');
		$form->AddElementValue('hid_1', "I'm hidden.");
		$form->AddElement('image', 'img_1', '图片？'
			, array('src' => 'http://www.acronymfinder.com/~/st/i/deli.gif'));
		$form->AddElement('password', 'pwd_1', '密码'
			, array('html-add' => 'style="color: red;"'));
		$form->AddElement('radio', 'rad_1', '只能选一个：');
		$form->AddElementAttrib('rad_1', array(
			'spacer' => '<br />',
			'label_align' => 'after',
		));
		$form->AddElementAttrib('rad_1', 'option', array(
			array('label' => '选择1', 'option' => '1'),
			array('label' => '选择2', 'option' => '2'),
		));
		$form->AddElement('select', 'sel_1', '选一个');
		$form->AddElementAttrib('sel_1', 'option', array(
			array('label' => '选择11', 'option' => '11'),
			array('label' => '选择22', 'option' => '22'),
			array('label' => '选择33', 'option' => '33'),
			array('label' => '选择44', 'option' => '44'),
		));
		$form->AddElementAttrib('sel_1', 'multiple', 8);
		$form->AddElementValue('sel_1', array(22, 44));
		$form->AddElement('select', 'sel_2', '再选一个');
		$form->AddElementAttrib('sel_2', 'option', array(
			array('label' => '选择11', 'option' => '11'),
			array('label' => '选择22', 'option' => '22'),
			array('label' => '选择33', 'option' => '33'),
			array('label' => '选择44', 'option' => '44'),
		));
		$form->AddElementValue('sel_2', 22);
		$form->AddElement('textarea', 'tx_2', '详细说明'
			, array('rows' => 4, 'cols' => 50));
		$form->AddElement('date_my97', 'date_1', '时间'
			, array('param' => 'lang:\'zh-cn\''));

		$form->AddElement('html', 'html_1', '<strong> or </strong>');
		$form->AddElement('xsubmit', 'frm_save', '保存');
		$form->AddElementAttrib('frm_save', 'keep_div', true);
		$form->AddElement('htmlraw', 'html_2', '<strong> OR </strong>');
		$form->AddElement('xreset', 'frm_reset', '重置');

//		return $this->oForm->GetHtml();
		return $form;
	} // end of func EgGenFormTaskEdit


	/**
	 * Generate a form state, to validate this is not a
	 * 	fake form pretend by a hacker.
	 * Just like .NET's _viewstate
	 * @return	string
	 */
	protected function GenFormState() {
		$s = session_id() . $this->aCfg['name'];
		$s = sha1($s);
		$s = str_replace(array('a', 'e', 'i', 'o', 'u'), '', $s);
		return $s;
	} // end of func GenFormState


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
		#' . $this->aCfg['name'] . ' .fl_frm_elt_ll {
			clear: left;
			padding-top: 0.2em;
		}
		#' . $this->aCfg['name'] . ' .fl_frm_elt_ll label {
			float: left;
			text-align: right;
			margin-right: 0.3em;
			padding-top: 0.2em;
		}
		#' . $this->aCfg['name'] . ' .fl_frm_elt_lr {
			/*clear: right;*/
			padding-top: 0.2em;
		}
		#' . $this->aCfg['name'] . ' .fl_frm_elt_lr label {
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
		foreach ($this->aCfg as $k => $v) {
			if (!empty($v))
				$s_html .= $k . '="' . $v . '" ';
		}
		if (!empty($this->aCfg['name']))
			$s_html .= 'id="' . $this->aCfg['name'] . '" ';
		$s_html .= ' class="fl_frm"' . " >\n";

		// Form body
		foreach ($this->aElement as $v) {
			$s_html .= $this->GetElement($v);
		}

		// Form state, to validate form is posted, and security.
		// Hidden element, not need id property.
		$s_html .= '<input type="hidden" name="'
			. $this->aCfg['name'] . '_fs"'
			. ' value="' . $this->GenFormState() . '"/>'
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
	 * Check if it can get $_POST data from this form
	 * Also include a little security check.
	 *
	 * If form state generated diff with posted,
	 * 	probably form is not postd from original page,
	 * 	so we assume it is an attack, treat as not a valid POST.
	 * @return	boolean
	 * @see	GenFormState()
	 */
	public function IsPost() {
		$s = $this->aCfg['name'] . '_fs';
		if (isset($_POST[$s])
			&& ($_POST[$s] == $this->GenFormState()))
			return true;
		else
			return false;
	} // end of func IsPost


	/**
	 * Reset all data to default, prepare to create a new form
	 *
	 * @param	boolean		$b_init	Re-do init.
	 */
	public function Reset ($b_init = false) {
		$this->aCfg = array(
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
	 * @see	$aCfg
	 */
	public function SetCfg($c, $v = '') {
		if (is_array($c)) {
			if (!empty($c))
				foreach ($c as $idx => $val)
					$this->SetCfg($idx, $val);
		}
		else
			$this->aCfg[$c] = $v;
	} // end of func SetCfg


	/**
	 * Set configuration enctype
	 * @param	int	$type	0:application/x-www-form-urlencoded
	 * 						1:multipart/form-data
	 * 						other value will empty the setting
	 */
	public function SetCfgEnctype($type = 0) {
		if (0 == $type)
			$this->aCfg['enctype'] = 'application/x-www-form-urlencoded';
		else if (1 == $type)
			$this->aCfg['enctype'] = 'multipart/form-data';
		else
			$this->aCfg['enctype'] = '';
	} // end of func SetCfgEnctype
} // end of class Form
?>
