<?php
namespace Fwlib\Html\Generator\Element;

use Fwlib\Html\Generator\AbstractElement;

/**
 * Html button
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Button extends AbstractElement
{
    /**
     * Button type, must assign
     *
     *  - button
     *  - reset
     *  - submit
     *
     * @var string
     */
    protected $type = 'button';


    /**
     * {@inheritdoc}
     */
    protected function getOutputForShowMode()
    {
        $output = "<button" .
            $this->getTypeHtml() .
            $this->getClassHtml() .
            $this->getIdHtml() . "\n  " .
            trim($this->getNameHtml()) . ">\n  " .
            $this->getValueHtml() .
            "</button>";

        return $output;
    }


    /**
     * @return  string
     */
    protected function getType()
    {
        return empty($this->type) ? 'button' : $this->type;
    }


    /**
     * @return  string
     */
    protected function getTypeHtml()
    {
        $type = $this->getType();

        return " type='$type'";
    }
}
