<?php
namespace Fwlib\Html\Generator\Element;

use Fwlib\Html\Generator\ElementMode;
use Fwlib\Html\Generator\Helper\GetJsLoadHtmlTrait;

/**
 * Select date with WDatePicker
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
abstract class AbstractWDatePicker extends PlainDate
{
    use GetJsLoadHtmlTrait;


    const CFG_JS_TIME_FORMAT = 'formatInJs';

    const CFG_PHP_TIME_FORMAT = 'formatInPHP';

    const CFG_MAX_DATE = 'maxDate';

    const CFG_MIN_DATE = 'minDate';

    const CFG_OPTIONS = 'options';

    const CFG_SIZE = 'size';

    const CFG_TAG = 'tag';

    const VAL_CLASS_WDATE = 'Wdate';

    const VAL_JS_TIME_FORMAT = 'yyyy-MM-dd';

    const VAL_MAX_DATE = '';

    const VAL_MIN_DATE = '';

    const VAL_SIZE = 11;

    const VAL_TAG = 'span';


    /**
     * {@inheritdoc}
     *
     * Append class Wdate of AbstractWDatePicker
     */
    public function getClass($suffix = '')
    {
        $class = parent::getClass($suffix);

        $class .= ' ' . static::VAL_CLASS_WDATE;

        return trim($class);
    }


    /**
     * {@inheritdoc}
     *
     * Configs
     * - formatInJs: Date format in js, EDIT mode in common.
     * - formatInPHP: Date format in PHP, SHOW mode in common.
     * - maxDate: Same with config of WdatePicker.
     * - minDate: Same with config of WdatePicker.
     * - options: Other WdatePicker format.
     * - size: int, Input size, by em in common.
     * - tag: Html tag used in EDIT mode.
     */
    protected function getDefaultConfigs()
    {
        return array_merge(parent::getDefaultConfigs(), [
            self::CFG_JS_TIME_FORMAT  => static::VAL_JS_TIME_FORMAT,
            self::CFG_PHP_TIME_FORMAT => static::VAL_PHP_TIME_FORMAT,
            self::CFG_MAX_DATE        => static::VAL_MAX_DATE,
            self::CFG_MIN_DATE        => static::VAL_MIN_DATE,
            self::CFG_OPTIONS         => [],
            self::CFG_SIZE            => static::VAL_SIZE,
            self::CFG_TAG             => static::VAL_TAG,
        ]);
    }


    /**
     * @return  string
     */
    protected function getFormatInJs()
    {
        return $this->getConfig(self::CFG_JS_TIME_FORMAT);
    }


    /**
     * @return  string
     */
    protected function getFormatInPHP()
    {
        return $this->getConfig(self::CFG_PHP_TIME_FORMAT);
    }


    /**
     * @return  string
     */
    protected function getMaxDate()
    {
        return $this->getConfig(self::CFG_MAX_DATE);
    }


    /**
     * @return  string
     */
    protected function getMinDate()
    {
        return $this->getConfig(self::CFG_MIN_DATE);
    }


    /**
     * @return  array
     */
    protected function getOtherPickerOptions()
    {
        return $this->getConfig(self::CFG_OPTIONS);
    }


    /**
     * {@inheritdoc}
     */
    protected function getOutputForEditMode()
    {
        $options = $this->getPickerOptionString();

        $output = $this->getJsLoadHtml() .
            "<input type='text'" .
            $this->getClassHtml() .
            $this->getIdHtml() . "\n  " .
            trim(
                $this->getNameHtml() .
                $this->getValueHtml(ElementMode::EDIT) .
                " size='" . $this->getSize() . "'"
            ) . "\n  onfocus='WdatePicker($options);'" .
            $this->getRawAttributesHtml() .
            " />";

        return $output;
    }


    /**
     * {@inheritdoc}
     */
    protected function getOutputForShowMode()
    {
        $tag = $this->getTag();

        $output = "<input type='hidden'" .
            $this->getClassHtml() .
            $this->getIdHtml() . "\n  " .
            trim(
                $this->getNameHtml() .
                $this->getValueHtml(ElementMode::EDIT)
            ) . " />\n" .
            "<$tag" .
            $this->getClassHtml($this->getClass("__title")) .
            $this->getIdHtml($this->getId("__title")) .
            $this->getRawAttributesHtml() .
            ">" .
            $this->getValueHtml(ElementMode::SHOW) .
            "</$tag>";

        return $output;
    }


    /**
     * @return  string
     */
    protected function getPickerOptionString()
    {
        $options = $this->getPickerOptions();

        $jsonUtil = $this->getUtilContainer()->getJson();

        return $jsonUtil->encode($options);
    }


    /**
     * @return  array
     */
    protected function getPickerOptions()
    {
        $options = $this->getOtherPickerOptions();

        $options['dateFmt'] = $this->getFormatInJs();

        $maxDate = $this->getMaxDate();
        if (!empty($maxDate)) {
            $options['maxDate'] = trim($maxDate, "'\"");
        }

        $minDate = $this->getMinDate();
        if (!empty($minDate)) {
            $options['minDate'] = trim($minDate, "'\"");
        }

        $jsonUtil = $this->getUtilContainer()->getJson();
        $options = $jsonUtil->encode($options);

        return $options;
    }


    /**
     * @return  int
     */
    protected function getSize()
    {
        return $this->getConfig(self::CFG_SIZE);
    }


    /**
     * @return  string
     */
    protected function getTag()
    {
        return $this->getConfig(self::CFG_TAG);
    }


    /**
     * {@inheritdoc}
     *
     * Format value.
     */
    public function getValue()
    {
        $value = parent::getValue();

        if (!empty($value)) {
            $value = date($this->getFormatInPHP(), strtotime($value));
        }

        return $value;
    }
}
