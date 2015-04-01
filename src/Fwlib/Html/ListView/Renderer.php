<?php
namespace Fwlib\Html\ListView;

use Fwlib\Config\ConfigAwareTrait;
use Fwlib\Html\ListView\Helper\ClassAndIdConfigTrait;
use Fwlib\Util\UtilContainer;

/**
 * List Renderer
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Renderer implements RendererInterface
{
    use ClassAndIdConfigTrait;
    use ConfigAwareTrait;
    use ListDtoAwareTrait;


    /**
     * @var string
     */
    protected $postContent = null;

    /**
     * @var string
     */
    protected $preContent = null;

    /**
     * @var callable
     */
    protected $rowRenderer = null;


    /**
     * {@inheritdoc}
     *
     * Result =
     *  preContent + topPager + head + body + bottomPager + postContent
     */
    public function getHtml()
    {
        $class = $this->getClass();
        $rootId = $this->getId();

        $parts = [];

        if ($this->getConfig('showTopPager')) {
            $parts[] = '<!-- top pager -->';
        }

        $parts[] = '<!-- head -->';
        $parts[] = '<!-- body -->';

        if ($this->getConfig('showBottomPager')) {
            $parts[] = '<!-- bottom pager -->';
        }

        $partsHtml = implode("\n\n", $parts);

        $stringUtil = UtilContainer::getInstance()->getString();
        $partsHtml = $stringUtil->indentHtml($partsHtml, 2);

        $html = "
{$this->preContent}

<div class='$class' id='$rootId'>

$partsHtml

</div>

{$this->postContent}
";

        return $html;
    }


    /**
     * {@inheritdoc}
     */
    public function setPostContent($postContent)
    {
        $this->postContent = $postContent;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function setPreContent($preContent)
    {
        $this->preContent = $preContent;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function setRowRenderer(callable $renderer)
    {
        $this->rowRenderer = $renderer;

        return $this;
    }
}
