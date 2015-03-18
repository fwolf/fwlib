<?php
namespace Fwlib\Web;

/**
 * Trait for easy replace Request provider
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait RequestAwareTrait
{
    /**
     * @return  RequestInterface
     */
    public function getRequest()
    {
        return Request::getInstance();
    }
}
