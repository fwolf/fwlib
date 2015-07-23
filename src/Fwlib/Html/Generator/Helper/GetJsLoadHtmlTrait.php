<?php
namespace Fwlib\Html\Generator\Helper;

/**
 * Use this method to generate js load html and avoid duplicate load.
 *
 * @method  string  getJsPath()
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait GetJsLoadHtmlTrait
{
    /**
     * Get html to load js
     *
     * For convenient to combine with other output, tailed with '\n', the
     * `script` tag should take a whole line.
     *
     * @return  string
     */
    protected function getJsLoadHtml()
    {
        static $isLoaded = false;

        if ($isLoaded) {
            return '';
        } else {
            $isLoaded = true;
        }

        $jsPath = $this->getJsPath();

        $output = <<<TAG
<script type='text/javascript' src='$jsPath'></script>\n
TAG;

        return $output;
    }
}
