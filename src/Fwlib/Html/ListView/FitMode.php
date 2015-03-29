<?php
namespace Fwlib\Html\ListView;

/**
 * FitMode
 *
 * If column in data and title does not match, fitMode option will determine
 * which columns will be used, its value defines here.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class FitMode
{
    /**
     * Fit to title, drop data whose index is not in title index
     */
    const TO_TITLE = 0;

    /**
     * Fit to data, drop title whose index is not in data index
     */
    const TO_DATA = 1;

    /**
     * Fit to intersection of title and data, got fewest column
     */
    const INTERSECTION = 2;

    /**
     * Fit to union of title and data, got most column
     */
    const UNION = 3;
}
