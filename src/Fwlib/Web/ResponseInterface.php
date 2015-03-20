<?php
namespace Fwlib\Web;

/**
 * Response
 *
 * Hold all response information, send out with {@see send()} method. Beware,
 * redirect or download action will automatic do their own send().
 *
 *
 * Headers are stored as array. Each header line can have associate key, for
 * get or remove operate later. Lines without string key can not do that.
 *
 * Content is commonly html or json, real http response body except header.
 *
 *
 * @see \HttpResponse
 * @see \Symfony\Component\HttpFoundation\Response
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface ResponseInterface
{
    /**
     * Add a header line
     *
     * @param   string  $value
     * @param   string  $key
     * @return  static
     */
    public function addHeader($value, $key = null);


    /**
     * Getter of content
     *
     * @return  string
     */
    public function getContent();


    /**
     * Getter of single header line
     *
     * @param   string  $key
     * @return  string|null
     */
    public function getHeader($key);


    /**
     * Getter of all headers
     *
     * @return  array
     */
    public function getHeaders();


    /**
     * Remove a header line
     *
     * @param   string  $key
     * @return  static
     */
    public function removeHeader($key);


    /**
     * Send all out
     *
     * @param   bool    $obEndFlush
     * @return  static
     */
    public function send($obEndFlush = false);


    /**
     * Send content out
     *
     * Tidy or other purify can apply here.
     * @see \Fwlib\Web\Helper\TidyTrait
     *
     * @return  static
     */
    public function sendContent();


    /**
     * Send headers out
     *
     * Send single header line is useless.
     *
     * @return  static
     */
    public function sendHeaders();


    /**
     * Setter of content
     *
     * @param   string  $content
     * @return  static
     */
    public function setContent($content);


    /**
     * Setter of all headers
     *
     * @param   array   $headers
     * @return  static
     */
    public function setHeaders($headers);
}
