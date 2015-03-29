<?php
namespace Fwlib\Html\ListView;

/**
 * FitterInterface
 *
 * If column in list data and title does not match, they need to fit to same
 * columns for output.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface FitterInterface
{
    /**
     * Do fit
     *
     * @param   ListDto $listDto
     * @return  ListDto Fitted dto
     */
    public function fit(ListDto $listDto);


    /**
     * Setter of empty filler
     *
     * @param   string $emptyFiller
     * @return  static
     */
    public function setEmptyFiller($emptyFiller);


    /**
     * Setter of fit mode
     *
     * @see FitMode
     *
     * @param   int $mode
     * @return  static
     */
    public function setMode($mode);
}
