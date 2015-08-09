<?php
namespace Fwlib\Html\Generator\Element;

/**
 * Radio input, 1/yes/是, 0/no/否
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class YesOrNoRadio extends Radio
{
    const HTML_CLASS_YES = 'yes';

    const HTML_CLASS_NO = 'no';

    const NO = 0;

    const YES = 1;


    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfigs()
    {
        $configs = parent::getDefaultConfigs();

        $configs['items'] = [
            self::NO  => 'No',
            self::YES => 'Yes',
        ];

        return $configs;
    }


    /**
     * {@inheritdoc}
     *
     * Append class yes/no for coloring.
     */
    protected function getTitleClass()
    {
        $class = parent::getTitleClass();

        $radioValue = $this->getValue();

        $appendClass = '';
        if (self::YES === $radioValue) {
            $appendClass = ' ' . self::HTML_CLASS_YES;
        } elseif (self::NO === $radioValue) {
            $appendClass = ' ' . self::HTML_CLASS_NO;
        }

        return trim($class . $appendClass);
    }


    /**
     * {@inheritdoc}
     *
     * Return integer for compare with item strictly.
     *
     * @return  int
     */
    public function getValue()
    {
        return intval(parent::getValue());
    }
}
