<?php
namespace Fwlib\Html;

use Fwlib\Util\UtilContainer;

/**
 * Html generator: list table
 *
 * Give table head, data and other necessary information, generate table html.
 *
 * Some html/style operation use jQuery.
 *
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2003-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class ListTable
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
     * Config array
     *
     * @var array
     */
    protected $configs = array();

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
        // Orderby enabled column and default direction
        'orderByColumn' => array(),
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
     *  obCur     : Link on current orderBy head, to reverse order
     *  obOther   : Link on in-active orderBy head(their default dir add in tpl)
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
        'obOther'   => '',
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
     * @param   array               $configs
     */
    public function __construct($tpl, array $configs = array())
    {
        $this->tpl = $tpl;

        // Config will effect setData, so set it first.
        $this->setDefaultConfigs();
        $this->setConfigs($configs);

        $this->tpl->assignByRef("{$this->tplVarPrefix}Config", $this->configs);
        $this->tpl->assignByRef("{$this->tplVarPrefix}Info", $this->info);
        $this->tpl->assignByRef("{$this->tplVarPrefix}Url", $this->url);

        $this->tpl->assignByRef("{$this->tplVarPrefix}Data", $this->listData);
        $this->tpl->assignByRef("{$this->tplVarPrefix}Title", $this->listTitle);
    }


    /**
     * Fit each row in data with given keys
     *
     * If row index is not in given keys, it will be dropped.
     * If given keys is not in row index, it will be created with value
     * $this->configs['fitEmpty'].
     *
     * @param   array   $key
     */
    protected function fitData(array $key)
    {
        // Do search on first row for speed
        $keyAdd = array();
        $keyDel = array();
        reset($this->listData);
        $row = current($this->listData);

        // Drop key not in key list
        foreach ((array)$row as $k => $v) {
            if (!in_array($k, $key)) {
                $keyDel[] = $k;
            }
        }
        // Add key not exists
        foreach ($key as $k) {
            if (!isset($row[$k])) {
                $keyAdd[] = $k;
            }
        }


        if (empty($keyAdd) && empty($keyDel)) {
            return;
        }


        $fitEmpty = $this->configs['fitEmpty'];
        foreach ($this->listData as &$row) {
            foreach ((array)$keyDel as $k) {
                unset($row[$k]);
            }

            foreach ((array)$keyAdd as $k) {
                $row[$k] = $fitEmpty;
            }
        }
        unset($row);
    }


    /**
     * Fit title with given keys
     *
     * Drop title value not in given keys, and create new if given keys is not
     * exists in title array.
     *
     * @param   array   $key
     */
    protected function fitTitle(array $key)
    {
        // Title index not in key list
        foreach ($this->listTitle as $k => $v) {
            if (!in_array($k, $key)) {
                unset($this->listTitle[$k]);
            }
        }

        // Key not exist in title
        foreach ($key as $k) {
            if (!isset($this->listTitle[$k])) {
                // Title value is same as key
                $this->listTitle[$k] = $k;
            }
        }
    }


    /**
     * Fit data and title if their key are different
     *
     * Notice: data have multi row(2 dim), and 2nd dimention must use assoc
     * index. Title have only 1 row(1 dim), integer or assoc indexed.
     *
     * @see $config['fitMode']
     */
    protected function fitTitleWithData()
    {
        if (empty($this->listData) || empty($this->listTitle)) {
            return;
        }

        // Will compare by array keys
        // For data, will use it's first/current row,
        // For title, will use original value if hasn't assoc index
        $keyOfData = array_keys(current($this->listData));
        $keyOfTitle = array_keys($this->listTitle);

        if ($keyOfData == $keyOfTitle) {
            return;
        }


        switch ($this->configs['fitMode']) {
            case self::FIT_TO_TITLE:
                $ar = $keyOfTitle;
                break;

            case self::FIT_TO_DATA:
                $ar = $keyOfData;
                break;

            case self::FIT_INSECTION:
                $ar = array_intersect($keyOfTitle, $keyOfData);
                break;

            case self::FIT_UNION:
                $ar = array_unique(array_merge($keyOfTitle, $keyOfData));
                break;
            default:
        }

        $this->fitTitle($ar);
        $this->fitData($ar);
    }


    /**
     * Format list data use closure function
     *
     * Closure function $formatFunction must have 1 param and define as
     * reference, eg: function (&row) {}.
     *
     * @param   callback    $formatFunction
     * @return  $this
     */
    public function formatData($formatFunction)
    {
        foreach ($this->listData as &$row) {
            $formatFunction($row);
        }
        unset($row);

        return $this;
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
        $this->readRequest(true);
        $this->setPager();

        return $this->tpl->fetch($this->configs['tpl']);
    }


    /**
     * Get config for generate SQL
     *
     * The result will use as config in SqlGenerator, include:
     * - LIMIT
     * - ORDERBY
     *
     * When there are multiple list on single page, second list must set
     * $forcenew to true.
     *
     * @param   boolean $forcenew
     * @return  array
     * @see Fwlib\Db\SqlGenerator
     */
    public function getSqlConfig($forcenew = false)
    {
        $this->readRequest($forcenew);

        $ar = array();

        $ar['LIMIT'] = $this->configs['pageSize'] * ($this->info['page'] - 1)
            . ', ' . $this->configs['pageSize'];

        if (!empty($this->configs['orderBy'])) {
            // orderBy_idx is column name
            $ar['ORDERBY'] = $this->configs['orderBy']
                . ' ' . $this->configs['orderByDir'];
        }

        return $ar;
    }


    /**
     * Get Util instance
     *
     * @param   string  $name
     * @return  object
     */
    protected function getUtil($name)
    {
        return UtilContainer::getInstance()
            ->get($name);
    }


    /**
     * Read http param and parse
     *
     * $param   boolean $forcenew
     * @return  $this
     */
    protected function readRequest($forcenew = false)
    {
        $arrayUtil = $this->getUtil('Array');

        // Avoid duplicate
        if (!empty($this->url['base']) && !$forcenew) {
            return;
        }


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

        $httpUtil = $this->getUtil('HttpUtil');
        $this->url['base'] = $httpUtil->getSelfUrl(false);

        $page = $arrayUtil->getIdx($this->param, $this->configs['pageParam'], 1);
        $this->setPage($page);


        // Always treat orderBy
        $orderBy = '';
        $dir = '';
        if (isset($this->param[$this->configs['orderByParam']])) {
            $orderBy = $this->param[$this->configs['orderByParam']];
            $dir = $arrayUtil->getIdx(
                $this->param,
                $this->configs['orderByParam'] . 'Dir',
                ''
            );
        }
        $this->setOrderBy($orderBy, $dir);

        return $this;
    }


    /**
     * Set single config
     *
     * @param   string  $key
     * @param   mixed   $value
     * @return  ListTable
     */
    public function setConfig($key, $value)
    {
        $this->configs[$key] = $value;

        if ('class' == $key) {
            $this->setId($this->configs['id'], $value);

        } elseif ('id' == $key) {
            $this->setId($value, $this->configs['class']);
        }

        return $this;
    }


    /**
     * Set multiple configs
     *
     * @param   array   $configs
     * @return  ListTable
     */
    public function setConfigs(array $configs)
    {
        $this->configs = array_merge($this->configs, $configs);

        if (isset($configs['class']) || isset($configs['id'])) {
            $this->setId(
                isset($configs['id']) ? $configs['id']
                : $this->configs['id'],
                isset($configs['class']) ? $configs['class']
                : $this->configs['class']
            );
        }


        return $this;
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
        if ($updateTotalRows || (-1 == $this->info['totalRows'])) {
            $this->info['totalRows'] = count($listData);
        }

        if (!is_null($listTitle)) {
            $this->listTitle = $listTitle;
        }

        // Same number of items maybe index diff, so always do fit.
        $this->fitTitleWithData();

        return $this;
    }


    /**
     * Set db query
     *
     * Will query total rows and list data by set db connection and config,
     * will overwrite exists $listData.
     *
     * @param   Fwlib\Bridge\Adodb  $db
     * @param   array       $config
     * @return  $this
     */
    public function setDbQuery($db, $config)
    {
        // Get totalRows
        $this->info['totalRows'] = $db->execute(
            array_merge($config, array('SELECT' => 'COUNT(1) AS c'))
        )
        ->fields['c'];

        // Query data
        $rs = $db->execute(
            array_merge($config, $this->getSqlConfig(true))
        );
        $this->listData = $rs->GetArray();

        $this->fitTitleWithData();

        return $this;
    }


    /**
     * Set default configs
     */
    protected function setDefaultConfigs()
    {
        $this->setConfigs(
            array(
                // Notice: this is NOT actual class and id used in template,
                // see $this->info and $this->setId() for details.

                // Classname for root element, and prefix of inherit elements
                // Should not be empty.
                'class'             => 'list-table',
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
                'fitMode'           => self::FIT_TO_TITLE,

                // If a value in data is empty, display with this value.
                // Not for title, which will use field name.
                'fitEmpty'          => '&nbsp;',
                //'fit_data_title'    => 0,
                //'fit_empty'         => '&nbsp;',


                // Enable orderBy on those column, empty to disable orderBy
                // Format: {[column, direction],}
                // First [] is default, and default direction is ASC.
                'orderByColumn'     => array(),
                // Which column to orderBy,
                'orderBy'           => '',
                // Orderby direction, ASC or DESC
                'orderByDir'        => 'ASC',
                // Get param for orderBy
                'orderByParam'      => 'ob',
                // Preserved, will be auto generated if orderBy enabled
                'orderByText'       => '',
                // Orderby text/symbol for ASC and DESC
                'orderByTextAsc'    => '↑',
                'orderByTextDesc'   => '↓',
                // More orderBy symbol
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
                'pageParam'         => 'p',
                // How many rows to display each page
                'pageSize'          => 10,
                //'page_cur'          => 1,
                //'pageParam'        => 'p',// Used in url to set page no.
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
            $class = $this->configs['class'];    // For later use
        } else {
            $this->configs['class'] = $class;
        }

        $this->info['class'] = $class;

        // Class may have multiple value split by space, use first one as
        // prefix of other elements.
        if (false !== strpos($class, ' ')) {
            $class = strstr($class, ' ', true);
        }
        $this->info['classPrefix'] = $class . '__';


        $id = trim($id);
        if (0 == strlen($id)) {     // Avoid 0, which is empty
            $id = 1;
        }
        $this->configs['id'] = $id;
        $this->info['idPrefix'] = $this->info['classPrefix'] . $id . '__';
        $this->info['id'] = $this->info['classPrefix'] . $id;


        // Change pageParam, eg: p1, pa
        if ('0' != $id && '1' != $id) {
            $this->configs['pageParam'] = 'p' . $id;
        }
        // Useless, readRequest() will call it
        //$this->setPage();

        // Change orderBy param
        $this->configs['orderByParam'] = 'ob' . $id;

        return $this;
    }


    /**
     * Set orderBy info
     *
     * Didn't validate orderBy key exists in data or title array.
     *
     * @param   mixed   $key
     * @param   string  $dir    ASC/DESC
     */
    public function setOrderBy($key = null, $dir = null)
    {
        // Parse orderBy config
        $orderByColumn = array();
        foreach ((array)$this->configs['orderByColumn'] as $v) {
            $orderByColumn[$v[0]] = array(
                $v[0],
                (isset($v[1])) ? $v[1] : 'ASC',
            );
        }
        $this->info['orderByColumn'] = $orderByColumn;


        // Check orderBy param, if fail, use config default
        if (!isset($orderByColumn[$key])) {
            list($key, $dir) = current($orderByColumn);
        } elseif (empty($dir)) {
            $dir = $orderByColumn[$key][1];
        }
        $this->configs['orderBy'] = $key;


        $dir = strtoupper($dir);
        if ('ASC' == $dir) {
            $dirReverse = 'DESC';
            $this->configs['orderByDir'] = 'ASC';
            $this->configs['orderByText'] = $this->configs['orderByTextAsc'];
        } else {
            $dirReverse = 'ASC';
            $this->configs['orderByDir'] = 'DESC';
            $this->configs['orderByText'] = $this->configs['orderByTextDesc'];
        }


        // Url param
        $ob = $this->configs['orderByParam'];
        // Change orderBy will clear page param
        // Orderby index is appended in template by each th, remove here
        $this->url['obCur'] = $this->genUrl(
            array("{$ob}Dir" => $dirReverse),
            array($ob, $this->configs['pageParam'])
        );

        // Other column orderBy will clear diretion
        // Added pageParam is dummy, to keep url start with '?', fit tpl later
        $this->url['obOther'] = $this->genUrl(
            array($this->configs['pageParam'] => 1),
            array($ob, "{$ob}Dir")
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
        $arrayUtil = $this->getUtil('Array');

        if (is_null($page)) {
            $page = $arrayUtil->getIdx(
                $this->param,
                $this->configs['pageParam'],
                1
            );
        }
        $page = intval($page);


        // Compare with min and max page number
        if (1 > $page) {
            $page = 1;
        }
        if (0 < $this->info['totalRows']
            && 0 < $this->configs['pageSize']
        ) {
            $this->info['pageMax'] =
                ceil($this->info['totalRows'] / $this->configs['pageSize']);
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
     * @return  $this
     * @see     $config
     */
    protected function setPager()
    {
        $page      = $this->info['page'];
        $pageMax   = $this->info['pageMax'];
        $pageSize  = $this->configs['pageSize'];
        $totalRows = $this->info['totalRows'];


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
            $this->configs['pagerTextBody']
        );

        // Generate url for pager
        //$this->url['base'] = GetSelfUrl(true);   // Move to readRequest()
        if (1 < $page) {
            // Not first page
            $this->url['pageFirst'] = $this->genUrl(
                array($this->configs['pageParam'] => 1)
            );
            $this->url['pagePrev'] = $this->genUrl(
                array($this->configs['pageParam'] => $page - 1)
            );
        } else {
            $this->url['pageFirst'] = '';
            $this->url['pagePrev'] = '';
        }
        if ($page < $pageMax) {
            // Not last page
            $this->url['pageNext'] = $this->genUrl(
                array($this->configs['pageParam'] => $page + 1)
            );
            $this->url['pageLast'] = $this->genUrl(
                array($this->configs['pageParam'] => $pageMax)
            );
        } else {
            $this->url['pageNext'] = '';
            $this->url['pageLast'] = '';
        }

        // Form submit target url
        $this->url['form'] = $this->genUrl(
            null,
            array($this->configs['pageParam'])
        );

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


    /**
     * Set ListTable title
     *
     * Usually used together with setDbQuery(), which need not set listData
     * from outside(use setData() method).
     *
     * In common this method should call before setDbQuery().
     *
     * @param   array   $title
     * @return  $this
     */
    public function setTitle($title)
    {
        $this->listTitle = $title;

        return $this;
    }


    /**
     * Set totalRows
     *
     * @param   int     $totalRows
     * @return  $this
     */
    public function setTotalRows($totalRows)
    {
        $this->info['totalRows'] = $totalRows;

        return $this;
    }
}
