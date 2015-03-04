<?php
namespace Fwlib\Html\TextDocument;

use Fwlib\Config\ConfigAwareTrait;
use Fwlib\Util\UtilContainerAwareTrait;

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
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class DocumentView
{
    use ConfigAwareTrait;
    use UtilContainerAwareTrait;


    /**
     * Converter instances
     *
     * @var AbstractTextConverter[]     Index by converter base class name
     */
    protected $converters = [];

    /**
     * Current document type
     * Index for index page, Unknown for unknown type.
     *
     * @var string
     */
    public $currentDocumentType = 'Index';

    /**
     * Html title
     *
     * Generate when display.
     *
     * @var string
     */
    public $title = '';


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

        $file = $this->getUtilContainer()->getHttp()
            ->getGet($this->getConfig('paramFile'));

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
        $this->currentDocumentType = $type;
        $converter = $this->getDocumentConverter($type);

        $this->title = $converter->getTitle($file);

        $view = $this->getUtilContainer()->getHttp()
            ->getGet($this->getConfig('paramRaw'));
        if ('raw' == $view) {
            $html = $converter->convertRaw($file);
        } else {
            $html = $converter->convert($file);
        }

        $html = "<article class='{$this->getConfig('className')}'>\n\n$html
</article>\n";

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
        $this->currentDocumentType = 'Index';
        $this->title = $this->getConfig('titleTail');

        $numberUtil = $this->getUtilContainer()->getNumber();

        $html = "<div class='{$this->getConfig('className')}'>
  <table class='index'>
    <thead>
      <tr>";

        foreach (['File Name', 'Title', 'Last Modified'] as $v) {
            $html .= "
        <th>$v</th>";
        }
        if ($this->getConfig('showFileSize')) {
            $html .= "
        <th>File Size</th>";
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
            $link = "?{$this->getConfig('paramFile')}=" . addslashes($filename);
            $title = $this->getDocumentTitle($filename);
            $time = date($this->getConfig('timeFormat'), $file['mtime']);
            $size = strtolower($numberUtil->toHumanSize($file['size']));

            if ($this->getConfig('rawView')) {
                $linkRaw = $link . '&' . $this->getConfig('paramRaw') . '=raw';
                $html .= "
        <td class='document-filename'><a href='$linkRaw'>$filename</a></td>";
            } else {
                $html .= "
        <td class='document-filename'>$filename</td>";
            }

            $html .= "
        <td class='document-title'><a href='$link'>$title</a></td>
        <td class='document-mtime'>$time</td>";

            if ($this->getConfig('showFileSize')) {
                $html .= "
        <td class='document-size'>$size</td>";
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
        $stringUtil = $this->getUtilContainer()->getString();

        foreach ($arFile as $k => $v) {
            foreach ((array)$this->getConfig('exclude') as $rule) {
                if ($stringUtil->matchWildcard($v['name'], $rule)) {
                    unset($arFile[$k]);
                    break;
                }
            }
        }

        return $arFile;
    }


    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfigs()
    {
        $configs = [
            'className'     => 'document-view',
            'exclude'       => ['^\.*'],
            'paramFile'     => 'f',
            'paramRaw'      => 'view',
            'rawView'       => false,
            'recursive'     => true,
            'showFileSize'  => false,
            'timeFormat'    => 'Y-m-d H:i:s',
            'titleTail'     => 'Document in Fwlib',
        ];

        return $configs;
    }


    /**
     * Get document converter object by type
     *
     * @param   string  $type
     * @return  AbstractTextConverter
     */
    public function getDocumentConverter($type)
    {
        $type = ucfirst($type);

        switch ($type) {
            case 'Markdown':
                return $this->getMarkdown();
                break;
            case 'Restructuredtext':
                return $this->getRestructuredtext();
                break;
            case 'Unknown':
                return $this->getUnknownMarkup();
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
        $ar = [
            ''         => 'Unknown',
            'md'       => 'Markdown',
            'markdown' => 'Markdown',
            'rst'      => 'Restructuredtext',
            'rest'     => 'Restructuredtext',
            'txt'      => 'Markdown',
        ];

        $ext = $this->getUtilContainer()->getFileSystem()->getFileExt($filename);

        $arrayUtil = $this->getUtilContainer()->getArray();
        return $arrayUtil->getIdx($ar, $ext, 'Unknown');
    }


    /**
     * Get Markdown converter instance
     *
     * @return  Markdown
     */
    protected function getMarkdown()
    {
        if (is_null($this->converters['Markdown'])) {
            $this->converters['Markdown'] = new Markdown();
        }

        return $this->converters['Markdown'];
    }


    /**
     * Get Restructuredtext converter instance
     *
     * @return  Restructuredtext
     */
    protected function getRestructuredtext()
    {
        if (is_null($this->converters['Restructuredtext'])) {
            $this->converters['Restructuredtext'] = new Restructuredtext();
        }

        return $this->converters['Restructuredtext'];
    }


    /**
     * New UnknownMarkup converter instance
     *
     * @return  UnknownMarkup
     */
    protected function getUnknownMarkup()
    {
        if (is_null($this->converters['UnknownMarkup'])) {
            $this->converters['UnknownMarkup'] = new UnknownMarkup();
        }

        return $this->converters['UnknownMarkup'];
    }


    /**
     * List files
     *
     * @return  array
     */
    protected function listFile()
    {
        $arFile = [];

        $arDir = (array)$this->getConfig('dir');
        $fileSystem = $this->getUtilContainer()->getFileSystem();
        foreach ($arDir as $dir) {
            foreach ($fileSystem->listDir($dir) as $file) {
                $fullPath = $dir . $file['name'];

                if (is_dir($fullPath) && $this->getConfig('recursive')) {
                    $arDir[] = $fullPath;
                } elseif (is_file($fullPath)) {
                    $arFile[] = $file;
                }

            }
        }

        $arFile = $this->excludeFile($arFile);
        $arFile = $this->sortFile($arFile);

        return $arFile;
    }


    /**
     * Setter of $converter
     *
     * @param   AbstractTextConverter   $converter
     * @param   string                  $baseClassName
     * @return  static
     */
    public function setConverter(
        AbstractTextConverter $converter,
        $baseClassName = ''
    ) {
        if (empty($baseClassName)) {
            $className = get_class($converter);
            $baseClassName =
                implode('', array_slice(explode('\\', $className), -1));
        }

        $this->converters[$baseClassName] = $converter;

        return $this;
    }


    /**
     * Sort files
     *
     * @param   array   $arFile
     * @return  array
     */
    protected function sortFile($arFile)
    {
        $arrayUtil = $this->getUtilContainer()->getArray();

        return $arrayUtil->sortByLevel2($arFile, 'name', 'ASC');
    }
}
