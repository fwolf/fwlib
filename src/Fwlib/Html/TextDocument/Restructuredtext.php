<?php
namespace Fwlib\Html\TextDocument;

use Fwlib\Html\TextDocument\AbstractTextConverter;

/**
 * Text converter for reStructuredText
 *
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Restructuredtext extends AbstractTextConverter
{
    /**
     * Cmd options when used rst2xxx.py
     *
     * Param is combined in, eg: tab-width=4
     *
     * @var array
     */
    public $cmdOption = [
        'embed-stylesheet',
        //'link-stylesheet',

        // h1 is for title, document section/title start from h2
        'initial-header-level=2',

        //'no-doc-title',
        //'no-xml-declaration',
        'cloak-email-addresses',
    ];

    /**
     * Actual path of docutils execute file
     *
     * @var string
     * @see setPathDocutils()
     */
    protected $pathDocutils = '';

    /**
     * Use pipe to execute cmd, not use temporary file
     *
     * @var boolean
     */
    public $usePipe = true;


    /**
     * Constructor
     *
     * @var param   string  $pathDocutils
     */
    public function __construct($pathDocutils = '')
    {
        // Convert html only, leave CSS link from outside
        $this->setPathDocutils($pathDocutils);
    }


    /**
     * Convert string to html
     *
     * I had run benchmark to compare pipe and tmp file before migrate to PSR
     * standard, result is, their speed  are almost same.
     *
     * @param   string  $str
     * @param   boolean $bodyOnly
     * @return  string
     */
    public function convertString($str, $bodyOnly = true)
    {
        if (!is_executable($this->pathDocutils)) {
            throw new \Exception('Cannot found docutils executable.');
        }


        $cmd = $this->pathDocutils . $this->genCmdOptionString();
        $cmd = escapeshellcmd($cmd);

        if ($this->usePipe) {
            $desc = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
            ];

            $proc = proc_open($cmd, $desc, $pipes);
            if (!is_resource($proc)) {
                throw new \Exception('Open pipe fail.');
            }

            $fp = $pipes[0];
            fwrite($fp, $str);
            fflush($fp);
            fclose($fp);

            $html = '';
            while (!feof($pipes[1])) {
                $html .= fgets($pipes[1]);
            }
            fclose($pipes[1]);

            proc_close($proc);

        } else {
            // Use tmp file
            $tmpFile = tempnam(sys_get_temp_dir(), 'fwlib-html-restructuredtext-');
            file_put_contents($tmpFile, $str);

            // Execute cmd, got result
            $cmd .= " $tmpFile";
            exec($cmd, $output);

            unlink($tmpFile);
            $html = implode("\n", $output);
        }


        if ($bodyOnly) {
            // Trim html, only leave content in <body>
            $i = strpos($html, '<body>');
            if (!(false === $i)) {
                $html = substr($html, $i + 6);
            }
            $i = strrpos($html, '</body>');
            if (!(false === $i)) {
                $html = substr($html, 0, $i);
            }
        }


        return $html;
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

        // Title adornment character
        $tac = '(=+|-+|\\`+|\\:+|\\.+|\\\'+|\\"+|\\~+|\\^+|_+|\\*+|\\++|\\#+)';

        $i = preg_match("/$tac\\s*\\n(.+?)\\n\\1\\s*\\n/mx", $source, $ar);

        if (0 == $i) {
            // Not found
            return null;
        } else {
            return $ar[2];
        }
    }



    /**
     * Gen string include cmd option for exec
     *
     * @return  string
     */
    protected function genCmdOptionString()
    {
        if (empty($this->cmdOption)) {
            return '';
        } else {
            $s = ' ';
            foreach ($this->cmdOption as $v) {
                // Single char param without '-'
                if (1 == strlen($v)) {
                    $v = '-' . $v;
                } elseif (1 < strlen($v) && '--' != substr($v, 0, 2)) {
                    // Multi char param without '--'
                    $v = '--' . $v;
                }

                $s .= $v . ' ';
            }

            return $s;
        }
    }


    /**
     * Set path of docutils or detect it
     *
     * @param   $path   Manual additional path
     * @return  string
     */
    public function setPathDocutils($path = '')
    {
        // Possible path of docutils execute file for choose
        $possiblePath = [
            '/usr/bin/',
            '/usr/local/bin/',
            '/bin/',
        ];

        if (!empty($path)) {
            // Prepend to array
            array_unshift($possiblePath, $path);
        }

        // Find a usable path
        $found = false;
        while (!$found && !empty($possiblePath)) {
            $path = array_shift($possiblePath);

            if (is_executable($path . 'rst2html.py')) {
                $found = true;
                $this->pathDocutils = $path . 'rst2html.py';
                break;
            }

            // In some env like my (MT) CentOS 5, cmd hasn't .py extension
            if (is_executable($path . 'rst2html')) {
                $found = true;
                $this->pathDocutils = $path . 'rst2html';
                break;
            }
        }

        return $this->pathDocutils;
    }
}
