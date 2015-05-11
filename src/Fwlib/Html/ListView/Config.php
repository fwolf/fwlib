<?php
namespace Fwlib\Html\ListView;

/**
 * Config for {@see ListView} and its components
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Config extends \Fwlib\Config\Config
{
    /**
     * {@inheritdoc}
     *
     * Here is default configs.
     */
    protected $configs = [
        // Common config

        'class'             => 'list-view',
        'id'                => 1,

        // Default/failsafe, MUST set a positive value
        'pageSize'          => 10,

        // Default order by, format {key: direction}
        'orderBy'           => [],

        'showTopPager'      => false,
        'showBottomPager'   => true,


        // Fitter config

        'fitMode'           => FitMode::TO_TITLE,

        /**
         * If a value in body is empty, display with this value. Not for head,
         * which will use field name.
         * Will assign to {@see Fitter::$emptyFiller}
         */
        'fitEmptyFiller'    => '&nbsp;',


        // Renderer config

        // Text mark after head, if order assigned and match with head key
        'orderByTextAsc'    => '↑',
        'orderByTextDesc'   => '↓',

        // Text to form pager
        'pagerTextFirstPage'  => '首页',
        'pagerTextPrevPage'   => '上一页',
        'pagerTextNextPage'   => '下一页',
        'pagerTextLastPage'   => '尾页',
        'pagerTextSummary'    =>
            '共{rowCount}条信息，每页显示{pageSize}条，当前为第{page}/{maxPage}页',
        'pagerTextJump1'      => '转到第',
        'pagerTextJump2'      => '页',
        'pagerTextJumpButton' => '转',
        'pagerTextSpacer'     => " | ",

        // Auto select of page number form input
        'pageNumberInputFocusSelect' => true,

        /**
         * Raw string append to td/th/tr tag in list view, eg: nowrap='nowrap'.
         *
         * Notice the trAppend will only apply to list body, not list head.
         * The thAppend and tdAppend is assoc indexed same with head/body, and
         * trAppend is int indexed, match with the row number in this page,
         * start from 0.
         *
         * This for back compatible, if possible, use CSS instead.
         */
        'tdAppend' => [],
        'thAppend' => [],
        'trAppend' => [],
    ];
}
