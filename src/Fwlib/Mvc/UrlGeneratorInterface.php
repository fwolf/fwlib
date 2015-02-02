<?php
namespace Fwlib\Mvc;

/**
 * UrlGenerator Interface
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface UrlGeneratorInterface
{
    /**
     * Get full html link with computed url and given title
     *
     * @param   string  $title
     * @param   string  $rawHtml    Extra html in <a> tag
     * @return  string
     */
    public function getFullLink($title, $rawHtml = '');


    /**
     * Get result url, with base url, eg 'http://domail.tld/?foo=1'
     *
     * @return  string
     */
    public function getFullUrl();


    /**
     * Get html link with computed url and given title
     *
     * @param   string  $title
     * @param   string  $rawHtml    Extra html in <a> tag
     * @return  string
     */
    public function getLink($title, $rawHtml = '');


    /**
     * Get result url, without base url, only '?foo=1&bar=2' parts
     *
     * In modern PHP projects, most use single index as entrance page, url
     * need not contain host part and works well too.
     *
     * @return  string
     */
    public function getUrl();


    /**
     * Set base url, will not touch stored query parameters and fragment
     *
     * This is useful when want to change scheme/host/path parts and keep
     * query parameters.
     *
     * @param   string  $url
     * @return  static
     */
    public function setBaseUrl($url);


    /**
     * Set full url, all previous stored information will be cleared
     *
     * @param   string  $url
     * @return  static
     */
    public function setFullUrl($url);


    /**
     * Set a get parameter
     *
     * @param   string  $name
     * @param   string  $value
     * @return  static
     */
    public function setParameter($name, $value);


    /**
     * Set multiple get parameters
     *
     * @param   array   $parameters Array k-v is parameter name & value
     * @return  static
     */
    public function setParameters(array $parameters);


    /**
     * Unset all parameters
     *
     * @return  static
     */
    public function unsetAllParameters();


    /**
     * Unset a get parameter
     *
     * @param   string  $name
     * @return  static
     */
    public function unsetParameter($name);


    /**
     * Unset multiple get parameters
     *
     * @param   string[]    $names
     * @return  static
     */
    public function unsetParameters(array $names);
}
