<?php
namespace Fwlib\Html\ListView;

/**
 * RowAdjusterAwareTrait
 *
 * List body data are retrieved from model, may need adjust before display,
 * this trait provide a callable to do it. The callable is usually a closure,
 * take single list body row array as parameter, and return modified array.
 * Although html code may be applied to row or cell data, this is different with
 * render from list body to output html, row adjuster will only change value
 * which will be displayed in td of final html.
 *
 * @see Renderer::getListBody()
 *
 *
 * Row adjuster is not a class instance, it is used in {@see Renderer} and can
 * also be injected from {@see ListView}.
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
