<?php
require_once(dirname(__FILE__) . '/../../fwolflib.php');


/**
 * Ajax class, select div(layer)
 *
 * Requirement:
 * 	-	jQuery 1.2.6+ (Call scrollTop())
 *
 * @package		fwolflib
 * @subpackage	class.ajax
 * @copyright	Copyright © 2011, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class.ajax@gmail.com>
 * @since		2011-08-09
 */
class AjaxSelDiv extends Fwolflib {

	/**
	 * constructor
	 *
	 * @param	array	$ar_cfg
	 */
	public function __construct ($ar_cfg = array()) {
		parent::__construct($ar_cfg);
	} // end of func __construct


	/**
	 * Get html output
	 *
	 * @return	string
	 */
	public function GetHtml () {
		// Init data again
		$this->Init();

		global $s_id_div, $s_id_bg;
		$s_html = '';

		// Html define
		$s_html .= $this->GetHtmlCss();
		$s_html .= '<div id=\'' . $s_id_bg . '\'>
			<iframe style=\'position: absolute; z-index: -1;\'
				frameborder=\'0\' src=\'about:blank\'></iframe>
			</div>' . "\n";
		$s_html .= $this->GetHtmlDiv();
		$s_html .= $this->GetHtmlJs();

		return $s_html;
	} // end of func GetHtml


	/**
	 * Get html output, div part
	 *
	 * @return	string
	 */
	public function GetHtmlCss () {
		global $ar_id, $s_id, $s_id_div, $s_id_bg;
		global $s_id_close_bottom, $s_id_close_top;
		global $s_id_table, $s_id_title, $s_id_clearit, $s_id_tr_hover;
		global $s_id_empty, $s_id_loading, $s_id_tip;
		global $s_id_row, $s_id_row_tpl;


		// Css body
		$s_css = '';
		$s_css .= '<style type="text/css" media="screen, print">
			<!--
			#' . $s_id_empty . ', #' . $s_id_loading
				. ', #' . $s_id_row_tpl . ' {
				display: none;
			}
			#' . $s_id_empty . ' td, #' . $s_id_loading
				. ' td, #' . $s_id_tip
				. ' td, .' . $s_id . '_col_select {
				text-align: center;
			}
		';

		foreach ($ar_id as $k)
			$s_css .= '
				#' . ${'s_id_' . $k} . ' {
					' . $this->aCfg['css-' . $k] . '
				}
			';

		// Css using class
		$s_css .= '
			.' . $s_id_row . ' {
				' . $this->aCfg['css-row'] . '
			}
			.' . $s_id_tr_hover . ' {
				' . $this->aCfg['css-tr_hover'] . '
			}
		';

		$s_css .= $this->aCfg['css-add'] . '
			-->
			</style>
		';


		// Append css using js
		$s_js = '';
		$s_js .= '<script type=\'text/javascript\'>
			<!--//--><![CDATA[//>
			<!--
			/* Append css define to <head> */
			$(\'head\').append(\'\
			' . str_replace("\n", "\\\n", $s_css) . '\
			\');
			//--><!]]>
			</script>
		';

		return $s_js;
	} // end of func GetHtmlCss


	/**
	 * Get html output, div part
	 *
	 * @return	string
	 */
	public function GetHtmlDiv () {
		global $ar_id, $s_id, $s_id_div, $s_id_bg;
		global $s_id_close_bottom, $s_id_close_top;
		global $s_id_table, $s_id_title, $s_id_clearit, $s_id_tr_hover;
		global $s_id_empty, $s_id_loading, $s_id_tip;
		global $s_id_row, $s_id_row_tpl;
		$s_html = '';

		$s_html .= '<div id=\'' . $s_id_div . '\'>
			<div id=\'' . $s_id_title . '\'>'
				. $this->aCfg['title'] . '</div>
		';

		if (true == $this->aCfg['show-close-top'])
			$s_html .= '
				<div id=\'' . $s_id_close_top . '\'>'
					. $this->aCfg['title-close'] . '</div>
			';

		if (true == $this->aCfg['query']) {
			$s_html .= '
				<div id=\'' . $s_id_clearit . '\'></div>

				<label>' . $this->aCfg['query-input-title'] . '</label>
				<input type=\'text\' id=\''
					. $s_id . '_query\' size=\''
						. $this->aCfg['query-input-size'] . '\' />
				<input type=\'button\' id=\''
					. $s_id . '_submit\' value=\''
						. $this->aCfg['query-submit-title'] . '\' />
			';

			// Put query url as hidden input, so can edit it when needed
			$s_html .= '
				<input type=\'hidden\' id=\''
					. $s_id . '_url\' value=\''
						. $this->aCfg['query-url'] . '\' />
			';
		}

		$s_html .= '
			<table id=\'' . $s_id_table . '\'>
				<thead>
					<tr>
			';

		// Data table title
		if (!empty($this->aCfg['list-col']))
			foreach ($this->aCfg['list-col'] as $k => $v)
				$s_html .= '<th>' . $v . '</th>' . "\n";
		$s_html .= '<th>' . $this->aCfg['sel-title'] . '</th>' . "\n";

		$s_html .= '
					</tr>
				</thead>
				<tbody>
					<tr id=\'' . $s_id_row_tpl . '\'>
			';

		// Data table rows
		if (!empty($this->aCfg['list-col']))
			foreach ($this->aCfg['list-col'] as $k => $v)
				$s_html .= '<td class=\'' . $s_id . '_col_'
					. $k . '\'></td>' . "\n";
		$s_html .= '<td class=\'' . $s_id . '_col_'
			. $this->aCfg['sel-id'] . '\'>' . "\n";
		// Put hidden input here
		if (!empty($this->aCfg['list-hidden']))
			foreach ($this->aCfg['list-hidden'] as $k)
				$s_html .= '<input type=\'hidden\' class=\''
					. $s_id . '_col_' . $k . '\' />' . "\n";

		// Assign onclick using js, avoid lost event when cloning in IE.
		$s_html .= '
							<a href=\'javascript:void(0);\'
								>选择</a>
						</td>
					</tr>
					<tr id=\'' . $s_id_loading . '\'>
						<td colspan=\''	. $this->aCfg['sel-col-cnt'] . '\'>'
							. $this->aCfg['text-loading'] . '</td>
					</tr>
					<tr id=\'' . $s_id_empty . '\'>
						<td colspan=\''	. $this->aCfg['sel-col-cnt'] . '\'>'
							. $this->aCfg['text-empty'] . '</td>
					</tr>
					<tr id=\'' . $s_id_tip . '\'>
						<td colspan=\''	. $this->aCfg['sel-col-cnt'] . '\'>'
							. $this->aCfg['text-tip']
							. $this->aCfg['text-tip-add'] . '</td>
					</tr>
				</tbody>
			</table>
		';

		if (true == $this->aCfg['show-close-bottom'])
			$s_html .= '
				<div id=\'' . $s_id_close_bottom . '\'>'
					. $this->aCfg['title-close'] . '</div>
			';

		$s_html .= '</div>
		';
		return $s_html;
	} // end of func GetHtmlDiv


	/**
	 * Get html output, js part
	 *
	 * @return	string
	 */
	public function GetHtmlJs () {
		global $ar_id, $s_id, $s_id_div, $s_id_bg;
		global $s_id_close_bottom, $s_id_close_top;
		global $s_id_table, $s_id_title, $s_id_clearit, $s_id_tr_hover;
		global $s_id_empty, $s_id_loading, $s_id_tip;
		global $s_id_row, $s_id_row_tpl;
		$s_js = '';

		$s_js .= '<script type=\'text/javascript\'>
			<!--//--><![CDATA[//>
			<!--
			/* Set bg height and width */
			$(\'#' . $s_id_bg . '\')
				.css(\'width\', $(document).width())
				.css(\'height\', $(document).height() * 1.2);
			$(\'#' . $s_id_bg . ' iframe\')
				.css(\'width\', $(document).width())
				.css(\'height\', $(document).height() * 1.2);

			/* Set click action */
			$(\'#' . $this->aCfg['query-id'] . '\').click(function () {
				' . $this->aCfg['js-click'] . '
				$(\'#' . $s_id_bg . '\').show();
				$(\'#' . $s_id_div . '\')
					.css(\'top\', ($(window).height() -
						$(\'#' . $s_id_div . '\').height()) / 3
						+ $(window).scrollTop() + '
						. $this->aCfg['offset-y'] . ' + \'px\')
					.css(\'left\', $(window).width() / 2
						- $(\'#' . $s_id_div . '\').width() / 2
						+ ' . $this->aCfg['offset-x'] . ' + \'px\')
					.show();
			';
			// Do query at once when open select div
			if (true == $this->aCfg['query-when-click'])
				$s_js .= '
					$(\'#' . $s_id . '_submit\').click();
				';
			$s_js .= '
			});

			/* Set query action */
			$(\'#' . $s_id . '_submit\').click(function () {
		';

		// If do query when user input nothing ?
		if (true == $this->aCfg['query-empty'])
			$s_js .= '
					if (true) {
			';
		else
			$s_js .= '
					if (0 < $(\'#' . $s_id . '_query\').val().length) {
			';

		$s_js .= '
					/* Query begin */
					$(\'#' . $s_id_tip . '\').hide();
					$(\'#' . $s_id_loading . '\').show();
					$(\'#' . $s_id_empty . '\').hide();
					$.ajax({
						url: $(\'#' . $s_id . '_url\').val(),
						data: {\'' . $this->aCfg['query-var'] . '\':
							$(\'#' . $s_id . '_query\').val()},
						dataType: \'' . $this->aCfg['query-datatype'] . '\',
						success: function(msg){
							$(\'#' . $s_id_loading . '\').hide();
							$(\'.' . $s_id . '_row\').remove();
							if (0 < msg.length) {
								/* Got result */
								$(msg).each(function(){
									tr = $(\'#' . $s_id . '_row_tpl\').clone();
									tr.addClass(\'' . $s_id . '_row\');

									/* Attach onclick event */
									/* Cloning in IE will lost event */
									$(\'a\', tr).last().click(function () {
										' . $this->aCfg['js-sel'] . '
		';
		// When select, write selected value
		if (!empty($this->aCfg['sel-link']))
			foreach ($this->aCfg['sel-link'] as $k => $v)
				$s_js .= '
										$("#' . $v . '").val(
											$(".' . $s_id
												. '_col_' . $k . '",
												$(this).parent().parent())
												.' . $this->aCfg['list'][$k]['get'] . '());
		';

		$s_js .= '
										$("#' . $s_id_div . '").hide();
										$("#' . $s_id_bg . '").hide();
									});
		';

		// Assign result from ajax json to tr
		if (!empty($this->aCfg['list']))
			foreach ($this->aCfg['list'] as $k => $v) {
				$s_js .= '
									$(\'.' . $s_id . '_col_' . $k . '\'
										, tr).' . $v['get']
											. '(this.' . $k . ');
				';
			}

		$s_js .= '
									/* Row bg-color */
									tr.mouseenter(function () {
										$(this).addClass(\''
											. $s_id_tr_hover . '\');
									}).mouseleave(function () {
										$(this).removeClass(\''
											. $s_id_tr_hover . '\');
									});

									$(\'#' . $s_id_loading . '\')
										.before(tr);
		' . $this->aCfg['js-query'] . '
									tr.show();
								});
							}
							else {
								/* No result */
								$(\'#' . $s_id_empty . '\').show();
							}
						}
					});
				}
				else {
					/* Nothing to query */
					$(\'#' . $s_id_tip . '\').show();
					$(\'#' . $s_id_loading . '\').hide();
					$(\'#' . $s_id_empty . '\').hide();
				}
			});
		';

		// Query when typing
		if (true == $this->aCfg['query-typing'])
			$s_js .= '
				$(\'#' . $s_id . '_query\').keyup(function () {
					$(\'#' . $s_id . '_submit\').click();
				});
		';

		$s_js .= '
			/* Link to hide select layer */
			$(\'#' . $s_id_close_bottom . ', #'
				. $s_id_close_top . '\').click(function () {
				$(this).parent().hide();
				$(\'#' . $s_id_bg . '\').hide();
			});
			//--><!]]>
			</script>
		';

		return $s_js;
	} // end of func GetHtmlJs


	/**
	 * Init treatment
	 */
	public function Init () {
		parent::Init();

		// Prepare id vars
		global $ar_id, $s_id, $s_id_div, $s_id_bg;
		global $s_id_close_bottom, $s_id_close_top;
		global $s_id_table, $s_id_title, $s_id_clearit, $s_id_tr_hover;
		global $s_id_empty, $s_id_loading, $s_id_tip;
		global $s_id_row, $s_id_row_tpl;
		$s_id = $this->aCfg['id-prefix'] . $this->aCfg['id'];
		$ar_id = array('bg', 'close_bottom', 'close_top', 'div',
			'table', 'title', 'clearit',
			'empty', 'loading', 'tip'
		);
		foreach ($ar_id as $k)
			eval('$s_id_' . $k . ' = $s_id . "_" . "' . $k . '";');
		// Using class
		$s_id_tr_hover = $s_id . '_hover';
		$s_id_row = $s_id . '_row';
		$s_id_row_tpl = $s_id . '_row_tpl';

		// Join select list cols and hidden
		$this->aCfg['list'] = array();
		if (!empty($this->aCfg['list-col']))
			foreach ($this->aCfg['list-col'] as $k => $v) {
				$this->aCfg['list'][$k] = array(
					'title'	=> $v,
					'get'	=> 'text',	// jQuery method to read content
				);
			}
		if (!empty($this->aCfg['list-hidden']))
			foreach ($this->aCfg['list-hidden'] as $k) {
				$this->aCfg['list'][$k] = array(
					'get'	=> 'val',	// jQuery method to read content
				);
			}

		// Join tips, merge pagesize in.
		$this->aCfg['text-tip-add'] = str_replace('{pagesize}'
			, $this->aCfg['query-pagesize']
			, $this->aCfg['text-tip-add-tpl']);
		$this->aCfg['sel-col-cnt'] = count($this->aCfg['list-col']) + 1;
	} // end of Init


	/**
	 * Set default config
	 *
	 * @return	this
	 */
	protected function SetCfgDefault () {
		parent::SetCfgDefault();

		$this->SetCfg('id-prefix', 'ajax_sel_div_');
		$this->SetCfg('id', '1');
		$this->SetCfg('title', '请选择');
		$this->SetCfg('title-close', '关闭');

		// Allow query data by user input
		$this->SetCfg('query', true);
		// Id in form, for which click to show select div
		$this->SetCfg('query-id', '');
		$this->SetCfg('query-input-size', 30);
		$this->SetCfg('query-input-title', '名称：');
		$this->SetCfg('query-submit-title', '查询');
		$this->SetCfg('query-pagesize', 10);	// Page size

		// Json query
		// Do query when user input is empty ?
		$this->SetCfg('query-empty', true);
		// Query when user input ?
		$this->SetCfg('query-typing', true);
		// Url to treat ajax request
		$this->SetCfg('query-url', '');
		// Do query when open select div ?
		$this->SetCfg('query-when-click', false);
		// Var name for value in user input for ajax POST
		$this->SetCfg('query-var', 's');
		$this->SetCfg('query-datatype', 'json');

		// Show switch
		$this->SetCfg('show-close-bottom', true);
		$this->SetCfg('show-close-top', true);

		$this->SetCfg('text-loading', '正在查询，请稍候。');
		$this->SetCfg('text-empty', '没有找到相应信息，请更换查询条件。');
		$this->SetCfg('text-tip', '请输入准确名称中的连续部分进行查询。');
		// Tip will auto appended by page size(add)
		$this->SetCfg('text-tip-add', '');
		$this->SetCfg('text-tip-add-tpl', '查询结果最多显示 {pagesize} 项。');

		// Select link
		$this->SetCfg('sel-id', 'select');
		$this->SetCfg('sel-title', '选择');
		// Will auto compute later
		$this->SetCfg('sel-col-cnt', 0);
		// When selected, write these data back
		$this->SetCfg('sel-link', array(
			//'id in list'	=> 'id in form',
		));

		// Select list cols
		// Id should fit index of result data array
		$this->SetCfg('list-col', array(
			//id => title,
		));
		// Select list hidden value
		// Will auto show with 'select' link
		$this->SetCfg('list-hidden', array(
			//id,
		));

		// Div position adjust(based on h/v center)
		$this->SetCfg('offset-x', 0);
		$this->SetCfg('offset-y', 0);

		// User added js
		// After user click on form input, before default action
		$this->SetCfg('js-click', '');
		// After treat server result
		// After default action and before result show.
		$this->SetCfg('js-query', '');
		// When user click select link, before default action
		$this->SetCfg('js-sel', '');


		$this->SetCfg('css-bg', '
			background: #E5E5E5;
			display: none;
			filter: alpha(opacity=60);
			left: 0px;
			opacity: 0.6;
			position: absolute;
			top: 0px;
			z-index: 998;
		');
		$this->SetCfg('css-close_bottom', '
			cursor: pointer;
			margin-top: 0.5em;
			text-align: right;
			width: 100%;
		');
		$this->SetCfg('css-close_top', '
			cursor: pointer;
			float: right;
		');
		$this->SetCfg('css-div', '
			background-color: #FFF;
			border: 1px solid #999;
			display: none;
			padding: 0.7em;
			position: absolute;
			text-align: center;
			width: 700px;
			z-index: 999;
		');
		$this->SetCfg('css-table', '
			border: 1px solid;
			border-collapse: collapse;
			border-spacing: 0;
			float: none;
			line-height: 1.2em;
			text-align: center;
			vertical-align: baseline;
			width: 100%;
		');
		$this->SetCfg('css-title', '
			float: left;
			font-size: 1.2em;
			font-weight: bold;
			margin-bottom: 0.7em;
			padding-left: 2em;
			text-align: center;
			width: 90%;
		');
		$this->SetCfg('css-tr_hover', '
			background-color: #e3e3de;
		');
		$this->SetCfg('css-clearit', '
			clear: both;
		');
		$this->SetCfg('css-empty', '');
		$this->SetCfg('css-loading', '');
		$this->SetCfg('css-tip', '');
		// Css for row using class, not id
		$this->SetCfg('css-row', '');
		// Css add are user defined, can overwrite upper setting
		$this->SetCfg('css-add', '
		');

		return $this;
	} // end of func SetCfgDefault


} // end of class AjaxSelDiv
?>
