<?php
namespace Fwlib\Web;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Request implements RequestInterface
{
    use RequestTrait;


    /**
     * @var string
     */
    protected $actionParameter = 'a';

    /**
     * @var string
     */
    protected $moduleParameter = 'm';
}
