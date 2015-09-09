<?php
namespace Fwlib\Web;

/**
 * Http request
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface HttpRequestInterface extends RequestInterface
{
    /**
     * @see     \Fwlib\Util\Common\HttpUtil::getCookie()
     * @param   string    $name
     * @param   mixed     $default Default value if name is not found in input
     * @param   int       $filter
     * @param   int|array $options
     * @return  string|int
     */
    public function getCookie(
        $name,
        $default = null,
        $filter = FILTER_DEFAULT,
        $options = null
    );


    /**
     * @see     \Fwlib\Util\Common\HttpUtil::getCookies()
     * @param   array|int $definition
     * @param   bool      $addEmpty
     * @return  array
     */
    public function getCookies($definition = FILTER_DEFAULT, $addEmpty = true);


    /**
     * @see     \Fwlib\Util\Common\HttpUtil::getGet()
     * @param   string    $name
     * @param   mixed     $default Default value if name is not found in input
     * @param   int       $filter
     * @param   int|array $options
     * @return  string|int
     */
    public function getGet(
        $name,
        $default = null,
        $filter = FILTER_DEFAULT,
        $options = null
    );


    /**
     * @see     \Fwlib\Util\Common\HttpUtil::getGets()
     * @param   array|int $definition
     * @param   bool      $addEmpty
     * @return  array
     */
    public function getGets($definition = FILTER_DEFAULT, $addEmpty = true);


    /**
     * @see     \Fwlib\Util\Common\HttpUtil::getPost()
     * @param   string    $name
     * @param   mixed     $default Default value if name is not found in input
     * @param   int       $filter
     * @param   int|array $options
     * @return  string|int
     */
    public function getPost(
        $name,
        $default = null,
        $filter = FILTER_DEFAULT,
        $options = null
    );


    /**
     * @see     \Fwlib\Util\Common\HttpUtil::getPosts()
     * @param   array|int $definition
     * @param   bool      $addEmpty
     * @return  array
     */
    public function getPosts($definition = FILTER_DEFAULT, $addEmpty = true);
}
