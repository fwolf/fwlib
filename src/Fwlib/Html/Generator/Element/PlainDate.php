<?php
namespace Fwlib\Html\Generator\Element;

/**
 * Date input without any selection
 *
 * Add datetime format auto correction feature.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class PlainDate extends Text
{
    /**
     * Fix wrong date format
     *
     * @param   string $date
     * @return  string
     */
    protected function fixFormat($date)
    {
        $date = str_replace(['年', '月', '－'], '-', $date);

        $date = str_replace(['日'], '', $date);

        return $date;
    }


    /**
     * {@inheritdoc}
     *
     * Remove time part.
     */
    public function getValue()
    {
        $value = parent::getValue();

        if (!empty($value)) {
            $value = $this->fixFormat($value);

            $value = date('Y-m-d', strtotime($value));
        }

        return $value;
    }
}
