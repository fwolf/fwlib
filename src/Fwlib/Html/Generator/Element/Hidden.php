<?php
namespace Fwlib\Html\Generator\Element;

use Fwlib\Html\Generator\AbstractElement;
use Fwlib\Html\Generator\ElementMode;

/**
 * Hidden input
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Hidden extends AbstractElement
{
    /**
     * {@inheritdoc}
     */
    protected function getOutputForShowMode()
    {
        $output = "<input type='hidden'" .
            $this->getClassHtml() .
            $this->getIdHtml() . "\n  " .
            trim($this->getNameHtml()) .
            $this->getValueHtml(ElementMode::EDIT) .
            $this->getRawAttributes() .
            " />"
        ;

        return $output;
    }
}
