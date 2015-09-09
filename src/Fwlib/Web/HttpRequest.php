<?php
namespace Fwlib\Web;

use Fwlib\Web\Helper\HttpRequestWrapperTrait;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HttpRequest extends Request implements HttpRequestInterface
{
    use HttpRequestWrapperTrait;
}
