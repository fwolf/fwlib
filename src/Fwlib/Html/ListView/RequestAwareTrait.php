<?php
namespace Fwlib\Html\ListView;

/**
 * RequestAwareTrait
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
        return $this->request;
    }


    /**
     * @param   RequestInterface $request
     * @return  static
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }
}
