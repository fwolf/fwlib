<?php
require_once(dirname(__FILE__) . '/../fwolflib.php');
require_once(FWOLFLIB . 'func/string.php');


/**
 * Validator, mostly for form submition.
 *
 * Include both web frontend and php backend check.
 * Using jQuery to operate javascript.
 *
 * Requirement:
 * 	-	jQuery 1.2.6+ (Call scrollTop in JsAlert())
 *
 * Ref: http://code.google.com/p/easyvalidator/
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright © 2011, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2011-07-21
 */
class Validator extends Fwolflib {

	/**
	 * Validator rule
	 *
	 * array(
	 * 	id => array(
	 * 		id,		// String
	 * 		rule,	// Array of rules str, start with regex, url etc.
	 * 		tip,	// String
	 * 		show-error-blur,	// Boolean
	 * 		show-error-keyup,	// Boolean
	 * 	)
	 * )
	 * @var	array
	 */
	public $aRule = array();


	/**
	 * constructor
	 *
	 * @param	array	$ar_cfg
	 */
	public function __construct ($ar_cfg = array()) {
		parent::__construct($ar_cfg);
	} // end of func __construct


	/**
	 * Get css for tip... etc
	 *
	 * @param	boolean	$b_with_tag		With <script> tag ?
	 * @return	string
	 */
	public function GetCss ($b_with_tag = true) {
		$s_css = '';
		if ($b_with_tag)
			$s_css .= '<style type="text/css" media="screen, print">
				<!--
			';

		// Css body
		$s_css .= '
			div#' . $this->aCfg['id-prefix'] . 'tip {
				-moz-border-radius: 5px;
				-webkit-border-radius: 5px;
				background-color: #F5EBB3;
				border: 1px solid #A6C9E2;
				position: absolute;
				padding: 10px;
				left: 5px;
				z-index: 999;
			}
			div#' . $this->aCfg['id-prefix'] . 'tip #'
					. $this->aCfg['id-prefix'] . 'tip_arrow {
				position: absolute;
				top: 38px;
				left: 5px
			}
			.' . $this->aCfg['id-prefix'] . 'fail {
				background-color: #F6CBCB;
			/*	border: 2px solid red;*/
				color: black;
			}
		' . $this->aCfg['css-add'];


		if ($b_with_tag)
			$s_css .= '-->
				</style>
			';

		return $s_css;
	} // end of func GetCss


	/**
	 * Get validate js
	 *
	 * @param	boolean	$b_with_tag		With <script> tag ?
	 * @return	string
	 */
	public function GetJs ($b_with_tag = true) {
		$s_js = '';
		if ($b_with_tag)
			$s_js .= '<script type=\'text/javascript\'>
				<!--//--><![CDATA[//>
				<!--
			';

		$s_js .='
			/* Append css define to <head> */
			$(\'head\').append(\'\
			' . str_replace("\n", "\\\n", $this->GetCss()) . '\
			\');
		';

		if (!empty($this->aRule))
			foreach ($this->aRule as $rule) {
				$s_js .= '/* Set validate for ' . $rule['id'] . " */\n";
				$s_js .= $this->GetJsRule($rule);
			}

		if (!empty($this->aCfg['form-selector']))
			$s_js .= $this->GetJsFormSubmit(
				$this->aCfg['form-selector']);

		if ($b_with_tag)
			$s_js .= '//--><!]]>
				</script>
			';
		return $s_js;
	} // end of func GetJs


	/**
	 * Get check js for form submit
	 *
	 * @param	string	$s_form		Form selector
	 * @return	string
	 */
	public function GetJsFormSubmit ($s_form) {
		$s_js = '';

		// Need pre define msg alert func ?
		if ('jsalert' == $this->aCfg['func-show-error'])
			$s_js .= $this->GetJsJsAlert();

		$s_js .= '
			$(\'' . $s_form . '\').submit(function () {
				var ar_err = new Array();
		';

		if (!empty($this->aRule))
			foreach ($this->aRule as $rule) {
				$s_js .= '
				ar_err = ar_err.concat('
				. StrUnderline2Ucfirst($this->aCfg['id-prefix']
					. $rule['id'], true)
				. '(false));
				';
			}

		$s_js .= '
			/* Show error msg */
		' . $this->GetJsShowErr();


		// Experiment focus validate fail input after alert error.
		// This can't attach to blur event of each input,
		// that may call alert recurrently, which is infinite loop.
		// So use it only when form submit.
		if (true == $this->aCfg['show-error-focus'])
			$s_js .= '
				var f = function() {
					$(\'.' . $this->aCfg['id-prefix'] . 'fail\')
						.first().focus();
					$(\'' . $s_form . '\').unbind(\'mouseover\', f);
				};
				$(\'' . $s_form . '\').bind(\'mouseover\', f);
			';


		// Disable submit botton
		if (!empty($this->aCfg['form-submit-delay']))
			$s_js .= '
				/* Disable multi submit for some time */
				$(\'' . $s_form . ' input[type="submit"]\')
					.attr(\'disabled\', true);
				setTimeout(function () {
					$(\'' . $s_form . ' input[type="submit"]\')
						.removeAttr(\'disabled\');
				}, '
					. (1000	* $this->aCfg['form-submit-delay'])
				. ');
			';


		$s_js .= '
				return (0 == ar_err.length);
			});
		';

		return $s_js;
	} // end of func GetJsFormSubmit


	/**
	 * Get js for display alert msg using float div
	 *
	 * @return	string
	 */
	public function GetJsJsAlert () {
		$s_js = '';
		$s_func = StrUnderline2Ucfirst($this->aCfg['id-prefix']
			, true) . 'JsAlert';

		$s_id_div = $this->aCfg['id-prefix'] . 'js_alert';
		$s_id_div_bg = $this->aCfg['id-prefix'] . 'js_alert_bg';
		foreach (array('div', 'bg', 'fieldset', 'legend', 'li')
				as $k) {
			if (!empty($this->aCfg['func-jsalert-css-' . $k]))
				$s = str_replace("\n", "\\\n"
						, $this->aCfg['func-jsalert-css-' . $k]);
			eval('$s_css_' . $k . ' = $s;');
		}

		$s_js .= '
			function ' . $s_func . ' (msg) {
				s_msg = \'\';
				$(msg).each(function () {
					s_msg += \'<li>\' + this + \'</li>\';
				});

				/* Set css */
				if (\'undefined\' == typeof(b_' . $this->aCfg['id-prefix']
						. 'css_setted)) {
					var b_' . $this->aCfg['id-prefix'] . 'css_setted = 1;
					$(\'head\').append(\'\
						<style type="text/css" media="screen, print">\
						<!--\
						#' . $s_id_div . ' {\
						' . $s_css_div . '\
						}\
						#' . $s_id_div_bg . ' {\
						' . $s_css_bg . '\
						}\
						#' . $s_id_div . ' fieldset {\
						' . $s_css_fieldset . '\
						}\
						#' . $s_id_div . ' legend {\
						' . $s_css_legend . '\
						}\
						#' . $s_id_div . ' li {\
						' . $s_css_li . '\
						}\
						-->\
						</style>\
					\');
				}

				$(\'body\').append(\'\
					<div id="' . $s_id_div . '" class="'
							. $this->aCfg['func-jsalert-class'] . '">\
						<fieldset>\
						<legend align="center">　'
							. $this->aCfg['func-jsalert-legend']
							. '　</legend>\
						<ul>\
							\' + s_msg + \'\
							<li><a href="javascript:void(0);"\
								onclick="$(\\\'#' . $s_id_div
										. '\\\').remove();'
									. '$(\\\'#' . $s_id_div_bg
										. '\\\').remove();">'
								. $this->aCfg['func-jsalert-close']
								. '</a></li>\
						</ul>\
						</fieldset>\
					</div>\
					<div id="' . $s_id_div_bg . '"></div>\
				\');

				$(\'#' . $s_id_div . '\').css(\'top\'
					, ($(window).height() -
							$(\'#' . $s_id_div . ' fieldset\')
								.height())
						/ 2 + $(window).scrollTop() + \'px\');
				$(\'#' . $s_id_div_bg . '\')
					.height($(document).height() * 1.2);
			} /* end of func ' . $s_func . ' */
		';

		return $s_js;
	} // end of func GetJsJsAlert


	/**
	 * Get validate js of one rule
	 *
	 * @param	array	$ar_rule	Id, tip/text: string;
	 * 		rule: string/array of string.
	 * @return	string
	 */
	public function GetJsRule ($ar_rule) {
		$s_js = '';
		if (!empty($ar_rule['tip']))
			$s_js .= $this->GetJsTip($ar_rule['id'], $ar_rule['tip']);

		$s_func = StrUnderline2Ucfirst($this->aCfg['id-prefix']
			. $ar_rule['id'], true);
		// Validate func for this control
		$s_js .= '
			/**
			 * Validate ' . $ar_rule['id'] . '
			 *
			 * @param	boolean	b_alert_err		Alert when got err
			 * @return	array	Empty means no error.
			 */
			function ' . $s_func . ' (b_alert_err) {
				var obj = $(\'#' . $ar_rule['id'] . '\');
				var ar_err = Array();
				/* Standard error, rule str can customize it. */
				var s_err = \'' . $ar_rule['tip'] . '\';
			';

		// Input is disabled ?
		if (false == $this->aCfg['check-disabled'])
			$s_js .= '
				if (true == obj.attr(\'disabled\'))
					return ar_err;
			';

		// Show loading img ? part 1/2
		if (!empty($this->aCfg['path-img-loading']))
			$s_js .= '
				obj.after(\'<img id="'
					. $this->aCfg['id-prefix'] . 'loading" src="'
					. $this->aCfg['path-img-loading'] . '" />\');
			';

		$s_js .= '
				/* Do check */
		' . $this->GetJsRuleStr($ar_rule) . '
				if (0 < ar_err.length) {
					obj.addClass(\''
						. $this->aCfg['id-prefix'] . 'fail\');
				}
				else {
					obj.removeClass(\''
						. $this->aCfg['id-prefix'] . 'fail\');
				}

				/* If err msg not start with label, prepend it. */
				var s_label = \'\';
				if (0 < ar_err.length) {
					$(ar_err).each(function (index, value) {
						s_label = $(\'label[for="' . $ar_rule['id']
							. '"]\').text().trim()
							.replace(/(:|：)/g, \'\');
						if ((0 < s_label.length) &&
								(null == value.match(\'^\' + s_label)))
							ar_err[index] = (s_label + \''
									. $this->aCfg['tip-separator'] . '\'
								+ value);
					});
				}

				/* Alert err ? default false. */
				if (\'undefined\' == typeof(b_alert_err))
					b_alert_err = false;
				if (true == b_alert_err) {
					' . $this->GetJsShowErr() . '
				}
		';

		// Show loading img ? part 2/2
		if (!empty($this->aCfg['path-img-loading']))
			$s_js .= '
				$(\'#' . $this->aCfg['id-prefix'] . 'loading\')
					.remove();
			';

		$s_js .= '
				return ar_err;
			} /* end of func ' . $s_func . ' */
		';

		// Do validate when blur, and alert if setted
		$s_js .= '
			$(\'#' . $ar_rule['id'] . '\').blur(function() {
		';
		if (true == (isset($ar_rule['show-error-blur'])
				? $ar_rule['show-error-blur']
				: $this->aCfg['show-error-blur']))
			$s_js .= $s_func . '(true);';
		else
			$s_js .= $s_func . '(false);';
		$s_js .= '
			});
		';

		// Do validate when keyup, but no alert
		if (true == (isset($ar_rule['show-error-keyup'])
				? $ar_rule['show-error-keyup']
				: $this->aCfg['show-error-keyup'])) {
			$s_js .= '
				$(\'#' . $ar_rule['id'] . '\').keyup(function() {
					' . $s_func . '(false);
				});
			';
		}

		return $s_js;
	} // end of func GetJsRule


	/**
	 * Get js check str for rule(s)
	 *
	 * @param	array	$ar_rule	Id, tip/text: string;
	 * 		rule: string/array of string.
	 * @return	string
	 */
	public function GetJsRuleStr ($ar_rule) {
		if (!is_array($ar_rule['rule']))
			$ar_rule['rule'] = array($ar_rule['rule']);

		$s_js = '';
		foreach ($ar_rule['rule'] as $rule) {
			// Call by rule cat
			if ('required' == substr($rule, 0, 8))
				$s_js .= $this->GetJsRuleStrRequired();
			elseif ('regex:' == substr($rule, 0, 6))
				$s_js .= $this->GetJsRuleStrRegex(substr($rule, 6));
			elseif ('url:' == substr($rule, 0, 4))
				$s_js .= $this->GetJsRuleStrUrl(substr($rule, 4));
		}

		return $s_js;
	} // end of func GetjsRuleStr


	/**
	 * Get js for rule: regex
	 *
	 * @param	string	$rule
	 * @return	string
	 */
	public function GetJsRuleStrRegex ($rule) {
		$rule = trim($rule);
		$s_js = '
			var reg_validate = new RegExp(' . $rule . ');
			if (!reg_validate.test(obj.val().trim()))
				ar_err.push(s_err);
		';
		return $s_js;
	} // end of func GetJsRuleStrRegex


	/**
	 * Get js for rule: required
	 *
	 * @return	string
	 */
	public function GetJsRuleStrRequired () {
		$s_js = '
			if (0 == obj.val().trim().length)
				ar_err.push(s_err);
		';
		return $s_js;
	} // end of func GetJsRuleStrRequired


	/**
	 * Get js for rule: url
	 *
	 * Rule: url [, [id] | [id in form=id in url]]
	 *
	 * @param	string	$rule
	 * @return	string
	 */
	public function GetJsRuleStrUrl ($rule) {
		$rule = trim($rule);
		$ar = explode(',', $rule);

		$s_js = '
			var s_id = obj.attr(\'id\');
			/*
				Object can use as map type later in ajax post,
				while array cannot.
			*/
			var data = new Object;

			/* Gen post data */
		';

		// Prepare data to do ajax post
		if (0 == count($ar))
			// No id assigned, data is itself only
			$s_js .= '
				data[s_id] = obj.val();
				data = $.param(data);
			';
		elseif ('*' == trim($ar[1]))
			// All form content needed
			$s_js .= '
				data = obj.parent(\'form\').serialize();
			';
		else {
			// Itself and some other input needed, and may have rename

			// Itself, useful here if later rules not include it self.
			$s_js .= '
				data[s_id] = obj.val();
			';
			for ($i = 1; $i < count($ar); $i++) {
				$ar[$i] = trim($ar[$i]);
				if (empty($ar[$i]))
					continue;

				// Single val or indexed ?
				if (0 < strpos($ar[$i], '=')) {
					// Indexed
					list($s_id_form, $s_id_url) = explode('=', $ar[$i]);
					$s_js .= '
						data[\'' . trim($s_id_url) . '\'] = $(\'#'
							. trim($s_id_form) . '\').val();
					';
				}
				else
					// Single val
					$s_js .= '
						data[\'' . $ar[$i] . '\'] = $(\'#'
							. $ar[$i] . '\').val();
					';
			}
			$s_js .= '
				data = $.param(data);
			';
		}

		$s_js .= '
			$.ajax({
				async: false,
				url: \'' . trim($ar[0]) . '\',
				data: data,
				dataType: \'json\',
				type: \'POST\',
				success: function(msg) {
					/* Json return object, need convert to array */
					if (0 < msg.length)
						ar_err = ar_err.concat($.makeArray(msg));
				},
				error: function (jqXHR, textStatus, errorThrown) {
					ar_err.push(\'Ajax request error code \'
						+ jqXHR.status + \': \' + jqXHR.responseText);
				}
			});
		';
		return $s_js;
	} // end of func GetJsRuleStrUrl


	/**
	 * Get js for show error msg by setting
	 *
	 * Array contain err msg is named 'ar_err'.
	 * @return	string
	 */
	public function GetJsShowErr () {
		$s_js = '';
		// Error alert part.
		if (!empty($this->aCfg['func-show-error'])) {
			if ('alert' == $this->aCfg['func-show-error'])
				$s_js .= '
					if (0 < ar_err.length)
						alert(ar_err.join("\n"));
				';
			elseif ('jsalert' == $this->aCfg['func-show-error'])
				$s_js .= '
					if (0 < ar_err.length)
						' . StrUnderline2Ucfirst($this->aCfg['id-prefix']
							, true) . 'JsAlert(ar_err);
				';
			elseif ('JsAlert' == $this->aCfg['func-show-error'])
				$s_js .= '
					if (0 < ar_err.length)
						JsAlert(ar_err);
				';
		}
		return $s_js;
	} // end of func GetJsShowErr


	/**
	 * Get validate tip
	 *
	 * @param	string	$id
	 * @param	string	$tip
	 * @return	string
	 */
	public function GetJsTip ($id, $tip) {
		if (empty($tip))
			return '';

		$s_js = '
			$(\'#' . $id . '\').hover(
				function(e) {
					$(\'body\').append(\'\
						<div id="' . $this->aCfg['id-prefix'] . 'tip">\
							<img id="' . $this->aCfg['id-prefix']
								. 'tip_arrow"\
								src="' . $this->aCfg['path-img-arrow']
									. '" />\
							' . $tip . '</div>\');
					$(\'div#' . $this->aCfg['id-prefix'] . 'tip\')
						.css(\'top\', (e.pageY + ' .
							$this->aCfg['tip-offset-y'] . ') + \'px\')
						.css(\'left\', (e.pageX + ' .
							$this->aCfg['tip-offset-x'] . ') + \'px\');
				},
				function() {
					$(\'div#' . $this->aCfg['id-prefix'] . 'tip\')
						.remove();
				}
			).mousemove(
				function(e) {
					/* Same with above */
					$(\'div#' . $this->aCfg['id-prefix'] . 'tip\')
						.css(\'top\', (e.pageY + ' .
							$this->aCfg['tip-offset-y'] . ') + \'px\')
						.css(\'left\', (e.pageX + ' .
							$this->aCfg['tip-offset-x'] . ') + \'px\');
				}
			);
		';

		// Append hint, common known as '*' after input
		if (!empty($this->aCfg['hint-text']))
			$s_js .= '
				$(\'#' . $id . '\').after(\'<span class="'
					. $this->aCfg['hint-class']
					. '">' . $this->aCfg['hint-text'] . '</span>\
				\');
			';

		return $s_js;
	} // end of func GetJsTip


	/**
	 * Reset rules, or some part of it.
	 *
	 * @param	mixed	$id			Str or array of str, empty means all.
	 * @param	mixed	$part		Empty means all part, or assigned.
	 * @return	this
	 */
	public function ResetRule ($id = array(), $part = '') {
		if (empty($id))
			$this->aRule = array();
		else {
			if (!is_array($id))
				$id = array($id);
			foreach ($id as $s_id) {
				if (empty($part))
					unset($this->aRule[$s_id]);
				else {
					if (!is_array($part))
						$part = array($part);
					foreach ($part as $s_part)
						unset($this->aRule[$s_id][$s_part]);
				}
			}
		}
		return $this;
	} // end of func ResetRule


	/**
	 * Set default config
	 */
	protected function SetCfgDefault () {
		parent::SetCfgDefault();

		$this->SetCfg('id-prefix', 'validate_');

		// User custom additional css define, can overwrite GetCss()
		$this->SetCfg('css-add', '');

		// Check when input is disabled
		// Disabled input will be submit, needn't check in common.
		$this->SetCfg('check-disabled', false);

		// jQuery selector for form, empty for no submit check.
		$this->SetCfg('form-selector', 'form');
		// Disable submit button for some time when clicked.
		$this->SetCfg('form-submit-delay', 3);

		// Func to show error msg
		//	empty: Means no error msg, only set red border
		//	alert: Using javascipt original alert()
		//	jsalert: Using js to show msg in a float div
		//	JsAlert: Using Fwolflib::js::JsAlert()
		//	other: User defined js function(not implement).
		$this->SetCfg('func-show-error', '');

		// Notice: Using JsAlert() now, jsalert is alost useless.
		// Setting for func jsalert
		$this->SetCfg('func-jsalert-close', '继续');
		$this->SetCfg('func-jsalert-legend', 'Form validate fail');
		// JsAlert css
		$this->SetCfg('func-jsalert-class', 'alert_fail');
		$this->SetCfg('func-jsalert-css-div', '
			left: 0px;
			position: absolute;
			text-align: center;
			top: 200px;
			width: 99%;
			z-index: 999;
		');
		$this->SetCfg('func-jsalert-css-bg', '
			background: #E5E5E5;
			filter: alpha(opacity=60);
			height: 100%;
			left: 0px;
			opacity: 0.6;
			position: absolute;
			top: 0px;
			width: 100%;
			z-index: 998;
		');
		$this->SetCfg('func-jsalert-css-fieldset', '
			background: white;
			border: 1px solid red;
			font-weight: bold;
			margin: auto;
			margin-top: -10em;
			padding-bottom: 2em;
			padding-top: 2em;
			width: 40%;
		');
		$this->SetCfg('func-jsalert-css-legend', '
			color: red;
			font-weight: bold;
			font-size: 1.1em;
			margin-left: 2em;
			margin-right: 2em;
		');
		$this->SetCfg('func-jsalert-css-li', '
			list-style: square;
		');

		// Hint for input
		$this->SetCfg('hint-class', 'required');
		$this->SetCfg('hint-text', '*');

		// Path of arrow img in tip
		$this->SetCfg('path-img-arrow'
			, P2R . 'images/validate-arrow.png');
		$this->SetCfg('path-img-loading'
			, P2R . 'images/validate-loading.gif');

		// Show error in these event ?
		// Each id can overwrite these default setting by SetRule().
		// Notice: If show-error-blur is true, and focus change from
		// 1 input to 2 both validate false, there will be dup alert
		// msg occur, bcs confirm alert will cause blur of 2nd input.
		$this->SetCfg('show-error-blur', false);
		$this->SetCfg('show-error-keyup', false);

		// Focus error input after show error
		$this->SetCfg('show-error-focus', false);

		// Tips distance from mouse
		$this->SetCfg('tip-offset-x', -20);
		$this->SetCfg('tip-offset-y', -60);
		// Text between col name and tip,
		// when auto prepend col name to tip.
		$this->SetCfg('tip-separator', ': ');

		return $this;
	} // end of func SetCfgDefault


	/**
	 * Set validate rule
	 *
	 * @param	mixed	$id			Str(, split) or array of str.
	 * @param	array	$ar_cfg
	 * @see		$aRule
	 * @return	this
	 */
	public function SetRule ($id, $ar_cfg) {
		// Id check, convert, trim
		if (empty($id))
			return $this;
		if (!is_array($id))
			// String, maybe ',' splitted
			$id = explode(',', $id);
		array_walk($id, create_function('&$v', '$v = trim($v);'));

		foreach ($id as $s_id) {
			// Id empty after explode ?
			if (empty($s_id))
				continue;

			$this->aRule[$s_id]['id'] = $s_id;

			// Rule: append
			if (isset($ar_cfg['rule'])) {
				if (!is_array($ar_cfg['rule']))
					$ar_cfg['rule'] = array($ar_cfg['rule']);
				foreach ($ar_cfg['rule'] as $s_rule)
					$this->aRule[$s_id]['rule'][] = $s_rule;
			}

			// Other part: overwrite
			foreach (array('tip', 'show-error-blur'
				, 'show-error-keyup') as $s)
				if (isset($ar_cfg[$s]))
					$this->aRule[$s_id][$s] = $ar_cfg[$s];
		}

		return $this;
	} // end of func SetRule


	/**
	 * Set validate rule, old way
	 * for easy use and back compative.
	 *
	 * @param	mixed	$id			Str or array of str.
	 * @param	mixed	$rule		Str or array of rule.
	 * @param	string	$s_tip		Rule info, or tips etc.
	 * @param	this
	 */
	public function SetRuleV1 ($id, $rule, $s_tip = '') {
		if (empty($id) || empty($rule))
			return $this;

		if (!is_array($id))
			$id = array($id);
		foreach ($id as $s_id) {
			$this->aRule[$s_id]['id'] = $s_id;
			if (!is_array($rule))
				$rule = array($rule);
			foreach ($rule as $s_rule)
				$this->aRule[$s_id]['rule'][] = $s_rule;
			$this->aRule[$s_id]['tip'] = $s_tip;
		}

		return $this;
	} // end of func SetRuleV1


} // end of class Validator
?>
