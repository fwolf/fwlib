<?php
namespace Fwlib\Html;

use Fwlib\Base\AbstractAutoNewConfig;
use Fwlib\Util\HttpUtil;

/**
 * Html generator: list table
 *
 * Give table head, data and other necessary information, generate table html.
 *
 * @codeCoverageIgnore
 *
 * @package     Fwlib\Html
 * @copyright   Copyright 2003-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2003-05-17 12:17:14
 */
class ListTable extends AbstractAutoNewConfig
{
    /**
     * Configuration
     *
     * <code>
     * color_bg_[th/tr_even/tr_odd]:
     *                  Colors of rows.
     * color_bg_tr_hover:
     *                  Change to this color when mouseover of row.(Not implement)
     * fit_data_title:  0=data fit title, cut data items who's index not
     *                  in title
     *                  1=title fit data.
     *                  2=fit to fewest, only items both have allowed.
     *                  3=fit to mostest, all items in title or data allowed.
     * fit_empty:       If an value in data is empty, set to this value.
     *                  Title will always set to field name in same situation.
     * code_prefix:     Prefix auto add before $sId, use to generate html.
     * page_cur:        Current page no, ONLY USED TO DISP PAGER.
     * page_param:      Param name of page number in URL,
     *                  Will change if $sId changed.
     * page_size:       Rows per page, ONLY USED TO DISP PAGER, havn't any
     *                  effect to list data.
     * rows_total:      Total rows, ONLY USED TO DISP PAGER.
     * tpl:             Smarty template file to use.
     * </code>
     * @var array
     */
    public $aCfg = array(
        // 浅蓝色配色方案
        // fwolflib-list-table = fl_lt
        'code_prefix'       => 'fl_lt',     // Used in id/class in html and css.
        'color_bg_th'       => '#d0dcff',   // 表头（第0行）
        'color_bg_tr_even'  => '#fff',      // 偶数行
        'color_bg_tr_hover' => '#e3e3de',   // 鼠标指向时变色
        'color_bg_tr_odd'   => '#eef2ff',   // 奇数行，tbody后从0开始算
        'fit_data_title'    => 0,
        'fit_empty'         => '&nbsp;',
        'orderby'           => 0,           // 0=off, 1=on
        'orderby_dir'       => 'asc',
        'orderby_idx'       => '',          // Idx of th ar
        'orderby_param'     => 'o',
        'orderby_text'      => '',
        // &#8592 = ← &#8593 = ↑ &#8594 = → &#8595 = ↓
        // &#8710 = ∆ &#8711 = ∇
        'orderby_text_asc'  => '↑',
        'orderby_text_desc' => '↓',
        'page_cur'          => 1,
        'page_param'        => 'p',// Used in url to set page no.
        'page_size'         => 10,
        'pager'             => false,       // Is or not use pager
        'pager_bottom'      => true,        // Is or not use pager bottom, used when pager=true
        // This is a message template
        // When display, use key append by '_value'
        'pager_text_cur'    =>
            '共{rows_total}条记录，每页显示{page_size}条，当前为第{page_cur}/{page_max}页',

        'pager_text_first'  => '首页',
        'pager_text_goto1'  => '转到第',
        'pager_text_goto2'  => '页',
        'pager_text_goto3'  => '转',
        'pager_text_last'   => '尾页',
        'pager_text_next'   => '下一页',
        'pager_text_prev'   => '上一页',
        'pager_text_spacer' => ' | ',       // To be between below texts.
        'pager_top'         => true,        // Is or not use pager top, used when pager=true
        'rows_total'        => 0,
        'tpl'               => 'list-table.tpl',

        // Add custom string in td/th/tr tag, eg: nowrap="nowrap"
        // td/th can use index same with data array index,
        // tr can use int index which's value is string too.
        // For tr of th row, use th instead.
        'td_add'            => array(),
        'th_add'            => array(),
        'tr_add'            => array(),
        );

    /**
     * 数组变量，指向要显示数据存放的数组，其格式见类说明
     * @var array
     */
    protected $aData = array();

    /**
     * Page url param array.
     * @var array
     */
    protected $aParam = array();

    /**
     * Title of data, used as table title.
     * @var array
     */
    protected $aTitle = array();

    /**
     * Array of url, for links to display in tpl
     * <code>
     * array(
     *  base    => Original page url
     *  o_cur   => Cur orderby link(modified)
     *  o_other => Other orderby link(modified)
     *  p_first => First page link
     *  p_last  => Last page link
     *  p_next  => Next page link
     *  p_prev  => Prev page link
     * )
     * </code>
     * @var array
     */
    protected $aUrl = array(
        'base'          => '',
        'o_cur'         => '',
        'o_other'       => '',
        'p_first'       => '',
        'p_last'        => '',
        'p_next'        => '',
        'p_prev'        => '',
        );

    /**
     * 模板变量，指向在构造函数中传入的全局模板变量
     * @var object
     */
    protected $oTpl = null;

    /**
     * Class of this list in html, used with {@see $sId}
     *
     * Diff between $sClass and $sId:
     * $sClass has no prefix, while $sId has.
     * $sClass can be applyed css in project css file,
     *  while $sId can be applyed css inline in tpl file.
     * @var string
     */
    protected $sClass = 'fl_lt';

    /**
     * Identify of this list,
     * Also used in html, as div id property.
     * @var string
     */
    protected $sId = 'fl_lt';


    /**
     * Construct
     *
     * $ard, $art can't use referenct because title and data value maybe
     * changed in treatment.
     * @param   object  &$tpl   Smarty object, will save as {@link $oTpl}.
     * @param   array   $ard    Data array, will save as {@link $aData}.
     * @param   array   $art    Title of list.
     * @param   string  $id     Identify of this list, while multi list
     *                          in page, this is needed.
     *                          Note: will be applyed prefix automatic
     *                          when write to $sId.
     * @param   array   &$conf  Configuration.
     */
    public function __construct(
        &$tpl,
        $ard = array(),
        $art = array(),
        $id = '',
        &$conf = array()
    ) {
        parent::__construct();

        $this->oTpl = $tpl;

        // Config will effect setData, so set it first.
        $this->setConfig($conf);
        $this->oTpl->assignByRef('lt_config', $this->aCfg);

        $this->setData($ard, $art);
        $this->setId($id);
    }


    /**
     * Fit data and title when their items count diff
     *
     * <code>
     * fit_data_title:  0=data fit title, cut data items who's index not
     *                  in title
     *                  1=title fit data.
     *                  2=fit to fewest, only items both have allowed.
     *                  3=fit to mostest, all items in title or data allowed.
     * </code>
     * Notice: data have multi row(2 dim), title have only 1 row(1 dim).
     * @see $aCfg['fit_data_title']
     */
    protected function fitDataTitle()
    {
        if (empty($this->aData) || empty($this->aTitle)) {
            return ;
        }

        // Store result
        $ar_title = array();
        $ar_data = array();

        // Will compare by array keys, data use it's first/current row
        //$keys_data = array_keys($this->aData[0]);
        $keys_data = array_keys(current($this->aData));
        $keys_title = array_keys($this->aTitle);

        switch ($this->aCfg['fit_data_title']) {
            case 0:
                // data fit to title

                // Int index and string are difference
                // In common, we check only title's index type
                // Int index, can only fit by index position
                if (0 === $keys_title[0]) {
                    $ar_title = &$this->aTitle;
                    foreach ($keys_title as $k => $v) {
                        foreach ($this->aData as $idx => $row) {
                            if (isset($row[$keys_data[$k]])) {
                                $ar_data[$idx][$keys_data[$k]] = &$row[$keys_data[$k]];
                            } else {
                                $ar_data[$idx][$keys_data[$k]] = $this->aCfg['fit_empty'];
                            }
                        }
                    }
                } else {
                    $ar_title = &$this->aTitle;
                    foreach ($keys_title as $k => $v) {
                        foreach ($this->aData as $idx => $row) {
                            if (isset($row[$v])) {
                                $ar_data[$idx][$v] = &$row[$v];
                            } else {
                                $ar_data[$idx][$v] = $this->aCfg['fit_empty'];
                            }
                        }
                    }
                }
                break;
            case 1:
                // title fit to data, inser empty title if havn't

                // Int index, can only fit by index position
                if (0 === $keys_title[0]) {
                    $ar_data = &$this->aData;
                    foreach ($keys_data as $k => $v) {
                        if (isset($keys_title[$k])) {
                            $ar_title[$k] = &$this->aTitle[$k];
                        } else {
                            // Use data's index name
                            $ar_title[$k] = $v;
                        }
                    }
                } else {
                    $ar_data = &$this->aData;
                    foreach ($keys_data as $k => $v) {
                        if (isset($this->aTitle[$v])) {
                            $ar_title[$v] = &$this->aTitle[$v];
                        } else {
                            $ar_title[$v] = $v;
                        }
                    }
                }
                break;
            case 2:
                // Fit to fewest
                // Cut title first, then fit to title
                // Cut title:
                $ar_title = &$this->aTitle;
                if (0 === $keys_title[0]) {
                    // Int indexed
                    // Remove title if title has more items than data
                    for ($i = count($keys_data); $i < count($keys_title); $i++) {
                        unset($ar_title[$i]);
                    }
                } else {
                    // String indexed
                    // Remove item in title which not in data
                    foreach ($keys_title as $k => $v) {
                        if (!in_array($v, $keys_data)) {
                            unset($ar_title[$v]);
                        }
                    }
                }
                // Then use function itself to fit data to cutted title
                $this->aCfg['fit_data_title'] = 0;
                $this->fitDataTitle();
                $this->aCfg['fit_data_title'] = 2;
                $ar_data = &$this->aData;
                $ar_title = &$this->aTitle;
                break;
            case 3:
                // Fit to mostest
                // Fill title first, then fit to title
                // Fill title:
                if (0 === $keys_title[0]) {
                    // Add as append
                    $ar_title = &$this->aTitle;
                    // Int indexed
                    // Add title if title has fewer items than data
                    for ($i = count($keys_title); $i < count($keys_data); $i++) {
                        // Can only use field name in data
                        $ar_title[$i] = $keys_data[$i];
                    }
                } else {
                    // Add as insert
                    // need to merge keys first to keep order
                    $keys_merge = array_merge($keys_title, $keys_data);
                    foreach ($keys_merge as $k => $v) {
                        if (in_array($v, $keys_title)) {
                            $ar_title[$v] = $this->aTitle[$v];
                        } else {
                            // Title items is fewer, need to fill
                            // These infact is keys from data,
                            // because of merge, so we can use $v directly
                            $ar_title[$v] = $v;
                        }
                    }
                }
                $this->aTitle = &$ar_title;
                // Then use function itself to fit data to cutted title
                $this->aCfg['fit_data_title'] = 0;
                $this->fitDataTitle();
                $this->aCfg['fit_data_title'] = 2;
                $ar_data = &$this->aData;
                $ar_title = &$this->aTitle;
                break;
            default:
        }


        // Data write back
        //var_dump($ar_data);
        $this->aData = &$ar_data;
        $this->aTitle = &$ar_title;
    }


    /**
     * Get full output html
     * @return  string
     */
    public function getHtml()
    {
        return $this->oTpl->fetch($this->aCfg['tpl']);
    }


    /**
     * Get http GET param.
     * @return  array
     */
    public function getParam()
    {
        $this->aParam = &$_GET;
        if (!empty($this->aParam) && !get_magic_quotes_gpc()) {
            foreach ($this->aParam as $k => $v) {
                $this->aParam[$k] = addslashes($v);
            }
        }

        // :NOTICE: Will got only url of backend if ProxyPass actived
        // Can treat as below before new ListTable obj.
        /*
        $ar = array('/needless1/', '/needless1/');
        $_SERVER['REQUEST_URI'] = str_replace($ar, '/', $_SERVER['REQUEST_URI']);
        $_SERVER['SCRIPT_NAME'] = str_replace($ar, '/', $_SERVER['SCRIPT_NAME']);
        */
        $this->aUrl['base'] = HttpUtil::getSelfUrl(false);

        if (isset($this->aParam[$this->aCfg['page_param']])) {
            $this->parsePageCur($this->aParam[$this->aCfg['page_param']]);
        }

        // Orderby
        if (isset($this->aParam[$this->aCfg['orderby_param'] . '_idx'])) {
            $this->setOrderby(
                $this->aParam[$this->aCfg['orderby_param'] . '_idx'],
                $this->aParam[$this->aCfg['orderby_param'] . '_dir']
            );
        }

        return $this->aParam;
    }


    /**
     * Get info about some part of query sql
     * what can directly use in SqlGenerator, eg: limit, orderby
     *
     * @return  array
     * @see SqlGenerator
     */
    public function getSqlInfo()
    {
        $ar = array();

        $ar['LIMIT'] = $this->aCfg['page_size'] * ($this->aCfg['page_cur'] - 1)
            . ', ' . $this->aCfg['page_size'];

        if (1 == $this->aCfg['orderby']) {
            // orderby_idx is column name
            $ar['ORDERBY'] = $this->aCfg['orderby_idx']
                . ' ' . $this->aCfg['orderby_dir'];
        }

        return $ar;
    }


    /**
     * Get info about some part of query sql from url $_REQUEST
     * what can directly use in SqlGenerator, eg: limit, orderby
     *
     * @return  array
     * @see SqlGenerator
     */
    public function getSqlInfoFromUrl()
    {
        $ar = array();

        // Limit
        $i_page = $this->parsePageCur();
        $ar['LIMIT'] = ($i_page - 1) * $this->aCfg['page_size']
            . ', ' . $this->aCfg['page_size'];

        // Orderby
        $s = $this->aCfg['orderby_param'];
        $s_idx = HttpUtil::getRequest($_REQUEST, $s . '_idx');
        if (0 < strlen($s_idx)) {
            // Orderby enabled
            $s_dir = HttpUtil::getRequest($_REQUEST, $s . '_dir', 'asc');
            $ar['ORDERBY'] = $s_idx . ' ' . $s_dir;
        }

        return $ar;
    }


    /**
     * Parse & compute page_cur param
     *
     * @param   int $p  Page num param come from outer
     * @return  int
     */
    protected function parsePageCur($p = 0)
    {
        if (0 == $p) {
            // Read from GET prarm
            $i = HttpUtil::getRequest($_REQUEST, $this->aCfg['page_param']);
            // Special & dangous setting, use only if 1 LT in page
            $i1 = HttpUtil::getRequest($_REQUEST, 'p');
            if (!empty($i)) {
                $page_cur = $i;
            } elseif (!empty($i1)) {
                $page_cur = $i1;
            } else {
                $page_cur = 1;
            }
        } else {
            $page_cur = $p;
        }

        // Validate min and max
        // Min
        if (1 > $page_cur) {
            $page_cur = 1;
        }
        // Max
        if (0 < $this->aCfg['rows_total']
            && 0 < $this->aCfg['page_size']
        ) {
            $i = ceil($this->aCfg['rows_total'] / $this->aCfg['page_size']);
            if ($i < $page_cur) {
                $page_cur = intval($i);
            }
        }

        // Result
        $this->aCfg['page_cur'] = $page_cur;
        return $page_cur;
    }


    /**
     * set table data and title to display.
     * @param   array   &$ard   Data, will save as {@link $aData}.
     * @param   array   &$art   Title of list.
     */
    public function setData(&$ard = array(), &$art = array())
    {
        if (!empty($ard)) {
            $this->aData = $ard;
        }
        if (!empty($art)) {
            $this->aTitle = $art;
        }

        // Same number of items maybe index diff, so always do fit.
        $this->fitDataTitle();

        $this->oTpl->assignByRef('lt_data', $this->aData);
        $this->oTpl->assignByRef('lt_title', $this->aTitle);

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
    }


    /**
     * set identify and class of this list <div> in html
     * @param   string  $id
     * @param   string  $class
     * @return  string
     */
    public function setId($id, $class = '')
    {
        if (empty($id)) {
            $this->sId = $this->aCfg['code_prefix'];
        } else {
            $this->sId = $this->aCfg['code_prefix'] . '_' . $id;
        }
        if (!empty($class)) {
            $this->sClass = $class;
        } else {
            // On default, class = id
            $this->sClass = $this->sId;
        }
        $this->oTpl->assignByRef('lt_id', $this->sId);
        $this->oTpl->assignByRef('lt_class', $this->sClass);

        // Change page_param
        $this->aCfg['page_param'] = $this->sId . '_p';
        $this->parsePageCur();

        // Change orderby param
        $this->aCfg['orderby_param'] = $this->sId . '_o';

        // Find param by new Id
        $this->getParam();

        return $this->sId;
    }


    /**
     * set orderby info
     *
     * Didn't validate $idx to th array.
     * @param   mixed   $idx    Idx of th array
     * @param   string  $dir    asc/desc, lower letter only
     */
    public function setOrderby($idx, $dir = 'asc')
    {
        $this->aCfg['orderby'] = 1;

        // If had got orderby info from url, exit
        if (0 < strlen($this->aCfg['orderby_idx'])) {
            return;
        }

        $this->aCfg['orderby_idx'] = $idx;
        $dir = strtolower($dir);
        if ('asc' == $dir) {
            $dir_rev = 'desc';
            $this->aCfg['orderby_dir'] = 'asc';
            $this->aCfg['orderby_text']
                = $this->aCfg['orderby_text_asc'];
        } else {
            $dir_rev = 'asc';
            $this->aCfg['orderby_dir'] = 'desc';
            $this->aCfg['orderby_text']
                = $this->aCfg['orderby_text_desc'];
        }

        // Url param
        // Empty idx will fill in tpl
        // Keep value of $this->aParam first
        $ar = $this->aParam;
        $this->aUrl['o_cur'] = $this->setParam(
            array($this->aCfg['orderby_param'] . '_dir' => $dir_rev),
            array($this->aCfg['orderby_param'] . '_idx')
        );
        // Same with cur dir, or all new 'asc'
        $this->aUrl['o_other'] = $this->setParam(
            array($this->aCfg['orderby_param'] . '_dir'=> $this->aCfg['orderby_dir']),
            array($this->aCfg['orderby_param'] . '_idx')
        );
        // Restore value of $this->aParam
        $this->aParam = $ar;
        $this->oTpl->assignByRef('lt_url', $this->aUrl);
    }


    /**
     * set pager info
     *
     * Config data will also write to $aCfg, the difference with direct set config
     * is this will add more treatment about pager.
     * And use after setConfig()
     * @param   int     $rows_total Total row/record number
     * @param   int     $page_cur   Current displayed page, default is get from GET param
     *                              if fail, set to 1.
     * @see     $aCfg
     */
    public function setPager($rows_total = 0, $page_cur = 0)
    {
        // Enable pager disp
        $this->aCfg['pager'] = true;

        // Auto compute total rows if not assigned
        if (0 == $rows_total) {
            $rows_total = count($this->aData);
        }
        $this->aCfg['rows_total'] = $rows_total;

        // Some param needed
        $page_cur = $this->parsePageCur($page_cur);
        $page_size = $this->aCfg['page_size'];
        $page_max = ceil($rows_total / $page_size);

        // If data rows exceeds page_size, trim it
        if (count($this->aData) > $page_size) {
            // If page = 3/5, trim page 1, 2 first
            for ($i = 0; $i < ($page_cur - 1) * $page_size; $i ++) {
                unset($this->aData[$i]);
            }
            // Then trim page 4, 5
            for ($i = $page_cur * $page_size; $i < $page_max * $page_size; $i ++) {
                unset($this->aData[$i]);
            }
        }

        $this->aCfg['pager_text_cur_value'] = str_replace(
            array('{page_cur}', '{page_max}', '{rows_total}', '{page_size}'),
            array($page_cur, $page_max, $rows_total, $page_size),
            $this->aCfg['pager_text_cur']
        );

        // Generate url for pager
        //$this->aUrl['base'] = GetSelfUrl(true);   // Move to getParam()
        if (1 < $page_cur) {
            // Not first page
            //$this->aUrl['first'] = $this->aUrl['base'] . '&' . $this->sId
            //    . '-page_no=' . $page_cur;
            //$this->aUrl['prev'] = $this->aUrl['base'] . '&' . $this->sId
            //    . '-page_no=' . ($page_cur - 1);
            $this->aUrl['p_first'] = $this->setParam($this->aCfg['page_param'], 1);
            $this->aUrl['p_prev'] = $this->setParam($this->aCfg['page_param'], $page_cur - 1);
        } else {
            $this->aUrl['p_first'] = '';
            $this->aUrl['p_prev'] = '';
        }
        if ($page_cur < $page_max) {
            // Not last page
            //$this->aUrl['next'] = $this->aUrl['base'] . '&' . $this->sId
            //    . '-page_no=' . ($page_cur + 1);
            //$this->aUrl['last'] = $this->aUrl['base'] . '&' . $this->sId
            //    . '-page_no=' . $page_max;
            $this->aUrl['p_next'] = $this->setParam($this->aCfg['page_param'], $page_cur + 1);
            $this->aUrl['p_last'] = $this->setParam($this->aCfg['page_param'], $page_max);
        } else {
            $this->aUrl['p_next'] = '';
            $this->aUrl['p_last'] = '';
        }

        // Assign url to tpl
        $this->oTpl->assignByRef('lt_url', $this->aUrl);
        $this->oTpl->assign('lt_url_form', $this->setParam(array(), $this->aCfg['page_param']));

        // Assign hidden input
        if (!empty($this->aParam)) {
            $s = '';
            foreach ($this->aParam as $k => $v) {
                $s .= "<input type=\"hidden\" name=\"$k\" value=\"$v\" />\n";
            }
            $this->oTpl->assign('lt_url_form_hidden', $s);
        }

        // Add page_param deleted in above setParam
        // needed to display right form url in next table in this page
        $this->setParam($this->aCfg['page_param'], $page_cur);
    }


    /**
     * set url param, get the url
     *
     * If $k is string, then $v is string too and means $k=$v.
     * if $k is array, then $v is array to,
     * and values in $k/$v is added/removed from url param.
     * Always 'save' setting and return result url.
     *
     * @param   mixed   $k
     * @param   mixed   $v
     * @return  string
     * @see func/request.php::getParam()
     */
    public function setParam($k, $v = '')
    {
        if (!is_array($k) && !is_array($v)) {
            $this->aParam[addslashes($k)] = addslashes($v);
        }
        if (is_array($k)) {
            foreach ($k as $key => $val) {
                $this->aParam[addslashes($key)] = addslashes($val);
            }
            if (!is_array($v)) {
                $v = array($v);
            }
            foreach ($v as $val) {
                if (isset($this->aParam[$val])) {
                    unset($this->aParam[$val]);
                }
            }
        }

        // Generate url and return
        $s = '';
        foreach ($this->aParam as $k => $v) {
            $s .= "&$k=$v";
        }
        if (!empty($s)) {
            $s{0} = '?';
        }
        $s = $this->aUrl['base'] . $s;
        return $s;
    }
}
