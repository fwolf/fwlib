<?php
namespace Fwlib\Html\ListView;

/**
 * RequestInterface
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface RequestInterface extends ConfigAwareInterface
{
    /**
     * Getter of base url
     *
     * All link or action from this link will based on this url.
     *
     * Notice: Different with {@see HttpUtil}, this base url may carry
     * parameters.
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
     * Get parameter of order by direction
     *
     * @return  string
     */
    public function getOrderByDirectionParameter();

    /**
     * Get parameter of order by column
     *
     * @return  string
     */
    public function getOrderByParameter();

    /**
     * Get current page number
     *
     * @return  int
     */
    public function getPage();

    /**
     * Get parameter of current page
     *
     * @return  string
     */
    public function getPageParameter();

    /**
     * Get current page size from request
     *
     * Another fallback page size is in config of ListView
     *
     * @return  int
     */
    public function getPageSize();

    /**
     * Get parameter of list page size
     *
     * @return  mixed
     */
    public function getPageSizeParameter();

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
     * Set parameter of order by direction
     *
     * @param   string $orderByDirParameter
     * @return  static
     */
    public function setOrderByDirectionParameter($orderByDirParameter);

    /**
     * Set parameter of order by column
     *
     * @param   string $orderByParameter
     * @return  static
     */
    public function setOrderByParameter($orderByParameter);

    /**
     * Set parameter of current page
     *
     * @param   string $pageParameter
     * @return  static
     */
    public function setPageParameter($pageParameter);

    /**
     * Set parameter of list page size
     *
     * @param   mixed $pageSizeParameter
     * @return  static
     */
    public function setPageSizeParameter($pageSizeParameter);

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
