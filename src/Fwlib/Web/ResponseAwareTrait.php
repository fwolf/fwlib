<?php
namespace Fwlib\Web;

/**
 * Trait for easy replace Response provider
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ResponseAwareTrait
{
    /**
     * @return  ResponseInterface
     */
    public function getResponse()
    {
        return Response::getInstance();
    }
}
