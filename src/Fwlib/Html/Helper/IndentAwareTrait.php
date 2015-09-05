<?php
namespace Fwlib\Html\Helper;

use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
trait IndentAwareTrait
{
    /**
     * Indent a string
     *
     * @see StringUtil::indent()
     *
     * @param   string $str
     * @param   int    $width         Must > 0
     * @param   string $spacer        Which char is used to indent
     * @param   string $lineEnding    Original string's line ending
     * @param   bool   $fillEmptyLine Add spacer to empty line ?
     * @return  string
     */
    protected function indent(
        $str,
        $width,
        $spacer = ' ',
        $lineEnding = "\n",
        $fillEmptyLine = false
    ) {
        if (1 > $width) {
            return $str;
        }

        $stringUtil = UtilContainer::getInstance()->getString();

        return $stringUtil
            ->indent($str, $width, $spacer, $lineEnding, $fillEmptyLine);
    }


    /**
     * Indent a html string
     *
     * @see StringUtil::indent()
     *
     * @param   string $html
     * @param   int    $width         Must > 0
     * @param   string $spacer        Which char is used to indent
     * @param   string $lineEnding    Original string's line ending
     * @param   bool   $fillEmptyLine Add spacer to empty line ?
     * @return  string
     */
    protected function indentHtml(
        $html,
        $width,
        $spacer = ' ',
        $lineEnding = "\n",
        $fillEmptyLine = false
    ) {
        if (1 > $width) {
            return $html;
        }

        $stringUtil = UtilContainer::getInstance()->getString();

        return $stringUtil
            ->indentHtml($html, $width, $spacer, $lineEnding, $fillEmptyLine);
    }
}
