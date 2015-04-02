<?php
namespace Fwlib\Html\ListView;

use Fwlib\Config\ConfigAwareTrait;
use Fwlib\Html\ListView\Helper\ClassAndIdConfigTrait;
use Fwlib\Util\UtilContainer;
use Fwlib\Web\UrlGenerator;

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
    use RequestAwareTrait;
    use RowRendererAwareTrait;


    /**
     * @var string
     */
    protected $postContent = null;

    /**
     * @var string
     */
    protected $preContent = null;


    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfigs()
    {
        return [
            'pagerTextFirstPage'  => '首页',
            'pagerTextPrevPage'   => '上一页',
            'pagerTextNextPage'   => '下一页',
            'pagerTextLastPage'   => '尾页',
            'pagerTextSummary'    =>
                '共{totalRows}条信息，每页显示{pageSize}条，当前为第{page}/{maxPage}页',
            'pagerTextJump1'      => '转到第',
            'pagerTextJump2'      => '页',
            'pagerTextJumpButton' => '转',
            'pagerTextSpacer'     => " | ",
        ];
    }


    /**
     * {@inheritdoc}
     *
     * Result =
     *  preContent + topPager + head + body + bottomPager + postContent
     */
    public function getHtml()
    {
        $rootClass = $this->getClass();
        $rootId = $this->getId();

        $stringUtil = UtilContainer::getInstance()->getString();

        $parts = [];

        if ($this->getConfig('showTopPager')) {
            $parts[] = $this->getPager('top');
        }

        $parts[] = '<!-- head -->';
        $parts[] = '<!-- body -->';

        if ($this->getConfig('showBottomPager')) {
            $parts[] = $this->getPager('bottom');
        }

        $partsHtml = implode("\n\n", $parts);
        $partsHtml = $stringUtil->indentHtml($partsHtml, 2);

        $html = "{$this->preContent}

<div class='$rootClass' id='$rootId'>

$partsHtml

</div>

{$this->postContent}";

        return $html;
    }


    /**
     * Get pager html
     *
     * @param   string  $position   Top or bottom ?
     * @return  string
     */
    protected function getPager($position)
    {
        $request = $this->getRequest();
        $listDto = $this->getListDto();

        $pageSize = $this->getSafePageSize();
        $totalRows = $listDto->getTotalRows();
        $maxPage = ceil($totalRows / $pageSize);
        $page = $this->getSafePage($maxPage);

        $urlGenerator = new UrlGenerator();
        $urlGenerator->setFullUrl($request->getBaseUrl());
        $pageParameter = $request->getPageParameter();

        $pageUrls = [];
        if (1 < $page) {
            $firstUrl = $urlGenerator->setParameter($pageParameter, 1)
                ->getFullUrl();
            $pageUrls[$this->getConfig('pagerTextFirstPage')] = $firstUrl;

            $prevUrl = $urlGenerator->setParameter($pageParameter, $page - 1)
                ->getFullUrl();
            $pageUrls[$this->getConfig('pagerTextPrevPage')] = $prevUrl;
        }
        if ($page < $maxPage) {
            $nextUrl = $urlGenerator->setParameter($pageParameter, $page + 1)
                ->getFullUrl();
            $pageUrls[$this->getConfig('pagerTextNextPage')] = $nextUrl;

            $lastUrl = $urlGenerator->setParameter($pageParameter, $maxPage)
                ->getFullUrl();
            $pageUrls[$this->getConfig('pagerTextLastPage')] = $lastUrl;
        }

        $spacer = $this->getConfig('pagerTextSpacer');
        $linkHtml = '';
        foreach ($pageUrls as $title => $url) {
            $linkHtml .= "  <a href='" . $url . "'>$title</a>" . $spacer . "\n";
        }

        $summaryHtml = '  ' . str_replace(
            ['{totalRows}', '{pageSize}', '{page}', '{maxPage}'],
            [$totalRows, $pageSize, $page, $maxPage],
            $this->getConfig('pagerTextSummary')
        ) . $spacer;

        $formUrl = $urlGenerator->unsetParameter($pageParameter)
            ->getFullUrl();
        $formHtml =
            $this->getPagerJumpForm($formUrl, $page, $maxPage, $pageParameter);
        $stringUtil = UtilContainer::getInstance()->getString();
        $formHtml = $stringUtil->indent($formHtml, 2);

        $pagerClass = $this->getClass("pager");
        $pagerId = $this->getId("pager--{$position}");
        $html = "<div class='{$pagerClass}' id='{$pagerId}'>
{$linkHtml}{$summaryHtml}
{$formHtml}
</div>";

        return $html;
    }


    /**
     * Get html of jump form in pager
     *
     * Html5 can auto combine form field to action url with parameter, html
     * 4.01 can not, for compatible we still split params to field..
     * @see http://www.w3.org/TR/html401/interact/forms.html#h-17.13
     * @see http://www.w3.org/TR/html5/forms.html#form-submission-0
     *
     * As form method is get, query parameters are removed from action url, if
     * use post method, these query parameters should be kept.
     *
     * @param   string  $formUrl
     * @param   int     $page
     * @param   int     $maxPage
     * @param   string  $pageParameter
     * @return  string
     */
    protected function getPagerJumpForm(
        $formUrl,
        $page,
        $maxPage,
        $pageParameter
    ) {
        $params = [];
        parse_str(substr(strstr($formUrl, '?'), 1), $params);
        $paramHtml = '';
        foreach ($params as $key => $value) {
            $paramHtml .=
                "  <input type='hidden' name='$key' value='$value' />\n";
        }

        $formUrl = strstr($formUrl, '?', true);

        $pageInputWidth = (100 > $maxPage) ? 1
            : strlen($maxPage) - 1;

        $textJump1 = $this->getConfig('pagerTextJump1');
        $textJump2 = $this->getConfig('pagerTextJump2');
        $textJumpButton = $this->getConfig('pagerTextJumpButton');

        $html = "$textJump1
<form method='get' action='" . $formUrl . "'>
{$paramHtml}  <input type='text' name='{$pageParameter}' value='{$page}' size='{$pageInputWidth}' />
  $textJump2
  <input type='submit' value='{$textJumpButton}' />
</form>";

        return $html;
    }


    /**
     * Get safe page number
     *
     * Page from request is minimal 1, but may exceed max page.
     *
     * @param   int $maxPage
     * @return  int
     */
    protected function getSafePage($maxPage)
    {
        $page = $this->getRequest()->getPage();

        $page = min($page, $maxPage);

        return $page;
    }


    /**
     * Get safe page size
     *
     * Try request first, then config.
     */
    protected function getSafePageSize()
    {
        $pageSize = $this->getRequest()->getPageSize();

        if (ListView::PAGE_SIZE_NOT_SET == $pageSize) {
            $pageSize = $this->getConfig('pageSize');
        }

        return $pageSize;
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
}
