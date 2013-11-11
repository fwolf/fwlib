<?php
namespace Fwlib\Html\TextDocument;

use Fwlib\Base\AbstractAutoNewConfig;
use Fwlib\Util\ArrayUtil;
use Fwlib\Util\FileSystem;
use Fwlib\Util\HttpUtil;
use Fwlib\Util\NumberUtil;
use Fwlib\Util\StringUtil;

/**
 * Viewer of text document
 *
 * Config:
 *  - className     Class name of top level div, for html output
 *  - dir           Document dir, with tailing '/', needed
 *  - exclude       Exclude dir/file in list, wildcard *? supported, default: '^\.*'
 *  - paramFile     Get parameter of file, default: 'f'
 *  - paramRaw      Get Parameter of raw view, default: 'view', value must be 'raw'
 *  - recursive     Scan document dir recursive, default: true
 *  - showFileSize  Default: false
 *  - rawView       Allow view of un-converted raw text, default: false
 *  - timeFormat    Default: 'Y-m-d H:i:s'
 *  - titleTail     Tail string of html title
 *
 * Supported text format:
 *  - Markdown
 *  - Restructuredtext
 *  - Txt(as Markdown)
 *  - Unknown(print raw)
 *
 * @package     Fwlib\Html\TextDocument
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-11-11
 */
class DocumentView extends AbstractAutoNewConfig
{
    /**
     * Markdown converter
     *
     * @var Fwlib\Html\TextDocument\Markdown
     */
    public $markdown = null;

    /**
     * Restructuredtext converter
     *
     * @var Fwlib\Html\TextDocument\Restructuredtext
     */
    public $restructuredtext = null;

    /**
     * Html title
     *
     * Generate when display.
     *
     * @var string
     */
    public $title = '';

    /**
     * UnknownMarkup converter
     *
     * @var Fwlib\Html\TextDocument\UnknownMarkup
     */
    public $unknownMarkup = null;


    /**
     * Constructor
     *
     * @param   array   $config
     */
    public function __construct($config = null)
    {
        // Unset for autonew
        unset($this->markdown);
        unset($this->restructuredtext);
        unset($this->unknownMarkup);

        parent::__construct($config);
    }


    /**
     * Display html output
     *
     * @param   boolean $returnOnly
     * @return  string
     */
    public function display($returnOnly = false)
    {
        $arFile = $this->listFile();
        if (empty($arFile)) {
            return null;
        }

        $html = '';
        $file = HttpUtil::getGet($this->config['paramFile']);
        if (empty($file)) {
            $html = $this->displayIndex($arFile, $returnOnly);
        } else {
            $html = $this->displayFile($file, $returnOnly);
        }

        return $html;
    }


    /**
     * Display document body
     *
     * @param   string  $file
     * @param   boolean $returnOnly
     * @return  string
     */
    public function displayFile($file, $returnOnly = false)
    {
        $type = $this->getDocumentType($file);
        $converter = $this->getDocumentConverter($type);

        $this->title = $converter->getTitle($file) .
            " - {$this->config['titleTail']}";

        $view = HttpUtil::getGet($this->config['paramRaw']);
        if ('raw' == $view) {
            $html = $converter->convertRaw($file);
        } else {
            $html = $converter->convert($file);
        }

        if (!$returnOnly) {
            echo $html;
        }
        return $html;
    }


    /**
     * Display document index
     *
     * @param   array   $arFile
     * @param   boolean $returnOnly
     * @return  string
     */
    public function displayIndex($arFile, $returnOnly = false)
    {
        $this->title = $this->config['titleTail'];

        $html = "<div class='{$this->config['className']}'>
  <table>
    <thead>
      <tr>";

        foreach (array('FileName', 'Title', 'Last Modified') as $v) {
            $html .= "
        <th>$v</th>";
        }
        if ($this->config['showFileSize']) {
            $html .= "
        <th>FileSize</th>";
        }

        $html .= "
      </tr>
    </thead>

    <tbody>
";


        foreach ($arFile as $k => $file) {
            $html .= "
      <tr>";

            $filename = $file['name'];
            $link = "?{$this->config['paramFile']}=" . addslashes($filename);
            $title = $this->getDocumentTitle($filename);
            $time = date($this->config['timeFormat'], $file['mtime']);
            $size = NumberUtil::toHumanSize($file['size']);

            if ($this->config['rawView']) {
                $linkRaw = $link . '&' . $this->config['paramRaw'] . '=raw';
                $html .= "
        <td><a href='$linkRaw'>$filename</a></td>";
            } else {
                $html .= "
        <td>$filename</td>";
            }

            $html .= "
        <td><a href='$link'>$title</a></td>
        <td>$time</td>";

            if ($this->config['showFileSize']) {
                $html .= "
        <td>$size</td>";
            }

            $html .= "
      </tr>
";
        }


        $html .= "
    </tbody>
  </table>
</div>
";

        if (!$returnOnly) {
            echo $html;
        }
        return $html;
    }


    /**
     * Remove exclude file from list
     *
     * @param   array   $arFile
     * @return  array
     */
    protected function excludeFile($arFile)
    {
        foreach ($arFile as $k => $v) {
            foreach ((array)$this->config['exclude'] as $rule) {
                if (StringUtil::matchWildcard($v['name'], $rule)) {
                    unset($arFile[$k]);
                    break;
                }
            }
        }

        return $arFile;
    }


    /**
     * Get document converter object by type
     *
     * @param   string  $type
     * @return  Fwlib\Html\TextDocument\AbstractTextConverter
     */
    public function getDocumentConverter($type)
    {
        $type = ucfirst($type);

        switch ($type) {
            case 'Markdown':
                return $this->markdown;
                break;
            case 'Restructuredtext':
                return $this->restructuredtext;
                break;
            case 'Unknown':
                return $this->unknownMarkup;
                break;
            default:
                throw new \Exception(
                    "Text document converter for type $type is not valid."
                );
        }
    }


    /**
     * Get document title, to show in index
     *
     * @param   string  $filename
     * @return  string
     */
    public function getDocumentTitle($filename)
    {
        $type = $this->getDocumentType($filename);

        $converter = $this->getDocumentConverter($type);

        return $converter->getTitle($filename);
    }


    /**
     * Get document type
     *
     * @param   string  $filename
     * @return  string
     */
    public function getDocumentType($filename)
    {
        $ar = array(
            ''         => 'Unknown',
            'md'       => 'Markdown',
            'markdown' => 'Markdown',
            'rst'      => 'Restructuredtext',
            'rest'     => 'Restructuredtext',
            'txt'      => 'Markdown',
        );

        $ext = FileSystem::getFileExt($filename);

        return ArrayUtil::getIdx($ar, $ext, 'Unknown');
    }


    /**
     * List files
     *
     * @return  array
     */
    protected function listFile()
    {
        $arFile = array();

        $arDir = (array)$this->config['dir'];
        foreach ($arDir as $dir) {
            foreach (FileSystem::listDir($dir) as $file) {
                $fullpath = $dir . $file['name'];

                if (is_dir($fullpath) && $this->config['recursive']) {
                    $arDir[] = $fullpath;
                } elseif (is_file($fullpath)) {
                    $arFile[] = $file;
                }

            }
        }

        $arFile = $this->excludeFile($arFile);
        $arFile = $this->sortFile($arFile);

        return $arFile;
    }


    /**
     * New Markdown object
     *
     * @return  Fwlib\Html\TextDocument\Markdown
     */
    protected function newObjMarkdown()
    {
        $this->checkServiceContainer();

        return $this->serviceContainer->get('Markdown');
    }


    /**
     * New Restructuredtext object
     *
     * @return  Fwlib\Html\TextDocument\Restructuredtext
     */
    protected function newObjRestructuredtext()
    {
        $this->checkServiceContainer();

        return $this->serviceContainer->get('Restructuredtext');
    }


    /**
     * New UnknownMarkup object
     *
     * @return  Fwlib\Html\TextDocument\UnknownMarkup
     */
    protected function newObjUnknownMarkup()
    {
        $this->checkServiceContainer();

        return $this->serviceContainer->get('UnknownMarkup');
    }


    /**
     * Set default config
     */
    protected function setConfigDefault()
    {
        $this->setConfig(
            array(
                'className'     => 'documentView',
                'exclude'       => array('^\.*'),
                'paramFile'     => 'f',
                'paramRaw'      => 'view',
                'rawView'       => false,
                'recursive'     => true,
                'showFileSize'  => false,
                'timeFormat'    => 'Y-m-d H:i:s',
                'titleTail'     => 'Document of Fwlib',
            )
        );
    }


    /**
     * Set document converter object
     *
     * @param   string  $type
     * @param   object  $converter
     */
    public function setConverter($type, $converter = null)
    {
        $this->{$type . 'Converter'} = $converter;
    }


    /**
     * Sort files
     *
     * @param   array   $arFile
     * @return  array
     */
    protected function sortFile($arFile)
    {
        return ArrayUtil::sortByLevel2($arFile, 'name', 'ASC');
    }
}
