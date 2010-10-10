<?php
/**
 * Obsoleted.
 *
* @package      MaGod
* @copyright    Copyright 2003, Fwolf
* @author       Fwolf <fwolf001@tom.com>
*/

require_once('MaGod/MaGod.php');

/**
* 列表类，以包含表头、分页、数据表格的方式显示各种列表。
*
* 传入的数组值应为至少包含一条数据的二维数组，并且其第二维的值也是数组，这个数组的维数决定了将来生成的列表会有几列。
* 数组的第一行为表头，其余是各数据行。简单理解，数组和你要生成的列表的格式几乎是一样的。
* 没有必要将从数据库中读出的全部数据都放到数据中，只需要将本页要显示的数据（比如，20条）放进来即可。
* 本类使用了模板文件class/DispListTable1.html。
*
* [2003-08-26] V1.0 将采用SMARTY模板类改写，同时文件名命名为DispListTable1.php（为了以前的程序能够使用原来的类文件）。
*
* 用法示例：
*
* //先准备好$tpl模板变量和存储数据的数组$ar（数值已经存入）
*
* $lt = new DispListTable($tpl, $ar);
*
* $lt->mCurPage = 2;
*
* $lt->mTotalRows = 108;
*
* $lt->mRowsPerPage = 50;
*
* //以下变量可省略，采用默认值
*
* $lt->mIsDispTitle = 1;
*
* $lt->mIsDispIndex = 1;
*
* $lt->mIsDispHead = 1;
*
* $lt->mSubmitUrl = 'list.php?part=level1';
*
* $lt->mListTitle = '某某列表';
*
* $lt->Disp();
*
* //显示第二个列表
*
* $lt->SetData($ar2);
*
* $lt->Disp();
*
* // End of Example
*
* @package    MaGod
* @copyright  Copyright 2003, Fwolf
* @author     Fwolf <fwolf001@163.net>
* @since      2003-05-17 12:17:14
* @access     public
* @version    $Id$
*/
class DispListTable
{
	/**
	 * 显示列表之前是否重置模板数据
	 * @access	public
	 * @var	boolean
	 */
	var $mListClearAllAssign = true;

	/**
    * 列表的标题
    * @access   public
    * @var      string
    */
    var $mListTitle = '';

    /**
     * 索引的提示信息数组
     * 第一组的内容将被作为提示信息
     * @access  private
     * @var array
     */
    var $mIndexTips = array(array('第一页', '上一页', '下一页', '最后页'),
                            array('|<', '<<', '>>', '>|'),
                            array('<b>|</b><font face="Wingdings 3" style="font-family: \'Wingdings 3\';">&#161;</font>', '<font face="Wingdings 3" style="font-family: \'Wingdings 3\';">&#161;</font>', '<font face="Wingdings 3" style="font-family: \'Wingdings 3\';">&#162;</font>', '<font face="Wingdings 3" style="font-family: \'Wingdings 3\';">&#162;</font><b>|</b>'),  // 197/198
                            array('<font face="Webdings" style="font-family: \'Webdings\';">&#57;</font>', '<font face="Webdings" style="font-family: \'Webdings\';">&#55;</font>', '<font face="Webdings" style="font-family: \'Webdings\';">&#56;</font>', '<font face="Webdings" style="font-family: \'Webdings\';">&#58;</font>'));

    /**
     * 使用哪组索引提示信息，默认用第一组
     * @access  public
     * @var int
     */
    var $mIndexTipsId = 0;

    /**
    * 是否显示标题部分
    * @access   public
    * @var      int
    */
    var $mIsDispTitle = true;
    /**
    * 是否显示分页代码部分
    * @access   public
    * @var      int
    */
    var $mIsDispIndex = true;
    /**
    * 是否显示表头
    * @access   public
    * @var      int
    */
    var $mIsDispHead = true;
    /**
    * 当前页数
    * @access   public
    * @var      int
    */
    var $mCurPage = 1;
    /**
    * 每页显示多少条记录
    * @access   public
    * @var      int
    */
    var $mRowsPerPage = 20;
    /**
    * 一共有多少条记录
    * @access   public
    * @var      int
    */
    var $mTotalRows = 0;
    /**
    * 分页代码将要链接到的页面地址
    *
    * 此参数如省略，默认为$_SERVER['REQUEST_URI']，带参数的当前页
    * @access   public
    * @var      string
    */
    var $mSubmitUrl = '';
    /**
    * 最终生成的HTML字符串
    * @access   private
    * @var      string
    */
    var $mHtmlStr = '';
    /**
    * 模板变量，指向在构造函数中传入的全局模板变量
    * @access   private
    * @var      array
    */
    var $mrTpl;
    /**
    * 数组变量，指向要显示数据存放的数组，其格式见类说明
    * @access   private
    * @var      array
    */
    var $mrAr;

    // {{{ 列表显示样式等

    /**
     * 表格的宽度
     * @access  private
     * @var     string
     */
    var $mTableWidth = '80%';

    /**
     * 表格的背景颜色
     * @access  private
     * @var     string
     */
    var $mTableBgcolor = '#ffffff';

    /**
     * 表格边框的宽度，一般为2px
     * @access  private
     * @var     string
     */
    var $mTableBorderWidth = '2px';

    /**
     * 表格边框的颜色
     * @access  private
     * @var     string
     */
    var $mTableBorderColor = '#006699';

    /**
     * 表格边框的线型
     * @access  private
     * @var     string
     */
    var $mTableBorderLineStyle = 'solid';

    /**
     * 表格表头部分颜色
     * @access  private
     * @var     string
     */
    var $mThColor = '#FFA34F';

    /**
     * 表格表头部分字体大小
     * @access  private
     * @var     string
     */
    var $mThFontSize = '9pt';

    /**
     * 表格表头部分字体粗细
     * @access  private
     * @var     string
     */
    var $mThFontWeight = 'bold';

    /**
     * 表格表头部分背景颜色
     * @access  private
     * @var     string
     */
    var $mThBgcolor = '#006699';

    /**
     * 表格表头部分高度
     * @access  private
     * @var     string
     */
    var $mThHeight = '25px';

    /**
     * 表格行背景选用颜色1
     * @access  private
     * @var     string
     */
    var $mTrBgcolor1 = '#EFEFEF';

    /**
     * 表格行背景选用颜色2
     * @access  private
     * @var     string
     */
    var $mTrBgcolor2 = '#DEE3E7';

    /**
     * 表格行背景鼠标指向时的颜色
     * @access  private
     * @var     string
     */
    var $mTrPointedColor = '#CCFFCC';

    /**
     * 表格行背景被标记时的颜色
     * @access  private
     * @var     string
     */
    var $mTrMarkedColor = '#FFCC99';

    /**
     * 单元格线型
     * @access  private
     * @var     string
     */
    var $mTdLineStyle = 'solid';

    /**
     * 单元格高度
     * @access  private
     * @var     string
     */
    var $mTdHeight = '20px';

	// }}}

	/**
	 * 模板文件的路径
	 * @access	private
	 * @var		string
	 */
	var $mTemplatePath = 'class/disp_list_table.html';


    /**
    * 类构造函数
    *
    * @param    array   &$tpl   传入的模板变量，要赋值给{@link $mrTpl}。
    * @param    array   &$ar    传入的数组变量，要赋值给{@link $mrAr}。
    */
    function DispListTable(&$tpl, &$ar)
    {
        $this->mrTpl = &$tpl;
		$this->SetData($ar);
    } // end of function DispListTable


    /**
    * 设置列表中的数据，当new一个实例后，要显示多个列表时，就要通过设置新的列表中的数据和改变相应的类变量来实现。而此时模板变量不变（其值会改变）。
    * @access   public
    * @param    object  &$ar    传入的数组变量，要赋值给{@link $mrAr}。
    */
    function SetData(&$ar)
    {
        //$this->mrAr = &$ar;
		//将输入的数组转换成用数字作为索引的，因为SMARTY不支持ASSOC索引
		$this->mrAr = array();
        if (empty($ar))
        {
            return(false);
        }
		foreach ($ar as $key=>$val)
		{
		    array_push($this->mrAr, $val);
		}
		//
		$this->mTotalRows = count($this->mrAr);
    } // end of function SetData

    /**
    * 显示最终表格
    *
    * @access   public
	* @param	boolean	$isDirectOutput	是否直接输出
    */
    function Disp($isDirectOutput = true)
    {
        $this->GerHtml(!$isDirectOutput);
        if ($isDirectOutput)
        {
            echo($this->mHtmlStr);
        }
    } // end function Disp

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
        if (1 > $total_pages)           { $total_pages = 1; }
        if (1 > $this->mCurPage)               { $this->mCurPage = 1;     }
        if ($this->mCurPage > $total_pages)    { $this->mCurPage = $total_pages; }
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
    } // end function GetIndex

    /**
    * 生成最终的HTML代码
    *
    * @access   private
	* @param	boolean	$isDirectOutput	是否直接输出
    * @return   string
    */
    function GerHtml($isDirectOutput = false)
    {
        $i_n = count($this->mrAr);
        if (1 > $i_n)
        {
            $this->mHtmlStr = '<p align="center">没有检索到任何数据。</p>';
			return(false);
        }

        $s_index = $this->GetIndex();

		if ( true == $this->mListClearAllAssign )
		{
			$this->mrTpl->clear_all_assign();
		}
		$this->mrTpl->assign('Title', $this->mListTitle);
		$this->mrTpl->assign('Data',$this->mrAr);
		$style = array();
		$style['table_width']				= $this->mTableWidth;
		$style['table_bgcolor']				= $this->mTableBgcolor;
		$style['table_border_width']		= $this->mTableBorderWidth;
		$style['table_border_color']		= $this->mTableBorderColor;
		$style['table_border_line_style']	= $this->mTableBorderLineStyle;
		$style['th_color']			= $this->mThColor;
		$style['th_font_size']		= $this->mThFontSize;
		$style['th_font_weight']	= $this->mThFontWeight;
		$style['th_bgcolor']		= $this->mThBgcolor;
		$style['th_height']			= $this->mThHeight;
		$style['tr_pointed_color']	= $this->mTrPointedColor;
		$style['tr_marked_color']	= $this->mTrMarkedColor;
		$style['tr_bgcolor1']		= $this->mTrBgcolor1;
		$style['tr_bgcolor2']		= $this->mTrBgcolor2;
		$style['td_line_style']		= $this->mTdLineStyle;
		$style['td_height']			= $this->mTdHeight;
		$this->mrTpl->assign('Style', $style);
		$this->mrTpl->assign('IsDispTitle',	$this->mIsDispTitle);
		$this->mrTpl->assign('IsDispHead',	$this->mIsDispHead);
		//分页索引
		if ($this->mIsDispIndex)
		{
		    $this->mrTpl->assign('Index',	$s_index);
		}
		else
		{
		    $this->mrTpl->assign('Index',	'');
		}
		if ($isDirectOutput)
		{
		    $this->mrTpl->display($this->mTemplatePath);
		}
		$this->mHtmlStr = $this->mrTpl->fetch($this->mTemplatePath);
/*
    var $mTrBgcolor1 = '#EFEFEF';
    var $mTrBgcolor2 = '#DEE3E7';
*/
		return($this->mHtmlStr);
/*
		$this->mrTpl->Clear();
        $this->mrTpl->set_file('lt', 'class/DispListTable.html');
        $this->mrTpl->set_blockF('lt', 'main');
        $this->mrTpl->set_blockF('main', 'title');
        $this->mrTpl->set_blockF('main', 'th');
        $this->mrTpl->set_blockF('main', 'tr');
        $this->mrTpl->set_blockF('th', 'thd');
        $this->mrTpl->set_blockF('tr', 'trd');

        $this->mrTpl->set_var('TITLE_TEXT', $this->mListTitle);
        $this->mrTpl->set_var('INDEX', (1 == $this->mIsDispIndex) ? $s_index : '');
        $this->mrTpl->shBlock('title', $this->mIsDispTitle);

        //表头
        $i_col = count($this->mrAr[0]);     //列数
        foreach ($this->mrAr[0] as $key=>$val)
        {
            $this->mrTpl->set_var('HEAD', $val);
            if (2 > $i_col)
            {
                $this->mrTpl->set_var('THC', 'thHead');
            }
            elseif (0 == $key)
            {
                $this->mrTpl->set_var('THC', 'thCornerL');
            }
            elseif (($i_col - 1) == $key)
            {
                $this->mrTpl->set_var('THC', 'thCornerR');
            }
            else
            {
                $this->mrTpl->set_var('THC', 'thHead');
            }
            $this->mrTpl->show_block('thd', 'o_', true);
        }
        $this->mrTpl->shBlock('th', $this->mIsDispHead);

        //表行
        $s_color = $this->mTrBgcolor2;
        for ($i = 1; $i < $i_n; $i++)
        {
            //用于表格行颜色区分与标记的行号和颜色
            $s_color = ($s_color == $this->mTrBgcolor1) ? $this->mTrBgcolor2 : $this->mTrBgcolor1;
            $this->mrTpl->set_var('tr_num',     $i);
            $this->mrTpl->set_var('tr_bgcolor', $s_color);

            $this->mrTpl->set_var('o_trd', '');
            for ($j = 0; $j < $i_col; $j++)
            {
                $this->mrTpl->set_var('VALUE', $this->mrAr[$i][$j]);
                $this->mrTpl->show_block('trd', 'o_', true);
            }
            $this->mrTpl->show_block('tr', 'o_', true);

        }

        //表格样式
        $this->mrTpl->set_var('table_width',                $this->mTableWidth);
        $this->mrTpl->set_var('table_bgcolor',              $this->mTableBgcolor);
        $this->mrTpl->set_var('table_border_width',         $this->mTableBorderWidth);
        $this->mrTpl->set_var('table_border_color',         $this->mTableBorderColor);
        $this->mrTpl->set_var('table_border_line_style',    $this->mTableBorderLineStyle);
        $this->mrTpl->set_var('th_color',                   $this->mThColor);
        $this->mrTpl->set_var('th_font_size',               $this->mThFontSize);
        $this->mrTpl->set_var('th_font_weight',             $this->mThFontWeight);
        $this->mrTpl->set_var('th_bgcolor',                 $this->mThBgcolor);
        $this->mrTpl->set_var('th_height',                  $this->mThHeight);
        $this->mrTpl->set_var('tr_pointed_color',           $this->mTrPointedColor);
        $this->mrTpl->set_var('tr_marked_color',            $this->mTrMarkedColor);
        $this->mrTpl->set_var('td_line_style',              $this->mTdLineStyle);
        $this->mrTpl->set_var('td_height',                  $this->mTdHeight);

        $this->mrTpl->show_block('main');

        $this->mHtmlStr = $this->mrTpl->get('o_main');
        return($this->mHtmlStr);
*/
    } // end function GetHtml


	/**
	 * 设置列表的模板文件
	 *
	 * @param	string	$fileName
	 */
	function SetTemplate($fileName)
	{
	    if (!empty($fileName) )
	    {
			$this->mTemplatePath = $fileName;
	    }
	} // end of function SetTemplate


	//--------------------------以下为从func_url.php中的URL函数----------------
    /**
    * 增加或设置/更改URL参数
    * @access   private
    * @see      UnsetUrlParam()
    * @param    string  $urlStr     要进行处理的URL地址
    * @param    string  $strName    要添加的参数等号左边，参数名
    * @param    string  $strValue   要添加的参数等号右边，参数值
    * @return   string
    */
    function SetUrlParam($urlStr, $strName, $strValue = '')
    {
        if (empty($strName) && empty($strValue))
        {
            return($urlStr);
        }
        $ar = $this->UrlToArray($urlStr);
        $i = 1;
        $is_found = 0;
        while (count($ar) > $i)
        {
            if ($strName == $ar[$i][0])
            {
                //已经有同名的参数了
                $ar[$i][1] = $strValue;
                $is_found ++;
            }
            $i++;
        }
        if (1 > $is_found)
        {
            //没有找到同名的参数
            array_push($ar, array($strName, $strValue));
        }
        return($this->ArrayToUrl($ar));
    } // end function SetUrlParam

    /**
    * 去掉URL参数
    * @access   private
    * @see      SetUrlParam()
    * @param    string  $urlStr     要进行处理的URL地址
    * @param    string  $strName    要删除的参数名
    * @return   string
    */
    function UnsetUrlParam($urlStr, $strName)
    {
        if (empty($strName))
        {
            return($urlStr);
        }
        $ar = $this->UrlToArray($urlStr);
        $ar2 = array();
        foreach ($ar as $key=>$val)
        {
            if ($strName == $val[0])
            {
                //找到指定的参数了，因为要删除他，所有就不复制，什么都不作
            }
            else
            {
                array_push($ar2, $val);
            }
        }
        return($this->ArrayToUrl($ar2));
    } // end function UnsetUrlParam

    /**
    * 将URL地址转换为数组
    *
    * {@source 4 21}
    * @access   private
    * @see      ArrayToUrl()
    * @param    string  $urlStr URL地址
    * @return   array
    */
    function UrlToArray($urlStr)
    {
        /*
        示例：转换 'http://localhost/index.php?a=1&b=&c=d.php?e=5&f=6'的结果为
        Array(
            [0] => Array(
                    [0] => http://localhost/working/hebca/source/test/index.php
                    [1] =>)
            [1] => Array(
                    [0] => a
                    [1] => 1)
            [2] => Array(
                    [0] => b
                    [1] =>)
            [3] => Array(
                    [0] => c
                    [1] => d.php?e
                    [2] => 5)
            [4] => Array(
                    [0] => f
                    [1] => 6) )
        */
        $ar = array();
        $str = $urlStr;
        $i = 0;
        //先寻找“?”
        $i = strpos($str, '?');
        if (1 > $i)
        {
            //URL中没有?，说明其没有参数
            array_push($ar, array($str, ''));
        }
        else
        {
            array_push($ar, array(substr($str, 0, $i), ''));
            $str = substr($str, $i + 1) . '&';
            //解析用&间隔的参数
            while (!empty($str))
            {
                $i = strpos($str, '&');
                if (0 < $i)
                {
                    $sub_str = substr($str, 0, $i);
                    //分析$sub_str这个等式
                    array_push($ar, split('[=]', $sub_str));
                    $str = substr($str, $i + 1);
                }
                else
                {
                    //剩下的不可识别字符
                    array_push($ar, array(substr($str, 0, 1), ''));
                    $str = substr($str, 1);
                }
            }
        }
        return($ar);
    } // end function UrlToArray

    /**
    * 将数组转换为URL地址
    *
    * 要进行转换的源数组必须是{@link UrlToArray()}结果的格式，即数组的第一个元素为文件地址，其余为各参数
    * @access   private
    * @see      UrlToArray()
    * @param    array   $ar 数组
    * @return   string
    */
    function ArrayToUrl(&$ar)
    {
        $i = count($ar);
        $s_url = '';
        if (0 < $i)
        {
            $s_url .= $ar[0][0] . '?';
            for ($j = 1; $j < $i; $j++)
            {
                foreach ($ar[$j] as $key=>$val)
                {
                    $s_url .= $val . '=';
                }
                $s_url = substr($s_url, 0, strlen($s_url) - 1);
                $s_url .= '&';
            }
            $s_url = substr($s_url, 0, strlen($s_url) - 1);
        }
        //去掉URL尾端的无效字符
        $s_url = str_replace('&=', '', $s_url);
        $s_url = ereg_replace ('[&]+$', '', $s_url);
        return($s_url);
    } // end function ArrayToUrl
    //-------------------------------------end---------------------------------

} // end class DispListTable

?>

<script type="text/javascript" language="javascript">
<!--
// {{{ 让表格的行具备标记功能，取自phpMyAdmin中的相关部分。

/**
 * This array is used to remember mark status of rows in browse mode
 */
var marked_row = new Array;

/**
 * Sets/unsets the pointer and marker in browse mode
 *
 * @param   object    the table row
 * @param   interger  the row number
 * @param   string    the action calling this script (over, out or click)
 * @param   string    the default background color
 * @param   string    the color to use for mouseover
 * @param   string    the color to use for marking a row
 *
 * @return  boolean  whether pointer is set or not
 */
function DispListTable_SetPointer(theRow, theRowNum, theAction, theDefaultColor, thePointerColor, theMarkColor)
{
    var theCells = null;

    // 1. Pointer and mark feature are disabled or the browser can't get the
    //    row -> exits
    if ((thePointerColor == '' && theMarkColor == '')
        || typeof(theRow.style) == 'undefined') {
        return false;
    }

    // 2. Gets the current row and exits if the browser can't get it
    if (typeof(document.getElementsByTagName) != 'undefined') {
        theCells = theRow.getElementsByTagName('td');
    }
    else if (typeof(theRow.cells) != 'undefined') {
        theCells = theRow.cells;
    }
    else {
        return false;
    }

    // 3. Gets the current color...
    var rowCellsCnt  = theCells.length;
    var domDetect    = null;
    var currentColor = null;
    var newColor     = null;
    // 3.1 ... with DOM compatible browsers except Opera that does not return
    //         valid values with "getAttribute"
    if (typeof(window.opera) == 'undefined'
        && typeof(theCells[0].getAttribute) != 'undefined') {
        currentColor = theCells[0].getAttribute('bgcolor');
        domDetect    = true;
    }
    // 3.2 ... with other browsers
    else {
        currentColor = theCells[0].style.backgroundColor;
        domDetect    = false;
    } // end 3

    // 4. Defines the new color
    // 4.1 Current color is the default one
    if (currentColor == ''
        || currentColor.toLowerCase() == theDefaultColor.toLowerCase()) {
        if (theAction == 'over' && thePointerColor != '') {
            newColor              = thePointerColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor              = theMarkColor;
        }
    }
    // 4.1.2 Current color is the pointer one
    else if (currentColor.toLowerCase() == thePointerColor.toLowerCase()
             && (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])) {
        if (theAction == 'out') {
            newColor              = theDefaultColor;
        }
        else if (theAction == 'click' && theMarkColor != '') {
            newColor              = theMarkColor;
            marked_row[theRowNum] = true;
        }
    }
    // 4.1.3 Current color is the marker one
    else if (currentColor.toLowerCase() == theMarkColor.toLowerCase()) {
        if (theAction == 'click') {
            newColor              = (thePointerColor != '')
                                  ? thePointerColor
                                  : theDefaultColor;
            marked_row[theRowNum] = (typeof(marked_row[theRowNum]) == 'undefined' || !marked_row[theRowNum])
                                  ? true
                                  : null;
        }
    } // end 4

    // 5. Sets the new color...
    if (newColor) {
        var c = null;
        // 5.1 ... with DOM compatible browsers except Opera
        if (domDetect) {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].setAttribute('bgcolor', newColor, 0);
            } // end for
        }
        // 5.2 ... with other browsers
        else {
            for (c = 0; c < rowCellsCnt; c++) {
                theCells[c].style.backgroundColor = newColor;
            }
        }
    } // end 5

    return true;
} // end of the 'DispListTable_SetPointer()' function

// }}}

//-->
</script>
