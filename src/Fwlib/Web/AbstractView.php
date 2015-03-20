<?php
namespace Fwlib\Web;

/**
 * @copyright   Copyright 2008-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractView implements ViewInterface
{
    use ViewTrait;


    /**
     * Parts of output
     *
     * @var array
     */
    protected $outputParts = [
        1 => 'header',
        0 => 'body',
        2 => 'footer',
    ];

    /**
     * View title
     *
     * In common, title will present as html page <title>.
     *
     * @var string
     */
    protected $title = '';
}
