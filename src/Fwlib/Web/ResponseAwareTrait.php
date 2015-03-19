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
     * @var ResponseInterface
     */
    protected $response = null;


    /**
     * @return  ResponseInterface
     */
    public function getResponse()
    {
        return is_null($this->response)
            ? Response::getInstance()
            : $this->response;
    }


    /**
     * @param   ResponseInterface   $response
     * @return  static
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;

        return $this;
    }
}
