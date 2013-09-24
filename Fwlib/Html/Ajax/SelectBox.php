<?php
namespace Fwlib\Html\Ajax;

use Fwlib\Config\Config;

/**
 * Float box for select data
 *
 * Requirement:
 *  - jQuery
 *
 * @codeCoverageIgnore
 *
 * @link http://jqueryui.com/dialog/
 *
 * @package     Fwlib\Html\Ajax
 * @copyright   Copyright 2011-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2011-08-09
 */
class SelectBox
{
    /**
     * Config object
     */
    protected $config = null;


    /**
     * Constructor
     *
     * @param   array   $config
     */
    public function __construct($config = array())
    {
        $this->config = new Config();

        $this->setConfigDefault();

        $this->setConfig($config);

        $this->init();
    }


    /**
     * Get html output
     *
     * @return  string
     */
    public function get()
    {
        // Init data again
        $this->init();

        $html = '';

        // Html define
        $html .= $this->getCss();
        $html .= '<div id=\'' . $this->id('bg') . '\'>
            <iframe style=\'position: absolute; z-index: -1;\'
                frameborder=\'0\' src=\'about:blank\'></iframe>
            </div>' . "\n";
        $html .= $this->getDiv();
        $html .= $this->getJs();

        return $html;
    }


    /**
     * Get html output, div part
     *
     * @return  string
     */
    public function getCss()
    {
        // Css body
        $css = '';
        $css .= '<style type="text/css" media="screen, print">
            <!--
            #' . $this->id('empty') . ', #' . $this->id('loading')
                . ', #' . $this->id('row-tpl') . ' {
                display: none;
            }
            #' . $this->id('empty') . ' td, #' . $this->id('loading')
                . ' td, #' . $this->id('tip')
                . ' td, .' . $this->id() . '-col-' . $this->config->get('id-td-choose') . ' {
                text-align: center;
            }
';

        foreach ($this->config->get('id-other') as $k) {
            $css .= '
                #' . $this->id($k) . ' {
' . $this->config->get('css-' . $k) . '
                }
';
        }

        // Css using class
        $css .= '
            .' . $this->id('row') . ' {
' . $this->config->get('css-datarow') . '
            }
            .' . $this->id('tr-hover') . ' {
' . $this->config->get('css-tr-hover') . '
            }
';

        $css .= $this->config->get('css-add') . '
            -->
            </style>
';


        // Append css using js
        $js = '
<script type=\'text/javascript\'>
<!--//--><![CDATA[//>
<!--
/* Append css define to <head> */
$(\'head\').append(\'\
' . str_replace("\n", "\\\n", $css) . '\
\');
//--><!]]>
</script>
';

        return $js;
    }


    /**
     * Get html output, div part
     *
     * @return  string
     */
    public function getDiv()
    {
        $html = '';

        $html .= '<div id=\'' . $this->id('div') . '\'>
            <div id=\'' . $this->id('title') . '\'>'
                . $this->config->get('title') . '</div>
';

        if (true == $this->config->get('show-close-top')) {
            $html .= '
                <div id=\'' . $this->id('close-top') . '\'>'
                    . $this->config->get('title-close') . '</div>
';
        }

        if (true == $this->config->get('query')) {
            $html .= '
                <div id=\'' . $this->id('clearit') . '\'></div>

                <label>' . $this->config->get('title-query-input') . '</label>
                <input type=\'text\' id=\''
                    . $this->id() . '-query\' size=\''
                        . $this->config->get('query-input-size') . '\' />
                <input type=\'button\' id=\''
                    . $this->id() . '-submit\' value=\''
                        . $this->config->get('title-query-submit') . '\' />
';

            // Put query url as hidden input, so can edit it when needed
            $html .= '
                <input type=\'hidden\' id=\''
                    . $this->id() . '-url\' value=\''
                        . $this->config->get('query-url') . '\' />
';
        }

        $html .= '
            <table id=\'' . $this->id('table') . '\'>
                <thead>
                    <tr>
';

        // Data table title
        foreach ((array)$this->config->get('title-datarow-col') as $k => $v) {
            $html .= '<th>' . $v . '</th>' . "\n";
        }
        $html .= '<th>' . $this->config->get('title-choose') . '</th>' . "\n";

        $html .= '
                    </tr>
                </thead>
                <tbody>
                    <tr id=\'' . $this->id('row-tpl') . '\'>
';

        // Data table rows
        foreach ((array)$this->config->get('title-datarow-col') as $k => $v) {
            $html .= '<td class=\'' . $this->id() . '-col-'
                . $k . '\'></td>' . "\n";
        }
        $html .= '<td class=\'' . $this->id() . '-col-'
            . $this->config->get('id-td-choose') . '\'>' . "\n";
        // Put hidden input here
        foreach ((array)$this->config->get('datarow-col-hidden') as $k) {
            $html .= '<input type=\'hidden\' class=\''
                . $this->id() . '-col-' . $k . '\' />' . "\n";
        }

        // Assign onclick using js, avoid lost event when cloning in IE.
        $html .= '
                            <a href=\'javascript:void(0);\'
                                >' . $this->config->get('title-choose') . '</a>
                        </td>
                    </tr>
                    <tr id=\'' . $this->id('loading') . '\'>
                        <td colspan=\'' . $this->config->get('datarow-col-cnt') . '\'>'
                            . $this->config->get('text-loading') . '</td>
                    </tr>
                    <tr id=\'' . $this->id('empty') . '\'>
                        <td colspan=\'' . $this->config->get('datarow-col-cnt') . '\'>'
                            . $this->config->get('text-empty') . '</td>
                    </tr>
                    <tr id=\'' . $this->id('tip') . '\'>
                        <td colspan=\'' . $this->config->get('datarow-col-cnt') . '\'>'
                            . $this->config->get('text-tip') . '</td>
                    </tr>
                </tbody>
            </table>
';

        if (true == $this->config->get('show-close-bottom')) {
            $html .= '
                <div id=\'' . $this->id('close-bottom') . '\'>'
                    . $this->config->get('title-close') . '</div>
';
        }

        $html .= '</div>
';
        return $html;
    }


    /**
     * Get html output, js part
     *
     * @return  string
     */
    public function getJs()
    {
        $js = '';

        $js .= '<script type=\'text/javascript\'>
            <!--//--><![CDATA[//>
            <!--
            /* Set bg height and width */
            $(\'#' . $this->id('bg') . '\')
                .css(\'width\', $(document).width())
                .css(\'height\', $(document).height() * 1.2);
            $(\'#' . $this->id('bg') . ' iframe\')
                .css(\'width\', $(document).width())
                .css(\'height\', $(document).height() * 1.2);

            /* Set click action */
            $(\'#' . $this->id('caller') . '\').click(function () {
' . $this->config->get('js-call') . '
                $(\'#' . $this->id('bg') . '\').show();
                $(\'#' . $this->id('div') . '\')
                    .css(\'top\', ((window.innerHeight
                                || document.documentElement.offsetHeight)
                            - $(\'#' . $this->id('div') . '\').height())
                        / 3
                        + (document.body.scrollTop
                            || document.documentElement.scrollTop) + '
                        . $this->config->get('offset-y') . ' + \'px\')
                    .css(\'left\', $(window).width() / 2
                        - $(\'#' . $this->id('div') . '\').width() / 2
                        + ' . $this->config->get('offset-x') . ' + \'px\')
                    .show();
';
        // Do query at once when open select div
        if (true == $this->config->get('query-when-open')) {
            $js .= '
                    $(\'#' . $this->id() . '-submit\').click();
';
        }
        $js .= '
            });

            /* Set query action */
            $(\'#' . $this->id() . '-submit\').click(function () {
';

        // If do query when user input nothing ?
        if (true == $this->config->get('query-empty')) {
            $if = '(true)';
        } else {
            $if = '(0 < $(\'#' . $this->id() . '-query\').val().length)';
        }
        $js .= '
                if ' . $if . ' {
';

        $js .= '
                    /* Query begin */
                    $(\'#' . $this->id('tip') . '\').hide();
                    $(\'#' . $this->id('loading') . '\').show();
                    $(\'#' . $this->id('empty') . '\').hide();
                    $.ajax({
                        url: $(\'#' . $this->id() . '-url\').val(),
                        data: {\'' . $this->config->get('query-param') . '\':
                            $(\'#' . $this->id() . '-query\').val()},
                        dataType: \'' . $this->config->get('query-datatype') . '\',
                        success: function(msg){
                            $(\'#' . $this->id('loading') . '\').hide();
                            $(\'.' . $this->id() . '-row\').remove();
                            if (0 < msg.length) {
                                /* Got result */
                                $(msg).each(function(){
                                    tr = $(\'#' . $this->id() . '-row-tpl\').clone();
                                    tr.addClass(\'' . $this->id() . '-row\');

                                    /* Attach onclick event */
                                    /* Cloning in IE will lost event */
                                    $(\'a\', tr).last().click(function () {
' . $this->config->get('js-choose') . '
';
        // When select, write selected value
        $list = $this->config->get('list');
        foreach ($this->config->get('writeback') as $k => $v) {
            $js .= '
                                    $("#' . $v . '").val(
                                        $(".' . $this->id()
                                            . '-col-' . $k . '",
                                            $(this).parent().parent())
                                            .' . $list[$k]['get'] . '());
';
        }

        $js .= '
                                        $("#' . $this->id('div') . '").hide();
                                        $("#' . $this->id('bg') . '").hide();
                                    });
';

        // Assign result from ajax json to tr
        foreach ((array)$this->config->get('list') as $k => $v) {
            $js .= '
                                $(\'.' . $this->id() . '-col-' . $k . '\'
                                    , tr).' . $v['get']
                                        . '(this.' . $k . ');
';
        }

        $js .= '
                                    /* Row bg-color */
                                    tr.mouseenter(function () {
                                        $(this).addClass(\''
                                            . $this->id('tr-hover') . '\');
                                    }).mouseleave(function () {
                                        $(this).removeClass(\''
                                            . $this->id('tr-hover') . '\');
                                    });

                                    $(\'#' . $this->id('loading') . '\')
                                        .before(tr);
' . $this->config->get('js-query') . '
                                    tr.show();
                                });
                            }
                            else {
                                /* No result */
                                $(\'#' . $this->id('empty') . '\').show();
                            }
                        }
                    });
                }
                else {
                    /* Nothing to query */
                    $(\'#' . $this->id('tip') . '\').show();
                    $(\'#' . $this->id('loading') . '\').hide();
                    $(\'#' . $this->id('empty') . '\').hide();
                }
            });
';

        // Query when typing
        if (true == $this->config->get('query-typing')) {
            $js .= '
                $(\'#' . $this->id() . '-query\').keyup(function () {
                    $(\'#' . $this->id() . '-submit\').click();
                });
';
        }

        $js .= '
            /* Link to hide select layer */
            $(\'#' . $this->id('close-bottom') . ', #'
                . $this->id('close-top') . '\').click(function () {
                $(this).parent().hide();
                $(\'#' . $this->id('bg') . '\').hide();
            });
            //--><!]]>
            </script>

';

        return $js;
    }


    /**
     * Get html element id, auto add id prefix
     *
     * If $key is empty, means for main id 'id'.
     *
     * If $key is not start with 'id-', will auto add.
     *
     * Value of all id except main will pretend by id-prefix and main id, so
     * it is safe to use multiple SelectBox in same page with different main
     * id.
     *
     * @param   string  $key
     */
    protected function id($key = '')
    {
        if (empty($key)) {
            // Main id
            $val = $this->config->get('id');

        } else {
            if ('id-' != substr($key, 0, 3)) {
                $key = 'id-' . $key;
            }
            $val = $this->config->get('id') . '-' . $this->config->get($key);
        }

        return $this->config->get('id-prefix') . $val;
    }


    /**
     * Initial using default and user config
     */
    public function init()
    {
        // Generate config for other id/class
        foreach ($this->config->get('id-other') as $k) {
            $this->config->set('id-' . $k, $k);
        }
        foreach ($this->config->get('class-other') as $k) {
            $this->config->set('id-' . $k, $k);
        }

        // Join select list cols and hidden
        $this->config->set('list', array());
        foreach ((array)$this->config->get('title-datarow-col') as $k => $v) {
            $this->config->set(
                'list.' . $k,
                array(
                    'title' => $v,
                    'get'   => 'text',  // jQuery method to read content
                )
            );
        }
        foreach ((array)$this->config->get('datarow-col-hidden') as $k) {
            $this->config->set(
                'list.' . $k,
                array(
                    'get'   => 'val',   // jQuery method to read content
                )
            );
        }

        // Join tips, merge pagesize in.
        $this->config->set(
            'text-tip',
            str_replace(
                '{pagesize}',
                $this->config->get('query-pagesize'),
                $this->config->get('text-tip')
            )
        );
        $this->config->set('datarow-col-cnt', count($this->config->get('title-datarow-col')) + 1);
    }


    /**
     * Set user config
     *
     * @param   array   $config
     * @return  $this
     */
    public function setConfig($config = array())
    {
        $this->config->set($config);
    }


    /**
     * Set default config
     *
     * @return  $this
     */
    protected function setConfigDefault()
    {
        $this->config->set(
            array(
                // Html

                'id-prefix' => 'fwlib-sel-box-',
                'id' => '1',

                // Id of elm on which click() will show select box
                'id-caller' => 'caller',
                // Id/class of td which choose link in
                'id-td-choose' => 'td-choose',

                // Other id will simple pretend with prefix, value is same
                // with name, can't customize
                'id-other' => array(
                    'bg', 'close-bottom', 'close-top', 'div',
                    'table', 'title', 'clearit',
                    'empty', 'loading', 'tip',
                    'row-tpl',
                ),
                // Other class, same with other id
                'class-other' => array(
                    'tr-hover', 'row',
                ),

                // Allow query data by user input
                'query' => true,
                'query-input-size' => 30,
                'query-pagesize' => 10,

                // Json query
                // Do query when user input is empty ?
                'query-empty' => true,
                // Query when user input ?
                'query-typing' => true,
                // Url to treat ajax request, IMPORTANT
                'query-url' => '',
                // Do query when open select box ?
                'query-when-open' => false,
                // Param name for value in user input for ajax POST
                'query-param' => 's',
                'query-datatype' => 'json',

                // Show switch
                'show-close-bottom' => true,
                'show-close-top' => true,

                // Data row
                // Will auto compute later
                'datarow-col-cnt' => 0,
                // Hidden input on datarow to store dta for writeback.
                'datarow-col-hidden' => array(
                    // [id]
                ),
                // When choosen, write these data back
                'writeback' => array(
                    // {'id/class in datarow without prefix': 'id in caller'}
                ),

                // Div position adjust(based on h/v center)
                'offset-x' => 0,
                'offset-y' => 0,


                // Lang
                'title' => 'Select',
                'title-close' => 'Close',
                'title-query-input' => 'Title:',
                'title-query-submit' => 'Search',
                // Choose link on search result row
                'title-choose' => 'Choose',
                // Data row col title
                // Id should fit index of query result data array.
                'title-datarow-col' => array(
                    // {id: title}
                ),

                'text-loading' => 'Searching, please stand by ...',
                'text-empty' => 'No result found, please change search keywords.',
                // Tip to show before user input search keywords.
                // {pagesize} will be replace by actual value.
                'text-tip'  => 'Please input sequential part of title to search,
result will only show first {pagesize} items.',

                // Js

                // Execute when user click on caller, before default action.
                'js-call' => '',
                // After treat server result
                // After default action and before result show.
                'js-query' => '',
                // When user click choose link, before default action
                'js-choose' => '',


                // CSS

                'css-bg' => '
            background: #E5E5E5;
            display: none;
            filter: alpha(opacity=60);
            left: 0px;
            opacity: 0.6;
            position: absolute;
            top: 0px;
            z-index: 998;
',
                'css-close-bottom' => '
            cursor: pointer;
            margin-top: 0.5em;
            text-align: right;
            width: 100%;
',
                'css-close-top' => '
            cursor: pointer;
            float: right;
',
                'css-div' => '
            background-color: #FFF;
            border: 1px solid #999;
            display: none;
            padding: 0.7em;
            position: absolute;
            text-align: center;
            width: 700px;
            z-index: 999;
',
                'css-table' => '
            border: 1px solid;
            border-collapse: collapse;
            border-spacing: 0;
            float: none;
            line-height: 1.2em;
            text-align: center;
            vertical-align: baseline;
            width: 100%;
',
                'css-title' => '
            float: left;
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 0.7em;
            padding-left: 2em;
            text-align: center;
            width: 90%;
',
                'css-tr-hover' => '
            background-color: #e3e3de;
',
                'css-clearit' => '
            clear: both;
',
                'css-empty' => '',
                'css-loading' => '',
                'css-tip' => '',
                // Css for row using class, not id
                'css-datarow' => '',
                // Css add are user defined, can overwrite upper setting
                'css-add' => '
',
            )
        );

        return $this;
    }


    /**
     * Write generated html to file
     *
     * @param   string  $file
     * @return  mixed   @see file_put_contents)
     */
    public function write($file)
    {
        return file_put_contents(
            $file,
            $this->get()
        );
    }
}
