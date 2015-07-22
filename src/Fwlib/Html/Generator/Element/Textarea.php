<?php
namespace Fwlib\Html\Generator\Element;

use Fwlib\Html\Generator\AbstractElement;
use Fwlib\Html\Generator\ElementMode;
use Fwlib\Util\UtilContainer;

/**
 * Textarea input
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Textarea extends AbstractElement
{
    /**
     * @return  int
     */
    protected function getCols()
    {
        return $this->getConfig('cols');
    }


    /**
     * {@inheritdoc}
     *
     * Configs
     * - cols: int, property of html tag textarea.
     * - rows: int, property of html tag textarea.
     * - tag: Html tag used in show mode.
     */
    protected function getDefaultConfigs()
    {
        return array_merge(parent::getDefaultConfigs(), [
            'cols' => 40,
            'rows' => 4,
            'tag'  => 'div',
        ]);
    }


    /**
     * {@inheritdoc}
     */
    protected function getOutputForEditMode()
    {
        $cols = $this->getCols();
        $rows = $this->getRows();

        $output = "<textarea" .
            $this->getClassHtml() .
            $this->getIdHtml() . "\n  " .
            trim($this->getNameHtml() . " rows='{$rows}' cols='{$cols}'") .
            $this->getRawAttributesHtml() .
            ">" .
            $this->getValueHtml(ElementMode::EDIT) .
            "</textarea>";

        return $output;
    }


    /**
     * {@inheritdoc}
     */
    protected function getOutputForShowMode()
    {
        $tag = $this->getTag();

        $output = "<$tag" .
            $this->getClassHtml() .
            $this->getIdHtml() .
            $this->getRawAttributesHtml() .
            ">" .
            $this->getValueHtml(ElementMode::SHOW) .
            "</$tag>";

        return $output;
    }


    /**
     * @return  int
     */
    protected function getRows()
    {
        return $this->getConfig('rows');
    }


    /**
     * @return  string
     */
    protected function getTag()
    {
        return $this->getConfig('tag');
    }


    /**
     * {@inheritdoc}
     *
     * Value should be save/load in original format, no encode or escape.
     *
     * In edit mode, and '\n' will be kept.
     */
    protected function getValueHtml($mode = null, $encode = true)
    {
        $value = $this->getValue();
        $mode = $this->getMode($mode);

        if (ElementMode::EDIT == $mode) {
            $value = htmlspecialchars($value);

            // Upper encode will convert \n to <br>, need to be removed for
            // proper show in textarea.  Before this we need unite \n and \r,
            // for do <br> replacement.

            // Merge \n and \r
            $value = str_replace(["\r\n", "\n\r"], "\n", $value);

            // Remove generated <br>
            $value =
                str_ireplace(["<br>\n", "<br/>\n", "<br />\n"], "\n", $value);

        } else {
            $stringUtil = UtilContainer::getInstance()->getString();

            $value = $stringUtil->encodeHtml($value, false, true, true);
        }

        return $value;
    }
}
