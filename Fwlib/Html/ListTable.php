<?php
namespace Fwlib\Html;

use Fwlib\Base\AbstractAutoNewConfig;
use Fwlib\Util\ArrayUtil;
use Fwlib\Util\HttpUtil;

/**
 * Html generator: list table
 *
 * Give table head, data and other necessary information, generate table html.
 *
 * Some html/style operation use jQuery.
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
     * If column in data and title doesn't match, fitMode option will determin
     * which columns will be used, its value defines here.
     */
    // Fit to title, drop data whose index is not in title index
    const FIT_TO_TITLE  = 0;

    // Fit to data, drop title whose index is not in data index
    const FIT_TO_DATA   = 1;

    // Fit to insection of title and data, got fewest column
    const FIT_INSECTION = 2;

    // Fit to union of title and data, got mostest column
    const FIT_UNION     = 3;

    /**
     * Information generated in treatment
     *
     * Default value is given according to default config.
     *
     * @var array
     */
    protected $info = array(
        // Class for root element
        'class'         => 'ListTable',
        // Class prefix for non-root elements
        'classPrefix'   => 'ListTable-',
        // Id of root element
        'id'            => 'ListTable-1',
        // Id prefix for non-root elements
        'idPrefix'      => 'ListTable-1-',
        // Current page number
        'page'          => 1,
        // Max page number
        'pageMax'       => 1,
        // Parsed pager text
        'pagerTextBody' => '',
        'totalRows'     => -1,
    );

    /**
     * List data array
     *
     * @var array
     */
    protected $listData = array();

    /**
     * List title array, show as table title
     *
     * @var array
     */
    protected $listTitle = array();

    /**
     * Page url param array
     *
     * @var array
     */
    protected $param = array();

    /**
     * Template object
     *
     * @var Fwlib\Bridge\Smarty
     */
    protected $tpl = null;

    /**
     * Prefix of var assigned to template
     *
     * Should keep sync with var name in template file, in most case this
     * property need not change.
     *
     * @var string
     */
    protected $tplVarPrefix = 'listTable';

    /**
     * Array of url, computed for show in tpl
     *
     * {
     *  base      : Original page url
     *  form      : Page jump form target url
     *  obCur     : Current orderby link (modified)
     *  obReverse : Reverse order orderby link (modified)
     *  pageFirst : First page link
     *  pageLast  : Last page link
     *  pageNext  : Next page link
     *  pagePrev  : Prev page link
     * }
     *
     * @var array
     */
    protected $url = array(
        'base'      => '',
        'form'      => '',
        'obCur'     => '',
        'obReverse' => '',
        'pageFirst' => '',
        'pageLast'  => '',
        'pageNext'  => '',
        'pagePrev'  => '',
    );


    /**
     * Constructor
     *
     * If there are multiple list to show in one page, MUST set 'id' in
     * config.
     *
     * @param   Fwlib\Bridge\Smarty $tpl
     * @param   array               $config
     */
    public function __construct($tpl, $config = null)
    {
        parent::__construct();

        $this->tpl = $tpl;

        // Config will effect setData, so set it first.
        $this->setConfig($config);

        $this->tpl->assignByRef("{$this->tplVarPrefix}Config", $this->config);
        $this->tpl->assignByRef("{$this->tplVarPrefix}Info", $this->info);
        $this->tpl->assignByRef("{$this->tplVarPrefix}Url", $this->url);
    }


    /**
     * Fit data and title if their key are different
     *
     * Notice: data have multi row(2 dim), title have only 1 row(1 dim).
     *
     * @see $config['fitMode']
     */
    protected function fitDataWithTitle()
    {
        if (empty($this->listData) || empty($this->listTitle)) {
            return;
        }

        // Will compare by array keys, for data, use it's first/current row
        $keyOfData = array_keys(current($this->listData));
        $keyOfTitle = array_keys($this->listTitle);

        if ($keyOfData == $keyOfTitle) {
            return;
        }


        // Store result
        $ar_title = array();
        $ar_data = array();

        switch ($this->config['fitMode']) {
            case $this::FIT_TO_TITLE:
                // Int index and string are difference
                // In common, we check only title's index type
                // Int index, can only fit by index position
                if (0 === $keyOfTitle[0]) {
                    $ar_title = &$this->listTitle;
                    foreach ($keyOfTitle as $k => $v) {
                        foreach ($this->listData as $idx => $row) {
                            if (isset($row[$keyOfData[$k]])) {
                                $ar_data[$idx][$keyOfData[$k]] = &$row[$keyOfData[$k]];
                            } else {
                                $ar_data[$idx][$keyOfData[$k]] = $this->config['fitEmpty'];
                            }
                        }
                    }
                } else {
                    $ar_title = &$this->listTitle;
                    foreach ($keyOfTitle as $k => $v) {
                        foreach ($this->listData as $idx => $row) {
                            if (isset($row[$v])) {
                                $ar_data[$idx][$v] = &$row[$v];
                            } else {
                                $ar_data[$idx][$v] = $this->config['fitEmpty'];
                            }
                        }
                    }
                }
                break;

            case $this::FIT_TO_DATA:
                // Int index, can only fit by index position
                if (0 === $keyOfTitle[0]) {
                    $ar_data = &$this->listData;
                    foreach ($keyOfData as $k => $v) {
                        if (isset($keyOfTitle[$k])) {
                            $ar_title[$k] = &$this->listTitle[$k];
                        } else {
                            // Use data's index name
                            $ar_title[$k] = $v;
                        }
                    }
                } else {
                    $ar_data = &$this->listData;
                    foreach ($keyOfData as $k => $v) {
                        if (isset($this->listTitle[$v])) {
                            $ar_title[$v] = &$this->listTitle[$v];
                        } else {
                            $ar_title[$v] = $v;
                        }
                    }
                }
                break;

            case $this::FIT_INSECTION:
                // Cut title first, then fit to title
                // Cut title:
                $ar_title = &$this->listTitle;
                if (0 === $keyOfTitle[0]) {
                    // Int indexed
                    // Remove title if title has more items than data
                    for ($i = count($keyOfData); $i < count($keyOfTitle); $i++) {
                        unset($ar_title[$i]);
                    }
                } else {
                    // String indexed
                    // Remove item in title which not in data
                    foreach ($keyOfTitle as $k => $v) {
                        if (!in_array($v, $keyOfData)) {
                            unset($ar_title[$v]);
                        }
                    }
                }
                // Then use function itself to fit data to cutted title
                $this->config['fitMode'] = 0;
                $this->fitDataWithTitle();
                $this->config['fitMode'] = 2;
                $ar_data = &$this->listData;
                $ar_title = &$this->listTitle;
                break;

            case $this::FIT_UNION:
                // Fill title first, then fit to title
                // Fill title:
                if (0 === $keyOfTitle[0]) {
                    // Add as append
                    $ar_title = &$this->listTitle;
                    // Int indexed
                    // Add title if title has fewer items than data
                    for ($i = count($keyOfTitle); $i < count($keyOfData); $i++) {
                        // Can only use field name in data
                        $ar_title[$i] = $keyOfData[$i];
                    }
                } else {
                    // Add as insert
                    // need to merge keys first to keep order
                    $keys_merge = array_merge($keyOfTitle, $keyOfData);
                    foreach ($keys_merge as $k => $v) {
                        if (in_array($v, $keyOfTitle)) {
                            $ar_title[$v] = $this->listTitle[$v];
                        } else {
                            // Title items is fewer, need to fill
                            // These infact is keys from data,
                            // because of merge, so we can use $v directly
                            $ar_title[$v] = $v;
                        }
                    }
                }
                $this->listTitle = &$ar_title;
                // Then use function itself to fit data to cutted title
                $this->config['fitMode'] = 0;
                $this->fitDataWithTitle();
                $this->config['fitMode'] = 2;
                $ar_data = &$this->listData;
                $ar_title = &$this->listTitle;
                break;
            default:
        }


        // Data write back
        $this->listData = &$ar_data;
        $this->listTitle = &$ar_title;
    }


    /**
     * Generate url with some modification
     *
     * @param   array   $modify
     * @param   array   $exclude
     * @return  string
     * @see $this->param
     */
    protected function genUrl($modify = null, $exclude = null)
    {
        $param = $this->param;

        foreach ((array)$modify as $k => $v) {
            $param[addslashes($k)] = addslashes($v);
        }

        foreach ((array)$exclude as $k) {
            unset($param[$k]);
        }

        $url = '';
        foreach ($param as $k => $v) {
            $url .= "&$k=$v";
        }
        if (!empty($url)) {
            $url{0} = '?';
        }
        $url = $this->url['base'] . $url;

        return $url;
    }


    /**
     * Get full output html
     *
     * @return  string
     */
    public function getHtml()
    {
        $this->readRequest();
        $this->setPager();

        return $this->tpl->fetch($this->config['tpl']);
    }


    /**
     * Get info about some plistTitle of query sql
     * what can directly use in SqlGenerator, eg: limit, orderby
     *
     * @return  array
     * @see SqlGenerator
     */
    public function getSqlInfo()
    {
        $ar = array();

        $ar['LIMIT'] = $this->config['pageSize'] * ($this->info['page'] - 1)
            . ', ' . $this->config['pageSize'];

        if (!empty($this->config['orderby'])) {
            // orderby_idx is column name
            $ar['ORDERBY'] = $this->config['orderby']
                . ' ' . $this->config['orderbyDir'];
        }

        return $ar;
    }


    /**
     * Get info about some plistTitle of query sql from url $_REQUEST
     * what can directly use in SqlGenerator, eg: limit, orderby
     *
     * :TODO: Check and merge to readRequest()
     *
     * @return  array
     * @see SqlGenerator
     */
    public function getSqlInfoFromUrl()
    {
        $ar = array();

        // Limit
        //$this->setPage();   // :TODO: Necessary ?
        $page = $this->info['page'];
        $ar['LIMIT'] = ($page - 1) * $this->config['pageSize']
            . ', ' . $this->config['pageSize'];

        // Orderby
        $s = $this->config['paramOrderby'];
        $s_idx = HttpUtil::getRequest($_REQUEST, $s);
        if (0 < strlen($s_idx)) {
            // Orderby enabled
            $s_dir = HttpUtil::getRequest($_REQUEST, $s . 'Dir', 'ASC');
            $ar['ORDERBY'] = $s_idx . ' ' . $s_dir;
        }

        return $ar;
    }


    /**
     * Read http param and parse
     *
     * @return  $this
     */
    protected function readRequest()
    {
        $this->param = &$_GET;
        if (!get_magic_quotes_gpc()) {
            array_walk($this->param, 'addslashes');
        }

        // :NOTICE: Will got only url of backend if ProxyPass actived
        // Can treat as below before new ListTable obj.
        /*
        $ar = array('/needless1/', '/needless1/');
        $_SERVER['REQUEST_URI'] = str_replace($ar, '/', $_SERVER['REQUEST_URI']);
        $_SERVER['SCRIPT_NAME'] = str_replace($ar, '/', $_SERVER['SCRIPT_NAME']);
        */

        $this->url['base'] = HttpUtil::getSelfUrl(false);

        $page = ArrayUtil::getIdx($this->param, $this->config['paramPage'], 1);
        $this->setPage($page);

        if (isset($this->param[$this->config['paramOrderby']])) {
            // Orderby is enabled
            $dir = ArrayUtil::getIdx(
                $this->param,
                $this->config['paramOrderby'] . 'Dir',
                'ASC'
            );
            $this->setOrderby(
                $this->param[$this->config['paramOrderby']],
                $dir
            );
        }

        return $this;
    }


    /**
     * Set default config
     */
    protected function setConfigDefault()
    {
        $this->config->set(
            array(
                // Notice: this is NOT actual class and id used in template,
                // see $this->info and $this->setId() for details.

                // Classname for root element, and prefix of inherit elements
                // Should not be empty.
                'class'             => 'ListTable',
                // Id of this list, default 1, will use class as prefix if not empty.
                // Can be string or integer, should not be empty.
                'id'                => 1,
                //'code_prefix'       => 'fl_lt',     // Used in id/class in html and css.


                // Color schema: light blue
                // Color should assign using CSS as possible as it could,
                // below color will use jQuery to assign.
                // :TODO: Use pure CSS ?
                'enableColorTh'     => true,
                'colorBgTh'         => '#d0dcff',   // 表头(thead)

                'enableColorTr'     => true,
                'colorBgTrEven'     => '#fff',      // 偶数行
                'colorBgTrOdd'      => '#eef2ff',   // 奇数行，tbody后从0开始算

                'enableColorHover'  => true,
                'colorBgTrHover'    => '#e3e3de',   // 鼠标指向时变色
                //'color_bg_th'       => '#d0dcff',   // 表头(thead)
                //'color_bg_tr_even'  => '#fff',      // 偶数行
                //'color_bg_tr_hover' => '#e3e3de',   // 鼠标指向时变色
                //'color_bg_tr_odd'   => '#eef2ff',   // 奇数行，tbody后从0开始算


                // Data and title fit mode, default FIT_TO_TITLE
                'fitMode'           => $this::FIT_TO_TITLE,

                // If a value in data is empty, display with this value.
                // Not for title, which will use field name.
                'fitEmpty'          => '&nbsp;',
                //'fit_data_title'    => 0,
                //'fit_empty'         => '&nbsp;',


                // Which column to orderby, empty to disable orderby feature
                'orderby'           => '',
                // Orderby direction, ASC or DESC
                'orderbyDir'        => 'ASC',
                // Get param for orderby
                'paramOrderby'      => 'ob',
                // Preserved, will be auto generated if orderby enabled
                'orderbyText'       => '',
                // Orderby text/symbol for ASC and DESC
                'orderbyTextAsc'    => '↑',
                'orderbyTextDesc'   => '↓',
                // More orderby symbol
                // &#8592 = ← &#8593 = ↑ &#8594 = → &#8595 = ↓
                // &#8710 = ∆ &#8711 = ∇
                //'orderby'           => 0,           // 0=off, 1=on
                //'orderby_dir'       => 'ASC',
                //'orderby_idx'       => '',          // Idx of th ar
                //'orderby_param'     => 'o',
                //'orderby_text'      => '',
                //'orderby_text_asc'  => '↑',
                //'orderby_text_desc' => '↓',


                // Get param for identify current page
                'paramPage'         => 'p',
                // How many rows to display each page
                'pageSize'          => 10,
                //'page_cur'          => 1,
                //'paramPage'        => 'p',// Used in url to set page no.
                //'page_size'         => 10,


                // Show pager above list table
                'pagerAbove'        => true,
                // Show pager below list table
                'pagerBelow'        => true,
                // Pager message template
                'pagerTextFirst'    => '首页',
                'pagerTextPrev'     => '上一页',
                'pagerTextNext'     => '下一页',
                'pagerTextLast'     => '尾页',
                'pagerTextBody'     =>
                    '共{totalRows}条记录，每页显示{pageSize}条，当前为第{page}/{pageMax}页',
                'pagerTextJump1'    => '转到第',
                'pagerTextJump2'    => '页',
                'pagerTextJumpButton'   => '转',
                // Spacer between pager text plistTitles
                'pagerTextSpacer'   => ' | ',
                //'pager'             => false,       // Is or not use pager
                //'pager_bottom'      => true,        // Is or not use pager bottom, used when pager=true
                // This is a message template
                // When display, use key append by '_value'
                //'pager_text_cur'    =>
                //    '共{rows_total}条记录，每页显示{page_size}条，当前为第{page_cur}/{page_max}页',
                //'pager_text_first'  => '首页',
                //'pager_text_goto1'  => '转到第',
                //'pager_text_goto2'  => '页',
                //'pager_text_goto3'  => '转',
                //'pager_text_last'   => '尾页',
                //'pager_text_next'   => '下一页',
                //'pager_text_prev'   => '上一页',
                //'pager_text_spacer' => ' | ',       // To be between below texts.
                //'pager_top'         => true,        // Is or not use pager top, used when pager=true


                //'rows_total'        => 0,
                // Template file path
                'tpl'               => __DIR__ . '/list-table.tpl',

                // Add custom string in td/th/tr tag, eg: nowrap='nowrap'
                // td/th can use index same with data array index,
                // tr can use int index which's value is string too.
                // :TODO: tr int index is converted to string ?
                // For tr of th row, use th instead.
                'tdAdd'             => array(),
                'thAdd'             => array(),
                'trAdd'             => array(),
                //'td_add'            => array(),
                //'th_add'            => array(),
                //'tr_add'            => array(),
            )
        );
    }


    /**
     * Set list data and title
     *
     * @param   array   $listData
     * @param   array   $listTitle
     * @param   boolean $updateTotalRows
     * @return  $this
     */
    public function setData($listData, $listTitle = null, $updateTotalRows = false)
    {
        $this->listData = $listData;
        if ($updateTotalRows) {
            $this->info['totalRows'] = count($listData);
        } elseif (-1 == $this->info['totalRows']) {
            // Count total rows from data
            $this->info['totalRows'] = count($listData);
        }

        if (!is_null($listTitle)) {
            $this->listTitle = $listTitle;
        }

        // Same number of items maybe index diff, so always do fit.
        $this->fitDataWithTitle();

        $this->tpl->assignByRef("{$this->tplVarPrefix}Data", $this->listData);
        $this->tpl->assignByRef("{$this->tplVarPrefix}Title", $this->listTitle);

        return $this;
    }


    /**
     * Set id and class of list table
     *
     * Id and class should not have space or other special chars not allowed
     * by js and css in it.
     *
     * @param   string  $id
     * @param   string  $class
     * @return  $this
     */
    public function setId($id, $class = null)
    {
        $class = trim($class);
        // Class should not be empty
        if (empty($class)) {
            $class = $this->config['class'];    // For later use
        } else {
            $this->config['class'] = $class;
        }
        $this->info['class'] = $class;
        $this->info['classPrefix'] = $class . '-';


        $id = trim($id);
        if (0 == strlen($id)) {     // Avoid 0, which is empty
            $id = 1;
        }
        $this->config['id'] = $id;
        $this->info['idPrefix'] = $this->info['classPrefix'] . $id . '-';
        $this->info['id'] = $this->info['classPrefix'] . $id;


        // Change paramPage, eg: p1, pa
        $this->config['paramPage'] = 'p' . $id;
        // Useless, readRequest() will call it
        //$this->setPage();

        // Change orderby param
        $this->config['paramOrderby'] = 'ob' . $id;

        return $this;
    }


    /**
     * Set orderby info
     *
     * Didn't validate orderby key exists in data or title array.
     *
     * @param   mixed   $key
     * @param   string  $dir    ASC/DESC
     */
    public function setOrderby($key, $dir = 'ASC')
    {
        $this->config['orderby'] = $key;

        $dir = strtoupper($dir);
        if ('ASC' == $dir) {
            $dirReverse = 'DESC';
            $this->config['orderbyDir'] = 'ASC';
            $this->config['orderbyText'] = $this->config['orderbyTextAsc'];
        } else {
            $dirReverse = 'ASC';
            $this->config['orderbyDir'] = 'DESC';
            $this->config['orderbyText'] = $this->config['orderbyTextDesc'];
        }

        // Url param
        $ob = $this->config['paramOrderby'];
        // Orderby index is appended in template by each th, remove here
        $this->url['obCur'] = $this->genUrl(
            array("{$ob}Dir" => $dirReverse),
            array($ob)
        );

        // Reverse orderby will clear page param
        $this->url['obReverse'] = $this->genUrl(
            array("{$ob}Dir" => $dir),
            array($ob, $this->config['paramPage'])
        );
    }


    /**
     * Check and set current page
     *
     * @param   int     $page   Page num param come from outer
     * @return  $this
     */
    protected function setPage($page = null)
    {
        if (is_null($page)) {
            $page = ArrayUtil::getIdx(
                $this->param,
                $this->config['paramPage'],
                1
            );
        }
        $page = intval($page);


        // Compare with min and max page number
        if (1 > $page) {
            $page = 1;
        }
        if (0 < $this->info['totalRows']
            && 0 < $this->config['pageSize']
        ) {
            $this->info['pageMax'] =
                ceil($this->info['totalRows'] / $this->config['pageSize']);
            $page = min($page, $this->info['pageMax']);
        }


        $this->info['page'] = $page;
        return $this;
    }


    /**
     * Set pager info
     *
     * Will execute even pager above/below are both disabled.
     *
     * @param   int     $totalRows
     * @param   int     $page       Current page number.
     * @return  $this
     * @see     $config
     */
    protected function setPager($totalRows = null, $page = null)
    {
        if (is_null($totalRows)) {
            $totalRows = $this->info['totalRows'];
        }
        if (is_null($page)) {
            $page = $this->info['page'];
        }

        // Some param needed
        $pageSize = $this->config['pageSize'];
        $pageMax = $this->info['pageMax'];


        // If data rows exceeds pageSize, trim it
        if (count($this->listData) > $pageSize) {
            // If page = 3/5, trim page 1, 2 first
            for ($i = 0; $i < ($page - 1) * $pageSize; $i ++) {
                unset($this->listData[$i]);
            }
            // Then trim page 4, 5
            for ($i = $page * $pageSize; $i < $pageMax * $pageSize; $i ++) {
                unset($this->listData[$i]);
            }
        }

        $this->info['pagerTextBody'] = str_replace(
            array('{page}', '{pageMax}', '{totalRows}', '{pageSize}'),
            array($page, $pageMax, $totalRows, $pageSize),
            $this->config['pagerTextBody']
        );

        // Generate url for pager
        //$this->url['base'] = GetSelfUrl(true);   // Move to readRequest()
        if (1 < $page) {
            // Not first page
            $this->url['pageFirst'] = $this->genUrl(
                array($this->config['paramPage'] => 1)
            );
            $this->url['pagePrev'] = $this->genUrl(
                array($this->config['paramPage'] => $page - 1)
            );
        } else {
            $this->url['pageFirst'] = '';
            $this->url['pagePrev'] = '';
        }
        if ($page < $pageMax) {
            // Not last page
            $this->url['pageNext'] = $this->genUrl(
                array($this->config['paramPage'] => $page + 1)
            );
            $this->url['pageLast'] = $this->genUrl(
                array($this->config['paramPage'] => $pageMax)
            );
        } else {
            $this->url['pageNext'] = '';
            $this->url['pageLast'] = '';
        }

        // Form submit target url
        $this->url['form'] = $this->genUrl(null, array($this->config['paramPage']));

        // Assign hidden input
        if (!empty($this->param)) {
            $s = '';
            foreach ($this->param as $k => $v) {
                $s .= "<input type='hidden' name='$k' value='$v' />\n";
            }
            $this->tpl->assign("{$this->tplVarPrefix}PagerHidden", $s);
        }

        return $this;
    }
}
