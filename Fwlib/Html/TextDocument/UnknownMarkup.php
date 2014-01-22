<?php
namespace Fwlib\Html\TextDocument;

use Fwlib\Html\TextDocument\AbstractTextConverter;

/**
 * Text converter for Unknown markup
 *
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-11-11
 */
class UnknownMarkup extends AbstractTextConverter
{
    /**
     * Convert string to html
     *
     * @param   string  $str
     * @return  string
     */
    public function convertString($str)
    {
        return $this->convertRaw($str);
    }


    /**
     * Get title of text content if possible
     *
     * @param   string  $source     String or filename to convert
     * @return  string
     */
    public function getTitle($source)
    {
        if ($this->isFile($source)) {
            $source = file_get_contents($source);
        }

        // Use first 50 chars, without special chars
        $source = str_replace(
            array("\n", "\r", "\t", '/', '*', '<?php', '<?'),
            '',
            ltrim($source)
        );
        $title = mb_strimwidth($source, 0, 50, '...', 'UTF-8');

        return addslashes($title);
    }
}
