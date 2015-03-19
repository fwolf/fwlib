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
     * @var RequestInterface
     */
    protected $request = null;


    /**
     * @return  RequestInterface
     */
    public function getRequest()
    {
        return is_null($this->request)
            ? Request::getInstance()
            : $this->request;
    }


    /**
     * @param   RequestInterface    $request
     * @return  static
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }
}
