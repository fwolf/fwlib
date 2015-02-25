<?php
namespace Fwlib\Util;

use Fwlib\Util\AbstractUtilAware;

/**
 * Escape color for bash shell
 *
 * Normally used in cli/bash mode.
 *
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2006-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 * @link http://linuxgazette.net/issue65/padala.html
 */
class EscapeColor extends AbstractUtilAware
{
    /**
     * Dict: attr
     */
    protected $dictAttr = [
        'reset'     => 0,    // Reset All Attributes (return to normal mode)
        'bright'    => 1,    // Bright (Usually turns on BOLD)
        'bold'      => 1,    // Bright (Usually turns on BOLD)
        'dim'       => 2,
        'underline' => 3,
        //'reset'     => 4,
        'blink'     => 5,
        'reverse'   => 7,
        'hidden'    => 8,
    ];

    /**
     * Dict: bg color
     */
    protected $dictBg = [
        'black'     => 40,
        'red'       => 41,
        'green'     => 42,
        'yellow'    => 43,
        'blue'      => 44,
        'magenta'   => 45,
        'cyan'      => 46,
        'white'     => 47,
    ];

    /**
     * Dict: fg color
     */
    protected $dictFg = [
        'black'     => 30,
        'red'       => 31,
        'green'     => 32,
        'yellow'    => 33,
        'blue'      => 34,
        'magenta'   => 35,
        'cyan'      => 36,
        'white'     => 37,
    ];

    /**
     * Set false to disable color paint
     */
    public $enabled = true;

    /**
     * Escape char to declare begin of escaped color
     */
    public $esc = "\x1b[";   // Or \033[


    /**
     * Paint text with escape color
     *
     * The Color Code: <ESC>[{attr};{fg};{bg}m
     *
     * @param   string      $str    String to convert
     * @param   int|string  $attr   Special attribute
     * @param   int|string  $fg     Forground color
     * @param   int|string  $bg     Background color
     * @return  string
     */
    public function paint($str, $attr = '', $fg = '', $bg = '')
    {
        if (!$this->enabled) {
            return $str;
        }


        $ar = [];

        if (isset($this->dictAttr[$attr])) {
            $attr = strtolower($attr);
            $ar[] = $this->dictAttr[$attr];
        } elseif (is_int($attr)) {
            $ar[] = $attr;
        }

        if (isset($this->dictFg[$fg])) {
            $fg = strtolower($fg);
            $ar[] = $this->dictFg[$fg];
        } elseif (is_int($fg)) {
            $ar[] = $fg;
        }

        if (isset($this->dictBg[$bg])) {
            $bg = strtolower($bg);
            $ar[] = $this->dictBg[$bg];
        } elseif (is_int($bg)) {
            $ar[] = $bg;
        }

        if (empty($ar)) {
            return $str;
        } else {
            return $this->esc . implode(';', $ar) . 'm'
                . $str . $this->esc . '0m';
        }

        return $s_esc . $s_attr . $s_fg . $s_bg . 'm' . $str . $s_esc . '0m';
    }


    /**
     * Print an escape color table
     *
     * Best run it in bash/cli mode.
     *
     * @param   boolean $export
     */
    public function printTable($export = false)
    {
        $output = '';
        $output .= "Table for 16-color terminal escape sequences.\n";
        $output .= "Replace ESC with \\033 in bash.\n";
        $output .= "\n";
        $output .= "Background | Foreground colors\n";
        $output .= "---------------------------------------------------------------------\n";
        for ($bg = 40; $bg <= 47; $bg ++) {
            // bold = bright
            for ($bold = 0; $bold <= 1; $bold ++) {
                $output .= $this->paint("ESC[{$bg}m   | ", $bold);
                for ($fg = 30; $fg <= 37; $fg ++) {
                    if (0 == $bold) {
                        $output .= $this->paint(" [{$fg}m  ", $bold, $fg, $bg);
                    } else {
                        $output .= $this->paint(" [$bold;{$fg}m", $bold, $fg, $bg);
                    }
                }
                $output .= "\n";
            }
        }
        $output .= "---------------------------------------------------------------------\n";

        $output = $this->getUtil('Env')->ecl($output, true);
        if (!$export) {
            echo $output;
        }

        return $output;
    }


    /**
     * Convert escape color text to html with color
     *
     * Notice: $in must be htmlspecialchars() out of func, or sth will be bad,
     * or make sure this will not appear < or > char.
     *
     * @param   string  $in     String to be convert
     * @return  string
     */
    public function toHtml($in)
    {
        // attr:
        // 0 - reset
        $in = preg_replace(
            "/\x1b\[0*m/",
            "</span>",
            $in
        );
        // 2,7,8 - dim(dark?),reverse,hidden - ignore
        $in = preg_replace(
            "/\x1b\[0?[278];([\d;]+)m/",
            "\x1b[\\1m",
            $in
        );
        // 1 - bold
        $in = preg_replace(
            "/\x1b\[0?1;([\d;]+)m/",
            "<span style=\"font-weight: bold;\">\x1b[\\1m",
            $in
        );
        // 3 - underline
        $in = preg_replace(
            "/\x1b\[0?3;([\d;]+)m/",
            "<span style=\"text-decoration: underline;\">\x1b[\\1m",
            $in
        );
        // 5 - blink
        $in = preg_replace(
            "/\x1b\[0?5;([\d;]+)m/",
            "<span style=\"text-decoration: blink;\">\x1b[\\1m",
            $in
        );


        // fg colors:
        $key = [];
        $replace = [];
        foreach ($this->dictFg as $k => $v) {
            $key[] = "/\x1b\[{$v};?(\d{0,2};?)m/";
            $replace[] = "<span style=\"color: $k;\">\x1b[\\1m";
        }
        $in = preg_replace($key, $replace, $in);


        // bg colors ??


        // Remove un-recoginized colors
        $in = preg_replace("/\x1b\[[\d;]*m/", '', $in);

        // Merge duplicate <span> markup
        $in = preg_replace(
            "/<span style=\"([^>]*)\"><span style=\"([^>]*)\">/",
            "<span style=\"\\1 \\2\">",
            $in
        );

        // Merge duplicate </span> markup
        $in = preg_replace(
            "/<\/span>([^<]*)<\/span>/",
            "</span>\\1",
            $in
        );
        $in = preg_replace(
            "/[\r\n]([^<]*)<\/span>([^<])*[\r\n]/",
            "\\1\\2",
            $in
        );
        $in = preg_replace("/[\r\n]([^<]*)<\/span>/", '\1', $in);

        // Remove \t
        $in = str_replace("\x07", '', $in);

        // Add losted </span> sometimes
        // this must run twice because the second <span> used in the 1st replace
        // will not be tract as the beginning <span> in remain search
        // it means, it was 'skipped'
        $in = preg_replace(
            "/<span([^>]*)>([^<\n]*)<span/",
            "<span\\1>\\2</span><span",
            $in
        );
        $in = preg_replace(
            "/<span([^>]*)>([^<\n]*)<span/",
            "<span\\1>\\2</span><span",
            $in
        );
        $in = preg_replace(
            "/<span([^>]*)>([^<]*)[\n\r]/",
            "<span\\1>\\2</span>\n",
            $in
        );

        // Clean escape control chars
        $escapeControl = [
            "/\x1b\\[(\\d+;)?\\d*[ABCDGJKnr]/",
            "/\x1b\\[(\\d+;)?\\d*[fH]/",
            //below is some chars which i don't know what it is .
            "/\x1b\\[\\??\\d*[hl]/",
            "/\x1b[>\\=]/",
            "/\x1b\&gt;/",
        ];
        $in = preg_replace($escapeControl, '', $in);

        return($in);
    }
}
