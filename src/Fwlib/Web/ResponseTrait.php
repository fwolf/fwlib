<?php
namespace Fwlib\Web;

use Fwlib\Base\SingletonTrait;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait ResponseTrait
{
    use SingletonTrait;


    /**
     * @var string
     */
    protected $content = '';

    /**
     * @var string[]
     */
    protected $headers = [];


    /**
     * @see ResponseInterface::addHeader()
     *
     * @param   string  $value
     * @param   string  $key
     * @return  static
     */
    public function addHeader($value, $key = null)
    {
        if (empty($key)) {
            $this->headers[] = $value;
        } else {
            $this->headers[$key] = $value;
        }

        return $this;
    }


    /**
     * @see ResponseInterface::getContent()
     *
     * @return  string
     */
    public function getContent()
    {
        return $this->content;
    }


    /**
     * @see ResponseInterface::getHeader()
     *
     * @param   string  $key
     * @return  string|null
     */
    public function getHeader($key)
    {
        return array_key_exists($key, $this->headers)
            ? $this->headers[$key]
            : null;
    }


    /**
     * @see ResponseInterface::getHeaders()
     *
     * @return  array
     */
    public function getHeaders()
    {
        return $this->headers;
    }


    /**
     * @see ResponseInterface::removeHeader()
     *
     * @param   string  $key
     * @return  static
     */
    public function removeHeader($key)
    {
        unset($this->headers[$key]);

        return $this;
    }


    /**
     * @see ResponseInterface::send()
     *
     * @param   bool    $obEndFlush
     * @return  static
     */
    public function send($obEndFlush = false)
    {
        $this->sendHeaders();

        $this->sendContent();

        if ($obEndFlush) {
            ob_end_flush();
        }

        return $this;
    }


    /**
     * @see ResponseInterface::sendContent()
     *
     * @return  static
     */
    public function sendContent()
    {
        echo $this->content;

        return $this;
    }


    /**
     * @see ResponseInterface::sendHeaders()
     *
     * @return  static
     */
    public function sendHeaders()
    {
        foreach ($this->headers as $header) {
            header($header);
        }

        return $this;
    }


    /**
     * @see ResponseInterface::setContent()
     *
     * @param   string  $content
     * @return  static
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }


    /**
     * @see ResponseInterface::setHeaders()
     *
     * @param   array   $headers
     * @return  static
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }
}
