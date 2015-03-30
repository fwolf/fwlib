<?php
namespace Fwlib\Html\ListView\Helper;

/**
 * Used for request retrieved from http get/post.
 *
 * @property    string  $orderByDirParameter
 * @property    string  $orderByParameter
 * @property    string  $pageParameter
 * @property    string  $pageSizeParameter
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait RequestParameterTrait
{
    /**
     * @return  string
     */
    public function getOrderByDirectionParameter()
    {
        return $this->orderByDirParameter;
    }


    /**
     * @return  string
     */
    public function getOrderByParameter()
    {
        return $this->orderByParameter;
    }


    /**
     * @return  string
     */
    public function getPageParameter()
    {
        return $this->pageParameter;
    }


    /**
     * @return  string
     */
    public function getPageSizeParameter()
    {
        return $this->pageSizeParameter;
    }


    /**
     * @param   string  $orderByDirParameter
     * @return  static
     */
    public function setOrderByDirectionParameter($orderByDirParameter)
    {
        $this->orderByDirParameter = $orderByDirParameter;

        return $this;
    }


    /**
     * @param   string  $orderByParameter
     * @return  static
     */
    public function setOrderByParameter($orderByParameter)
    {
        $this->orderByParameter = $orderByParameter;

        return $this;
    }


    /**
     * @param   string  $pageParameter
     * @return  static
     */
    public function setPageParameter($pageParameter)
    {
        $this->pageParameter = $pageParameter;

        return $this;
    }


    /**
     * @param   string  $pageSizeParameter
     * @return  static
     */
    public function setPageSizeParameter($pageSizeParameter)
    {
        $this->pageSizeParameter = $pageSizeParameter;

        return $this;
    }
}
