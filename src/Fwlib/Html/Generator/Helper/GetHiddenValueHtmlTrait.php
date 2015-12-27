<?php
namespace Fwlib\Html\Generator\Helper;

use Fwlib\Html\Generator\Element\Hidden;

/**
 * When element in show mode, hidden value input are needed to include it in
 * POST when form are submitted.
 *
 * @method  string  getName()
 * @method  mixed   getValue()
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait GetHiddenValueHtmlTrait
{
    /**
     * Get output of hidden input
     *
     * @return  string
     */
    protected function getHiddenValueHtml()
    {
        if (!$this->isHiddenValueIncluded()) {
            return '';
        }

        $hidden = (new Hidden())->setName($this->getName())
            ->setValue($this->getValue());

        return $hidden->getOutput();
    }


    /**
     * Should hidden value input included in element output ?
     *
     * @return  bool
     */
    protected function isHiddenValueIncluded()
    {
        return true;
    }
}
