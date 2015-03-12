<?php
namespace Fwlib\Util\Common;

/**
 * String util
 *
 * Util class is collection of functions, will not keep state, so method
 * amount and class complexity should have no limit.
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @copyright   Copyright 2004-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class StringUtil
{
    /**
     * Addslashes for any string|array, recursive
     *
     * @param   mixed   $source
     * @return  mixed
     */
    public function addSlashesRecursive($source)
    {
        if (empty($source)) {
            return $source;
        }

        if (is_string($source)) {
            return addslashes($source);
        } elseif (is_array($source)) {
            $rs = [];
            foreach ($source as $k => $v) {
                $rs[addslashes($k)] = $this->addSlashesRecursive($v);
            }
            return $rs;
        } else {
            // Other data type, return original
            return $source;
        }
    }


    /**
     * Encode string for html output
     *
     * @param   string  $str
     * @param   boolean $stripSlashes
     * @param   boolean $nl2br
     * @param   boolean $optimizeSpaces
     * @return  string
    */
    public function encodeHtml(
        $str,
        $stripSlashes = true,
        $nl2br = true,
        $optimizeSpaces = true
    ) {
        if ($stripSlashes) {
            $str = stripSlashes($str);
        }

        $str = htmlentities($str, ENT_QUOTES, 'UTF-8');

        if ($optimizeSpaces) {
            $ar = [
                '  '    => '&nbsp; ',
                ' '     => '&nbsp;',
                '&nbsp;&nbsp;'  => '&nbsp; ',
            ];
            $str = str_replace(array_keys($ar), array_values($ar), $str);
        }

        if ($nl2br) {
            $str = nl2br($str, true);
        }

        return $str;
    }


    /**
     * Encode array of string for html output
     *
     * @param   array   $stringArray
     * @param   boolean $stripSlashes
     * @param   boolean $nl2br
     * @param   boolean $optimizeSpaces
     * @return  string
    */
    public function encodeHtmls(
        array $stringArray,
        $stripSlashes = true,
        $nl2br = true,
        $optimizeSpaces = true
    ) {
        foreach ($stringArray as &$string) {
            $string = $this->encodeHtml(
                $string,
                $stripSlashes,
                $nl2br,
                $optimizeSpaces
            );
        }
        unset($string);

        return $stringArray;
    }


    /**
     * Indent a string
     *
     * The first line will also be indented.
     *
     * Commonly used in generate and combine html, fix indents.
     *
     * The $indentChar should consider width equals = 1, if not, the real
     * indent width is mb_strwidth($indentChar) * $width .
     *
     * @param   string  $str
     * @param   int     $width      Must > 0
     * @param   string  $spacer     Which char is used to indent
     * @param   string  $lineEnding Original string's line ending
     * @return  string
     */
    public function indent($str, $width, $spacer = ' ', $lineEnding = "\n")
    {
        $space = str_repeat($spacer, $width);

        $str = $space . str_replace($lineEnding, $lineEnding . $space, $str);

        return $str;
    }


    /**
     * Indent a html string, except value of some html tag like textarea
     *
     * Html string should have both start and end tag of html tag.
     *
     * Works for html tag:
     *  - textarea

     * @param   string  $html
     * @param   int     $width      Must > 0
     * @param   string  $spacer     Which char is used to indent
     * @param   string  $lineEnding Original string's line ending
     * @return  string
     */
    public function indentHtml(
        $html,
        $width,
        $spacer = ' ',
        $lineEnding = "\n"
    ) {
        // Find textarea start point
        $i = stripos($html, '<textarea>');
        if (false === $i) {
            $i = stripos($html, '<textarea ');
        }
        if (false === $i) {
            return $this->indent($html, $width, $spacer, $lineEnding);

        } else {
            $htmlBefore = substr($html, 0, $i);
            $htmlBefore =  $this->indent(
                $htmlBefore,
                $width,
                $spacer,
                $lineEnding
            );

            // Find textarea end point
            $html = substr($html, $i);
            $i = stripos($html, '</textarea>');
            if (false === $i) {
                // Should not happen, source html format error
                $htmlAfter = '';

            } else {
                $i += strlen('</textarea>');

                $htmlAfter = substr($html, $i);
                // In case there are another textarea in it
                $htmlAfter =  $this->indentHtml(
                    $htmlAfter,
                    $width,
                    $spacer,
                    $lineEnding
                );
                // Remove leading space
                $htmlAfter = substr($htmlAfter, 2);

                $html = substr($html, 0, $i);
            }

            return $htmlBefore . $html . $htmlAfter;
        }
    }


    /**
     * Match content using preg, return result array or string
     *
     * Return value maybe string or array, use with caution.
     *
     * @param   string  $preg
     * @param   string  $str
     * @param   boolean $simple  Convert single result to str(array -> str) ?
     * @return  string|array|null
     */
    public function matchRegex($preg, $str = '', $simple = true)
    {
        if (empty($preg) || empty($str)) {
            return null;
        }

        $i = preg_match_all($preg, $str, $matches, PREG_SET_ORDER);
        if (0 >= intval($i)) {
            // Got none match or Got error
            return null;
        }

        // Remove first element of match array, the whole match str part
        foreach ($matches as &$row) {
            if (1 < count($row)) {
                array_shift($row);
            }
            if (1 == count($row)) {
                $row = $row[0];
            }
        }
        unset($row);

        // Simplify
        if (1 == count($matches) && $simple) {
            $matches = $matches[0];
        }

        return $matches;
    }


    /**
     * Match a string with rule including wildcard
     *
     * Wildcard '*' means any number of chars, and '?' means EXACTLY one char.
     *
     * Eg: 'duck' match rule '*c?'
     *
     * @param   string  $str
     * @param   string  $rule
     * @return  boolean
     */
    public function matchWildcard($str, $rule)
    {
        // Convert wildcard rule to regex
        $rule = str_replace('*', '.*', $rule);
        $rule = str_replace('?', '.{1}', $rule);
        $rule = '/' . $rule . '/';

        // Must match whole string, same length
        if ((1 == preg_match($rule, $str, $matches))
            && (strlen($matches[0]) == strlen($str))
        ) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Generate random string
     *
     * In $mode:
     *  a means include a-z
     *  A means include A-Z
     *  0 means include 0-9
     *
     * @param   int     $len
     * @param   string  $mode
     * @return  string
     */
    public function random($len, $mode = 'a0')
    {
        $str = '';
        if (preg_match('/[a]/', $mode)) {
            $str .= 'abcdefghijklmnopqrstuvwxyz';
        }
        if (preg_match('/[A]/', $mode)) {
            $str .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if (preg_match('/[0]/', $mode)) {
            $str .= '0123456789';
        }

        $result = '';
        $strLen = strlen($str);

        // Algorithm
        // 1. rand by str length, faster than 2
        // 2. rand then mode by str length
        for ($i = 0; $i < $len; $i ++) {
            $result .= $str[mt_rand(0, $strLen - 1)];
        }
        return $result;
    }


    /**
     * Get substr by display width, and ignore html tag's length
     *
     * Using mb_strimwidth()
     *
     * Notice: No consider of html complement, all html tag treat as zero
     * length.
     *
     * Notice: Self close tag need use style as <br />, not <br>, for correct
     * html tag depth compute.
     *
     * @param   string $str      Source string
     * @param   int    $length   Length
     * @param   string $marker   If str length exceed, cut & fill with this
     * @param   string $encoding Default is utf-8
     * @return  string
     * @link http://www.fwolf.com/blog/post/133
     */
    public function substrIgnoreHtml(
        $str,
        $length,
        $marker = '...',
        $encoding = 'utf-8'
    ) {
        $str = htmlspecialchars_decode($str);

        $i = preg_match_all('/<[^>]*>/i', $str, $matches);
        if (0 == $i) {
            // No html in $str
            $str = mb_strimwidth($str, 0, $length, $marker, $encoding);
            $str = htmlspecialchars($str);

            return $str;
        }

        // Have html tags, need split str into parts by html
        $matches = $matches[0];

        $arParts = [];
        foreach ($matches as $match) {
            // Find position of match in source string
            $pos = strpos($str, $match);

            // Add 2 parts by position
            // Part 1 is normal text before matched html tag
            $part = substr($str, 0, $pos);
            $arParts[] = [
                'content' => $part,
                'depth'   => 0,
                'width'   => mb_strwidth($part, $encoding),
            ];

            // Part 2 is html tag
            if (0 < preg_match('/\/\s*>/', $match)) {
                // Self close tag
                $depth = 0;
            } elseif (0 < preg_match('/<\s*\//', $match)) {
                // End tag
                $depth = -1;
            } else {
                $depth = 1;
            }
            $arParts[] = [
                'content' => $match,
                'depth'   => $depth,
                'width'   => 0,
            ];

            // Cut source string for next loop
            $str = substr($str, $pos + strlen($match));
        }

        // All left part of source str, after all html tags
        $arParts[] = [
            'content' => $str,
            'depth'   => 0,
            'width'   => mb_strwidth($str, $encoding),
        ];

        // Remove empty parts
        $arParts = array_filter($arParts, function ($part) {
            return 0 < strlen($part['content']);
        });

        // Loop to cut needed length
        $result = '';
        $totalDepth = 0;
        foreach ($arParts as $part) {
            if (0 >= $length && 0 == $totalDepth) {
                break;
            }

            $width = $part['width'];
            if (0 == $width) {
                $result .= $part['content'];
                $totalDepth += $part['depth'];

            } else {
                $result .= htmlspecialchars(mb_strimwidth(
                    $part['content'],
                    0,
                    max($length, 0),
                    $marker,
                    $encoding
                ));
                $length -= $width;
            }
        }

        return $result;
    }


    /**
     * Convert string to array by splitter
     *
     * @param   string  $source
     * @param   string  $splitter
     * @param   boolean $trim
     * @param   boolean $removeEmpty
     * @return  array
     */
    public function toArray(
        $source,
        $splitter = ',',
        $trim = true,
        $removeEmpty = true
    ) {
        if (!is_string($source)) {
            $source = strval($source);
        }

        $rs = explode($splitter, $source);

        if ($trim) {
            foreach ($rs as &$v) {
                $v = trim($v);
            }
            unset($v);
        }

        if ($removeEmpty) {
            foreach ($rs as $k => $v) {
                if (empty($v)) {
                    unset($rs[$k]);
                }
            }
            // Re generate array index
            $rs = array_merge($rs, []);
        }

        return $rs;
    }


    /**
     * Convert to camelCase
     *
     * @param   string  $source
     * @return  string
     */
    public function toCamelCase($source)
    {
        return lcfirst($this->toStudlyCaps($source));
    }


    /**
     * Convert to snake case
     *
     * @param   string  $source
     * @param   string  $separator
     * @param   boolean $ucfirstWords
     * @return  string
     */
    public function toSnakeCase(
        $source,
        $separator = '_',
        $ucfirstWords = false
    ) {
        // Split to words
        $s = preg_replace('/([A-Z])/', ' \1', $source);

        // Remove leading space
        $s = trim($s);

        // Merge non-words char and replace by space
        $s = preg_replace('/[ _\-\.]+/', ' ', $s);

        if ($ucfirstWords) {
            $s = ucwords($s);
        } else {
            $s = strtolower($s);
        }

        // Replace space with separator
        $s = str_replace(' ', $separator, $s);

        return $s;
    }


    /**
     * Convert to StudlyCaps
     *
     * @param   string  $source
     * @return  string
     */
    public function toStudlyCaps($source)
    {
        return $this->toSnakeCase($source, '', true);
    }
}
