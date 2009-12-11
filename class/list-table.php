<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2003-2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2003-05-17 12:17:14
 * @version		$Id$
 */

require_once('fwolflib/func/request.php');

/**
 * Generate table list
 *
 * Table head and body are given seperately, but have some relationship,
 * you can give head or body contain more data, then limit disp by fit it
 * to another part.
 *
 * Notice: No db query feather in this, data are transferd from outerside.
 *
 * <code>
 * $s = '';
 * // Set <thead>
 * $art = array('col1' => 'description_col1',
 * 		'col2' => 'description_col2',
 * 		);
 * // Set <thead> without col name, only description given.
 * //$art = array('description_col1', 'description_col2');
 *
 * // Set <tbody> data, 1st dim of ar is row, 2nd dim of ar is col-val pair.
 * $ard = array(...);
 *
 * // Set Config, check manual to see their effect.
 * $ar_conf = array(
 * 		'fit_data_title' => 3,
 * 		);
 *
 * // New object
 * //$this->oLt = new ListTable($this->oTpl, $ard, $art, '', $ar_conf);
 * // Or cal SetXxx func, note that SetConfig MUST before SetData
 * $this->oLt->SetConfig($ar_conf);
 * $this->oLt->SetData($ar, $art);
 *
 * // Got output html
 * $s = $this->oLt->GetHtml();
 * </code>
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2003-2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2003-05-17 12:17:14
 * @version		$Id$
 */
class ListTable
{
	/**
	 * Configuration
	 *
	 * <code>
	 * color_bg_[th/tr_even/tr_odd]:
	 * 					Colors of rows.
	 * color_bg_tr_hover:
	 * 					Change to this color when mouseover of row.(Not implement)
	 * fit_data_title:	0=data fit title, cut data items who's index not
	 * 					in title
	 * 					1=title fit data.
	 * 					2=fit to fewest, only items both have allowed.
	 * 					3=fit to mostest, all items in title or data allowed.
	 * fit_empty:		If an value in data is empty, set to this value.
	 * 					Title will always set to field name in same situation.
	 * code_prefix:		Prefix auto add before $sId, use to generate html.
	 * page_cur:		Current page no, ONLY USED TO DISP PAGER.
	 * page_param:		Param name of page number in URL,
	 * 					Will change if $sId changed.
	 * page_size:		Rows per page, ONLY USED TO DISP PAGER, havn't any
	 * 					effect to list data.
	 * rows_total:		Total rows, ONLY USED TO DISP PAGER.
	 * tpl:				Smarty template file to use.
	 * </code>
	 * @var	array
	 */
	protected $aConfig = array(
		// 浅蓝色配色方案
		// fwolflib-list-table = fl_lt
		'code_prefix'		=> 'fl_lt',		// Used in id/class in html and css.
		'color_bg_th'		=> '#d0dcff',	// 表头（第0行）
		'color_bg_tr_even'	=> '#fff',		// 偶数行
		'color_bg_tr_hover'	=> '#e3e3de',	// 鼠标指向时变色
		'color_bg_tr_odd'	=> '#eef2ff',	// 奇数行，tbody后从0开始算
		'fit_data_title'	=> 0,
		'fit_empty'			=> '&nbsp;',
		'page_cur'			=> 1,
		'page_param'		=> 'p',
		'page_size'			=> 10,
		'pager'				=> false,		// Is or not use pager
		'pager_bottom'		=> true,		// Is or not use pager bottom, used when pager=true
		// This is a message template
		// When display, use key append by '_value'
		'pager_text_cur'	=> '共{rows_total}条记录，每页显示{page_size}条，当前为第{page_cur}/{page_max}页',

		'pager_text_first'	=> '首页',
		'pager_text_goto1'	=> '转到第',
		'pager_text_goto2'	=> '页',
		'pager_text_goto3'	=> '转',
		'pager_text_last'	=> '尾页',
		'pager_text_next'	=> '下一页',
		'pager_text_prev'	=> '上一页',
		'pager_text_spacer'	=> ' | ',		// To be between below texts.
		'pager_top'			=> true,		// Is or not use pager top, used when pager=true
//		'param'				=> 'p',			// Used in url to set page no.
		'rows_total'		=> 0,
		'tpl'				=> 'list-table.tpl',
		);

	/**
	 * 数组变量，指向要显示数据存放的数组，其格式见类说明
	 * @var	array
	 */
	protected $aData = array();

	/**
	 * Page url param array.
	 * @var array
	 */
	protected $aParam = array();

	/**
	 * Title of data, used as table title.
	 * @var	array
	 */
	protected $aTitle = array();

	/**
	 * Array of url, for links to display in tpl
	 * <code>
	 * array(
	 * 	base	=> Original page url
	 * 	first	=> First page
	 * 	last	=> Last page
	 * 	next	=> Next page
	 * 	prev	=> Prev page
	 * )
	 * </code>
	 * @var	array
	 */
	protected $aUrl = array(
		'base'		=> '',
		'first'		=> '',
		'last'		=> '',
		'next'		=> '',
		'prev'		=> '',
		);

	/**
	 * 模板变量，指向在构造函数中传入的全局模板变量
	 * @var	object
	 */
	protected $oTpl = null;

	/**
	 * Class of this list in html, used with {@see $sId}
	 *
	 * Diff between $sClass and $sId:
	 * $sClass has no prefix, while $sId has.
	 * $sClass can be applyed css in project css file,
	 * 	while $sId can be applyed css inline in tpl file.
	 * @var	string
	 */
	protected $sClass = 'fl_lt';

	/**
	 * Identify of this list,
	 * Also used in html, as div id property.
	 * @var	string
	 */
	protected $sId = 'fl_lt';


	/**
	 * Construct
	 *
	 * $ard, $art can't use referenct because title and data value maybe
	 * changed in treatment.
	 * @param	object	&$tpl	Smarty object, will save as {@link $oTpl}.
	 * @param	array	$ard	Data array, will save as {@link $aData}.
	 * @param	array	$art	Title of list.
	 * @param	string	$id		Identify of this list, while multi list
	 * 							in page, this is needed.
	 * 							Note: will be applyed prefix automatic
	 * 							when write to $sId.
	 * @param	array	&$conf	Configuration.
	 */
	public function __construct(&$tpl, $ard = array(), $art = array(),
		$id = '', &$conf = array())	{
		$this->GetParam();
		$this->oTpl = $tpl;

		// Config will effect SetData, so set it first.
		$this->SetConfig($conf);
		$this->oTpl->assign_by_ref('lt_config', $this->aConfig);

		$this->SetData($ard, $art);
		$this->SetId($id);
	} // end of func ListTable


	/**
	 * Fit data and title when their items count diff
	 *
	 * <code>
	 * fit_data_title:	0=data fit title, cut data items who's index not
	 * 					in title
	 * 					1=title fit data.
	 * 					2=fit to fewest, only items both have allowed.
	 * 					3=fit to mostest, all items in title or data allowed.
	 * </code>
	 * Notice: data have multi row(2 dim), title have only 1 row(1 dim).
	 * @see	$aConfig['fit_data_title']
	 */
	protected function FitDataTitle()
	{
		if (empty($this->aData) || empty($this->aTitle))
			return ;

		// Store result
		$ar_title = array();
		$ar_data = array();

		// Will compare by array keys, data use it's first row
		$keys_data = array_keys($this->aData[0]);
		$keys_title = array_keys($this->aTitle);

		switch ($this->aConfig['fit_data_title'])
		{
			case 0:
				// data fit to title

				// Int index and string are difference
				// In common, we check only title's index type
				// Int index, can only fit by index position
				if (0 === $keys_title[0])
				{
					$ar_title = &$this->aTitle;
					foreach ($keys_title as $k => $v)
						foreach ($this->aData as $idx => $row)
							if (isset($row[$keys_data[$k]]))
								$ar_data[$idx][$keys_data[$k]] = &$row[$keys_data[$k]];
							else
								$ar_data[$idx][$keys_data[$k]] = $this->aConfig['fit_empty'];
				}
				else
				{
					$ar_title = &$this->aTitle;
					foreach ($keys_title as $k => $v)
						foreach ($this->aData as $idx => $row)
							if (isset($row[$v]))
								$ar_data[$idx][$v] = &$row[$v];
							else
								$ar_data[$idx][$v] = $this->aConfig['fit_empty'];
				}
				break;
			case 1:
				// title fit to data, inser empty title if havn't

				// Int index, can only fit by index position
				if (0 === $keys_title[0])
				{
					$ar_data = &$this->aData;
					foreach ($keys_data as $k => $v)
						if (isset($keys_title[$k]))
							$ar_title[$k] = &$this->aTitle[$k];
						else
							// Use data's index name
							$ar_title[$k] = $v;
				}
				else
				{
					$ar_data = &$this->aData;
					foreach ($keys_data as $k => $v)
						if (isset($this->aTitle[$v]))
							$ar_title[$v] = &$this->aTitle[$v];
						else
							$ar_title[$v] = $v;
				}
				break;
			case 2:
				// Fit to fewest
				// Cut title first, then fit to title
				// Cut title:
				$ar_title = &$this->aTitle;
				if (0 === $keys_title[0])
				{
					// Int indexed
					// Remove title if title has more items than data
					for ($i = count($keys_data); $i < count($keys_title); $i++)
						unset($ar_title[$i]);
				}
				else
				{
					// String indexed
					// Remove item in title which not in data
					foreach ($keys_title as $k => $v)
						if (!in_array($v, $keys_data))
							unset($ar_title[$v]);
				}
				// Then use function itself to fit data to cutted title
				$this->aConfig['fit_data_title'] = 0;
				$this->FitDataTitle();
				$this->aConfig['fit_data_title'] = 2;
				$ar_data = &$this->aData;
				$ar_title = &$this->aTitle;
				break;
			case 3:
				// Fit to mostest
				// Fill title first, then fit to title
				// Fill title:
				if (0 === $keys_title[0])
				{
					// Add as append
					$ar_title = &$this->aTitle;
					// Int indexed
					// Add title if title has fewer items than data
					for ($i = count($keys_title); $i < count($keys_data); $i++)
						// Can only use field name in data
						$ar_title[$i] = $keys_data[$i];
				}
				else
				{
					// Add as insert
					// need to merge keys first to keep order
					$keys_merge = array_merge($keys_title, $keys_data);
					foreach ($keys_merge as $k => $v)
						if (in_array($v, $keys_title))
							$ar_title[$v] = $this->aTitle[$v];
						else
							// Title items is fewer, need to fill
							// These infact is keys from data,
							// because of merge, so we can use $v directly
							$ar_title[$v] = $v;
				}
				$this->aTitle = &$ar_title;
				// Then use function itself to fit data to cutted title
				$this->aConfig['fit_data_title'] = 0;
				$this->FitDataTitle();
				$this->aConfig['fit_data_title'] = 2;
				$ar_data = &$this->aData;
				$ar_title = &$this->aTitle;
				break;
			default:
		}


		// Data write back
		//var_dump($ar_data);
		$this->aData = &$ar_data;
		$this->aTitle = &$ar_title;
	} // end of func FitDataTitle


	/**
	 * Get full output html
	 * @return	string
	 */
	public function GetHtml()
	{
		return $this->oTpl->fetch($this->aConfig['tpl']);
	} // end of func GetHtml


	/**
	 * Get http GET param.
	 * @return	array
	 */
	public function GetParam() {
		$this->aParam = &$_GET;
		if (!empty($this->aParam) && !get_magic_quotes_gpc()) {
			foreach ($this->aParam as $k=>$v) {
				$this->aParam[$k] = addslashes($v);
			}
		}
		$this->aUrl['base'] = GetSelfUrl(false);
		if (isset($this->aParam[$this->aConfig['page_param']])) {
			$this->ParsePageCur($this->aParam[$this->aConfig['page_param']]);
		}
		return $this->aParam;
	} // end of func GetParam


	/**
	 * Get info about some part of query sql, eg: limit, order by
	 * @return	array
	 */
	public function GetSqlInfo() {
		$ar = array();
		$ar['limit'] = array(
			'offset'	=> $this->aConfig['page_size'] * ($this->aConfig['page_cur'] - 1),
			'count'		=> $this->aConfig['page_size'],
			);
		return $ar;
	} // end of func GetSqlInfo


	/**
	 * Parse & compute page_cur param
	 *
	 * @param	int	$p	Page num param come from outer
	 * @return	int
	 */
	protected function ParsePageCur($p = 0) {
		if (0 == $p) {
			// Read from GET prarm
			$i = GetRequest($_REQUEST, $this->aConfig['page_param']);
			// Special & dangous setting, use only if 1 LT in page
			$i1 = GetRequest($_REQUEST, 'p');
			if (!empty($i))
				$page_cur = $i;
			elseif (!empty($i1))
				$page_cur = $i1;
			else
				$page_cur = 1;
		} else
			$page_cur = $p;

		// Validate min and max
		// Min
		if (1 > $page_cur)
			$page_cur = 1;
		// Max
		if (0 < $this->aConfig['rows_total']
			&& 0 < $this->aConfig['page_size']) {
			$i = ceil($this->aConfig['rows_total']
				/ $this->aConfig['page_size']);
			if ($i < $page_cur)
				$page_cur = $i;
		}

		// Result
		$this->aConfig['page_cur'] = $page_cur;
		return $page_cur;
	} // end of func ParsePageCur


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
	 * Set table data and title to display.
	 * @param	array	&$ard	Data, will save as {@link $aData}.
	 * @param	array	&$art	Title of list.
	 */
	public function SetData(&$ard = array(), &$art = array())
	{
		if (!empty($ard))
			$this->aData = $ard;
		if (!empty($art))
			$this->aTitle = $art;

		// Same number of items maybe index diff, so always do fit.
		$this->FitDataTitle();

		$this->oTpl->assign_by_ref('lt_data', $this->aData);
		$this->oTpl->assign_by_ref('lt_title', $this->aTitle);

		return ;
		/* obsolete
		//$this->aData = &$ar;
		// 将输入的数组转换成用数字作为索引的，因为SMARTY不支持ASSOC索引
		$this->aData = array();
		if (empty($ar))
		{
			return(false);
		}
		foreach ($ar as $key=>$val)
		{
			array_push($this->aData, $val);
		}
		//
		$this->mTotalRows = count($this->aData);
		*/
	} // end of func SetData


	/**
	 * Set identify and class of this list <div> in html
	 * @param	string	$id
	 * @param	string	$class
	 * @return	string
	 */
	public function SetId($id, $class = '')	{
		if (empty($id))
			$this->sId = $this->aConfig['code_prefix'];
		else
			$this->sId = $this->aConfig['code_prefix'] . '_' . $id;
		if (!empty($class))
			$this->sClass = $class;
		else
			// On default, class = id
			$this->sClass = $this->sId;
		$this->oTpl->assign_by_ref('lt_id', $this->sId);
		$this->oTpl->assign_by_ref('lt_class', $this->sClass);

		// Change page_param
		$this->aConfig['page_param'] = $this->sId . '_p';
		$this->ParsePageCur();

		return $this->sId;
	} // end of func SetId


	/**
	 * Set pager info
	 *
	 * Config data will also write to $aConfig, the difference with direct set config
	 * is this will add more treatment about pager.
	 * And use after SetConfig()
	 * @param	int		$rows_total	Total row/record number
	 * @param	int		$page_cur	Current displayed page, default is get from GET param
	 * 								if fail, set to 1.
	 * @see		$aConfig
	 */
	public function SetPager($rows_total= 0, $page_cur = 0) {
		// Enable pager disp
		$this->aConfig['pager'] = true;

		// Auto compute total rows if not assigned
		if (0 == $rows_total)
			$rows_total = count($this->aData);
		$this->aConfig['rows_total'] = $rows_total;

		// Some param needed
		$page_cur = $this->ParsePageCur($page_cur);
		$page_size = $this->aConfig['page_size'];
		$page_max = ceil($rows_total / $page_size);

		// If data rows exceeds page_size, trim it
		if (count($this->aData) > $page_size) {
			// If page = 3/5, trim page 1, 2 first
			for ($i = 0; $i < ($page_cur - 1) * $page_size; $i ++)
				unset($this->aData[$i]);
			// Then trim page 4, 5
			for ($i = $page_cur * $page_size; $i < $page_max * $page_size; $i ++)
				unset($this->aData[$i]);
		}

		$this->aConfig['pager_text_cur_value'] = str_replace(
			array('{page_cur}', '{page_max}', '{rows_total}', '{page_size}'),
			array($page_cur, $page_max, $rows_total, $page_size),
			$this->aConfig['pager_text_cur']);

		// Generate url for pager
		//$this->aUrl['base'] = GetSelfUrl(true);	// Move to GetParam()
		if (1 < $page_cur) {
			// Not first page
//			$this->aUrl['first'] = $this->aUrl['base'] . '&' . $this->sId
//				. '-page_no=' . $page_cur;
//			$this->aUrl['prev'] = $this->aUrl['base'] . '&' . $this->sId
//				. '-page_no=' . ($page_cur - 1);
			$this->aUrl['first'] = $this->SetParam($this->aConfig['page_param'], 1);
			$this->aUrl['prev'] = $this->SetParam($this->aConfig['page_param'], $page_cur - 1);
		} else {
			$this->aUrl['first'] = '';
			$this->aUrl['prev'] = '';
		}
		if ($page_cur < $page_max) {
			// Not last page
//			$this->aUrl['next'] = $this->aUrl['base'] . '&' . $this->sId
//				. '-page_no=' . ($page_cur + 1);
//			$this->aUrl['last'] = $this->aUrl['base'] . '&' . $this->sId
//				. '-page_no=' . $page_max;
			$this->aUrl['next'] = $this->SetParam($this->aConfig['page_param'], $page_cur + 1);
			$this->aUrl['last'] = $this->SetParam($this->aConfig['page_param'], $page_max);
		} else {
			$this->aUrl['next'] = '';
			$this->aUrl['last'] = '';
		}

		// Assign url to tpl
		$this->oTpl->assign_by_ref('lt_url', $this->aUrl);
		$this->oTpl->assign('lt_url_form', $this->SetParam(array(), $this->aConfig['page_param']));
		// Assign hidden input
		if (!empty($this->aParam)) {
			$s = '';
			foreach ($this->aParam as $k => $v)
				$s .= "<input type=\"hidden\" name=\"$k\" value=\"$v\" />\n";
			$this->oTpl->assign('lt_url_form_hidden', $s);
		}
	} // end of func SetPager


	/**
	 * Set url param, get the url
	 *
	 * If $k is string, then $v is string to and means $k=$v.
	 * if $k is array, then means key=>val in $k is added, and val in $v is removed.
	 * Always 'remember' setting and return result url.
	 * @param	mixed	$k
	 * @param	mixed	$v
	 * @return	string
	 */
	public function SetParam($k, $v = '') {
		if (!is_array($k) && !is_array($v)) {
			$this->aParam[addslashes($k)] = addslashes($v);
		}
		if (is_array($k)) {
			foreach ($k as $key => $val)
				$this->aParam[addslashes($key)] = addslashes($val);
			if (!is_array($v))
				$v = array($v);
			if (is_array($v))
				foreach ($v as $val)
					if (isset($this->aParam[$val]))
						unset($this->aParam[$val]);

		}
		// Generate url and return
		$s = '';
		foreach ($this->aParam as $k => $v)
			$s .= "&$k=$v";
		if (!empty($s))
			$s{0} = '?';
		$s = $this->aUrl['base'] . $s;
		return $s;
	} // end of func SetParam


	// Old method

	/**
	* 生成分页索引代码
	*
	* 所有参数使用相关的类变量，如果类变量没有事先赋值，则会使用默认值
	* @access   private
	* @return   string
	*/
	function GetIndex()
	{
		//条件不满足时，返回空串
		if (0 == $this->mRowsPerPage)
		{
			return('');
		}
		//如果引用页的地址为.../dir/的话，那么将出错，因为document.URL是不包含参数的
		//此问题已通过在JS中增加判断的方法解决
		if (empty($this->mSubmitUrl))
		{
			$this->mSubmitUrl = $_SERVER['REQUEST_URI'];
		}
		//一共的页数
		$total_pages = ceil($this->mTotalRows / $this->mRowsPerPage);
		if (1 > $total_pages)		   { $total_pages = 1; }
		if (1 > $this->mCurPage)			   { $this->mCurPage = 1;	 }
		if ($this->mCurPage > $total_pages)	{ $this->mCurPage = $total_pages; }
		//生成的HTML字符串
		$str_html = '<table width="80%" border="0" cellspacing="0" cellpadding="0" align="center"><FORM METHOD="get" ACTION="' . $this->mSubmitUrl . '" onSubmit="return jump(page.value);"><tr><td align="right">';

		if (($this->mCurPage == 1) || ($total_pages == 1))
		{
			$str_html .= $this->mIndexTips[$this->mIndexTipsId][0] . '　' . $this->mIndexTips[$this->mIndexTipsId][1] . '　';
		}
		else
		{
			$s_url = $this->SetUrlParam($this->mSubmitUrl, 'page', '1');
			$str_html .= '<a href=' . $s_url . ' title="' . $this->mIndexTips[0][0] . '">' . $this->mIndexTips[$this->mIndexTipsId][0] . '</a>　';
			$s_url = $this->SetUrlParam($this->mSubmitUrl, 'page', strval($this->mCurPage - 1));
			$str_html .='<a href=' . $s_url . ' title="' . $this->mIndexTips[0][1] . '">' . $this->mIndexTips[$this->mIndexTipsId][1] . '</a>　';
		}
		if ($this->mCurPage == $total_pages)
		{
			$str_html .= $this->mIndexTips[$this->mIndexTipsId][2] . '　' . $this->mIndexTips[$this->mIndexTipsId][3] . '　';
		}
		else
		{
			$s_url = $this->SetUrlParam($this->mSubmitUrl, 'page', strval($this->mCurPage + 1));
			$str_html .= '<a href=' . $s_url . ' title="' . $this->mIndexTips[0][2] . '">' . $this->mIndexTips[$this->mIndexTipsId][2] . '</a>　';
			$s_url = $this->SetUrlParam($this->mSubmitUrl, 'page', strval($total_pages));
			$str_html .='<a href=' . $s_url . ' title="' . $this->mIndexTips[0][3] . '">' . $this->mIndexTips[$this->mIndexTipsId][3] . '</a>　';
		}
		$str_html .= '当前为第' . $this->mCurPage . '/' . $total_pages . '页，共' . $this->mTotalRows . '条记录　';
		$str_html .= '跳转<input name="page" id="page" type="text" value="' . $this->mCurPage . '" size="3" align="right"> <input type="button" value="Go" onClick="return jump(page.value);"></td></tr></FORM></table>';
		$str_html .= '<script language="JavaScript" type="text/JavaScript">function jump(p) {if (document.URL.match(/(page=[0-9]+)/)){document.URL=(document.URL.replace(/(page=[0-9]+)/, "page=" + p));}else{if (document.URL.match(/[?]{1}/)) {document.URL=document.URL + "&page=" + p;} else {document.URL=document.URL + "?page=" + p;}}return false;}</script>';

		return($str_html);
	} // end func GetIndex


} // end class ListTable
?>
