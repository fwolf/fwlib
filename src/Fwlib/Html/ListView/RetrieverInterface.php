<?php
namespace Fwlib\Html\ListView;

/**
 * RetrieverInterface
 *
 * Retrieve list body and row count from backend, use shared config with
 * {@see ListView} and other components.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface RetrieverInterface extends ConfigAwareInterface
{
    /**
     * Get list body
     *
     * @return  array[]
     */
    public function getListBody();

    /**
     * Get row count
     *
     * @return  int
     */
    public function getRowCount();

    /**
     * Set Request instance, used to know current page, order by etc
     *
     * @param   RequestInterface $request
     * @return  static
     */
    public function setRequest(RequestInterface $request);
}
