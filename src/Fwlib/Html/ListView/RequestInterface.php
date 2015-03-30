<?php
namespace Fwlib\Html\ListView;

/**
 * RequestInterface
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface RequestInterface
{
    /**
     * Getter of base url
     *
     * All link or action from this link will based on this url.
     *
     * @return  string
     */
    public function getBaseUrl();

    /**
     * Get order by key and direction
     *
     * @return  array   {key: ASC|DESC}
     */
    public function getOrderBy();

    /**
     * Get current page number
     *
     * @return  int
     */
    public function getPage();

    /**
     * Get current page size from request
     *
     * Another fallback page size is in config of ListView
     *
     * @return  int
     */
    public function getPageSize();

    /**
     * Getter of request source
     *
     * @see RequestSource
     *
     * @return  string
     */
    public function getRequestSource();

    /**
     * Setter of base url
     *
     * @param   string $baseUrl
     * @return  static
     */
    public function setBaseUrl($baseUrl);

    /**
     * Setter of request source
     *
     * @see RequestSource
     *
     * @param   string $requestSource
     * @return  static
     */
    public function setRequestSource($requestSource);
}
