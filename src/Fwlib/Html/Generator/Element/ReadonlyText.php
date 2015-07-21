<?php
namespace Fwlib\Html\Generator\Element;

/**
 * Readonly text
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ReadonlyText extends Text
{
    /**
     * {@inheritdoc}
     */
    protected function getOutputForEditMode()
    {
        return $this->getOutputForShowMode();
    }
}
