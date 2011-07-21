<?php
require_once(dirname(__FILE__) . '/../fwolflib.php');
require_once(FWOLFLIB . 'func/string.php');


/**
 * Validator, mostly for form submition.
 *
 * Include both web frontend and php backend check.
 * Using jQuery to operate javascript.
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
	 * 	array(
	 * 		id,		// Text or array of cols id text.
	 * 		rule,	// Str/array of rules, start with regex, url etc.
	 * 		tip,
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
				border: 2px solid red;
				color:red;
			}
		';


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
			// Append css define to <head>
			$(\'head\').append(\'\
			' . str_replace("\n", "\\\n", $this->GetCss()) . '\
			\');
		';

		if (!empty($this->aRule))
			foreach ($this->aRule as $rule) {
				$s_js .= '// Set validate for ' . $rule['id'] . "\n";
				$s_js .= $this->GetJsRule($rule);
			}

		if ($b_with_tag)
			$s_js .= '//--><!]]>
				</script>
			';
		return $s_js;
	} // end of func GetJs


	/**
	 * Get validate js of one rule
	 *
	 * @param	array	$ar_rule	Col, rule, text: all string.
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
			function ' . $s_func . ' () {
				var obj = $(\'#' . $ar_rule['id'] . '\');
				var rs_validate = false;	// Check result
				// Do check
		' . $this->GetJsRuleStr($ar_rule['rule']) . '
				if (false == rs_validate) {
					obj.addClass(\''
						. $this->aCfg['id-prefix'] . 'fail\');
				}
				else {
					obj.removeClass(\''
						. $this->aCfg['id-prefix'] . 'fail\');
				}
			} // end of func ' . $s_func . '
		';

		// Do validate when blur
		$s_js .= '
			$(\'#' . $ar_rule['id'] . '\').blur(function() {
				' . $s_func . '();
			});
		';

		return $s_js;
	} // end of func GetJsRule


	/**
	 * Get js check str for rule(s)
	 *
	 * @param	mixed	$ar_rule
	 * @return	string
	 */
	public function GetJsRuleStr ($ar_rule) {
		if (!is_array($ar_rule))
			$ar_rule = array($ar_rule);

		$s_js = '';
		foreach ($ar_rule as $rule) {
			// Find rule cat
			if ('required' == substr($rule, 0, 8))
				$s_js .= $this->GetJsRuleStrRequired();
			elseif ('regex:' == substr($rule, 0, 6))
				$s_js .= $this->GetJsRuleStrRegex(substr($rule, 6));
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
			rs_validate = (reg_validate.test(obj.val().trim()));
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
			rs_validate = (0 < obj.val().trim().length);
		';
		return $s_js;
	} // end of func GetJsRuleStrRequired


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
					//var top = (e.pageY + yOffset);
					//var left = (e.pageX + xOffset);
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
						//.css(\'left\', left+\'px\');
					//$(\'p#vtip\').bgiframe();
				},
				function() {
					$(\'div#' . $this->aCfg['id-prefix'] . 'tip\')
						.remove();
				}
			).mousemove(
				function(e) {
					// Same with above
					$(\'div#' . $this->aCfg['id-prefix'] . 'tip\')
						.css(\'top\', (e.pageY + ' .
							$this->aCfg['tip-offset-y'] . ') + \'px\')
						.css(\'left\', (e.pageX + ' .
							$this->aCfg['tip-offset-x'] . ') + \'px\');
				}
			);
		';
		return $s_js;
	} // end of func GetJsTip


	/**
	 * Init config
	 */
	protected function Init () {
		parent::Init();

		$this->SetCfg('id-prefix', 'validate_');
		// Path of arrow img in tip
		$this->SetCfg('path-img-arrow'
			, P2R . 'images/validate-arrow.png');
		// Tips distance from mouse
		$this->SetCfg('tip-offset-x', -20);
		$this->SetCfg('tip-offset-y', -60);

		return $this;
	} // end of func Init


	/**
	 * Clear all rules
	 */
	public function Reset () {
		$this->aRule = array();
	} // end of func Reset


	/**
	 * Set validate rule
	 *
	 * @param	mixed	$id			Str or array of str.
	 * @param	mixed	$s_rule		Str or array of rule.
	 * @param	string	$s_tip		Rule info, or tips etc.
	 */
	public function SetRule ($id, $s_rule, $s_tip = '') {
		if (empty($id) || empty($s_rule))
			return;

		if (is_array($id))
			foreach ($id as $v)
				$this->aRule[] = array(
					'id'	=> $v,
					'rule'	=> $s_rule,
					'tip'	=> $s_tip,
				);
		else
			$this->aRule[] = array(
				'id'	=> $id,
				'rule'	=> $s_rule,
				'tip'	=> $s_tip,
			);
	} // end of func SetRule


} // end of class Validator
?>
