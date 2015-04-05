<?php
namespace Fwlib\Html\ListView;

/**
 * RowAdjusterAwareTrait
 *
 * After list body data are retrieved from backend, they may need adjust
 * before display, by adjuster. Adjuster take single list body row array as
 * parameter, and return modified array. Although html code may be applied to
 * cell value, layout of list structure will not be changed, this is different
 * with render, which build list structure with adjusted value.
 *
 * @see Renderer::getListBody()
 *
 *
 * Row adjuster is usually closure, for convenience, as every list are
 * different, and implement an interface for that is expensive. Adjuster can
 * be injected to {@see ListView}.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait RowAdjusterAwareTrait
{
    /**
     * @var callable
     */
    protected $rowAdjuster = null;


    /**
     * @return  callable
     */
    protected function getRowAdjuster()
    {
        return $this->rowAdjuster;
    }


    /**
     * @param   callable $rowAdjuster
     * @return  static
     */
    public function setRowAdjuster(callable $rowAdjuster)
    {
        $this->rowAdjuster = $rowAdjuster;

        return $this;
    }
}
