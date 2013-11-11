<?php
namespace Fwlib\Html\TextDocument;

use Fwlib\Html\TextDocument\AbstractTextConverter;
use Michelf\MarkdownExtra;

/**
 * Text converter for Markdown
 *
 * @package     Fwlib\Html\TextDocument
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-11-11
 */
class Markdown extends AbstractTextConverter
{
    /**
     * Convert string to html
     *
     * @param   string  $str
     * @return  string
     */
    public function convertString($str)
    {
        return MarkdownExtra::defaultTransform($str);
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

        // Try setext style first, then atx style
        // @link https://github.com/michelf/php-markdown/blob/lib/Michelf/Markdown.php
        // @see \Michelf\Markdown(Extra)::doHeaders()
        $i = preg_match('/^(.+?)\s*\n(=+|-+)\s*\n/mx', $source, $ar);
        if (0 == $i) {
            $i = preg_match('/^\#{1,6}\s*(.+?)\s*\#*\n/mx', $source, $ar);
        }

        if (0 == $i) {
            // Still not found
            return null;
        } else {
            return $ar[1];
        }
    }
}
