<?php
namespace Fwlib\Web;

/**
 * Trait for easy replace HtmlHelper instance
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait HtmlHelperAwareTrait
{
    /**
     * @var HtmlHelper
     */
    protected $htmlHelper = null;


    /**
     * @return  HtmlHelper
     */
    public function getHtmlHelper()
    {
        return is_null($this->htmlHelper)
            ? HtmlHelper::getInstance()
            : $this->htmlHelper;
    }


    /**
     * @param   HtmlHelper  $htmlHelper
     * @return  static
     */
    public function setHtmlHelper(HtmlHelper $htmlHelper)
    {
        $this->htmlHelper = $htmlHelper;

        return $this;
    }
}
