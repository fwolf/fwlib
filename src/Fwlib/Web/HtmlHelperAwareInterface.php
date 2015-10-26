<?php
namespace Fwlib\Web;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
interface HtmlHelperAwareInterface
{
    /**
     * @return  HtmlHelper
     */
    public function getHtmlHelper();


    /**
     * @param   HtmlHelper $htmlHelper
     * @return  static
     */
    public function setHtmlHelper(HtmlHelper $htmlHelper);
}
