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
    use RowAdjusterAwareTrait;


    /**
     * @var string
     */
    protected $postContent = null;

    /**
     * @var string
     */
    protected $preContent = null;


    /**
     * Add order by text to list head title
     *
     * @param   string  $key
     * @param   string  $value
     */
    protected function addOrderByText($key, $value)
    {
        $orderBy = $this->getRequest()->getOrderBy();

        if (empty($orderBy) || !isset($orderBy[$key])) {
            return $value;
        }

        $orderByDirection = strtoupper($orderBy[$key]);
        $orderByText = ('ASC' == $orderByDirection)
            ? $this->getConfig('orderByTextAsc')
            : $this->getConfig('orderByTextDesc');

        return $value . $orderByText;
    }


    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfigs()
    {
        return [
            /**
             * Text display after head title, if order by assigned
             */
            'orderByTextAsc'    => '↑',
            'orderByTextDesc'   => '↓',

            'pagerTextFirstPage'  => '首页',
            'pagerTextPrevPage'   => '上一页',
            'pagerTextNextPage'   => '下一页',
            'pagerTextLastPage'   => '尾页',
            'pagerTextSummary'    =>
                '共{rowCount}条信息，每页显示{pageSize}条，当前为第{page}/{maxPage}页',
            'pagerTextJump1'      => '转到第',
            'pagerTextJump2'      => '页',
            'pagerTextJumpButton' => '转',
            'pagerTextSpacer'     => " | ",

            /**
             * Append raw string to td/th/tr tag in list table, eg:
             * nowrap='nowrap'. Notice the trAppend will only apply to list
             * body, not list head. The thAppend and tdAppend is assoc indexed
             * same with head/body, and trAppend is int indexed, match with
             * the row number in this page, start from 0.
             *
             * This for back compatible, if possible, use CSS instead.
             */
            'tdAppend' => [],
            'thAppend' => [],
            'trAppend' => [],
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

        $parts = [];

        if ($this->getConfig('showTopPager')) {
            $parts[] = $this->getPager('top');
        }

        $parts[] = $this->getListTable();

        if ($this->getConfig('showBottomPager')) {
            $parts[] = $this->getPager('bottom');
        }

        $partsHtml = '  ' . implode("\n\n  ", $parts);

        $html = "{$this->preContent}

<div class='$rootClass' id='$rootId'>

$partsHtml

</div>

{$this->postContent}";

        return $html;
    }


    /**
     * Get body part of list table
     *
     * @return  string
     */
    protected function getListBody()
    {
        $trAppendConfig = $this->getConfig('trAppend');
        $tdAppendConfig = $this->getConfig('tdAppend');

        $trClass = $this->getClass('body__tr');

        $stringUtil = UtilContainer::getInstance()->getString();

        $rowsHtml = '';
        foreach ($this->getListDto()->getBody() as $rowId => $row) {
            $trAppend = array_key_exists($rowId, $trAppendConfig)
                ? ' ' . ltrim($trAppendConfig[$rowId]) : '';

            $tdHtml = '';
            foreach ($row as $key => $value) {
                $tdClass = $this->getClass("td__{$key}");
                $tdId = $this->getId("td__{$key}--{$rowId}");

                $tdAppend = array_key_exists($key, $tdAppendConfig)
                    ? ' ' . ltrim($tdAppendConfig[$key]) : '';

                $tdHtml .= "<td class='$tdClass' id='$tdId'" . $tdAppend . ">
  $value
</td>
";
            }

            $tdHtml = rtrim($stringUtil->indent($tdHtml, 2));
            $rowsHtml .= "<tr class='$trClass'" . $trAppend . ">
$tdHtml
</tr>
";
        }

        $rowsHtml = rtrim($stringUtil->indent($rowsHtml, 2));
        $html = "<tbody>
$rowsHtml
</tbody>";

        return $html;
    }


    /**
     * Get head part of list table
     *
     * @return  string
     */
    protected function getListHead()
    {
        $thHtml = '';
        $thAppendConfig = $this->getConfig('thAppend');

        foreach ($this->getListDto()->getHead() as $key => $value) {
            $thId = $this->getId("th__$key");

            $thAppend = array_key_exists($key, $thAppendConfig)
                ? $thAppendConfig[$key] : '';

            if (!empty($thAppend)) {
                $thAppend = ' ' . ltrim($thAppend);
            }

            $value = $this->addOrderByText($key, $value);

            $thHtml .= "    <th id='$thId'" . $thAppend . ">$value</th>\n";
        }

        $trClass = $this->getClass('head__tr');

        $html = "<thead>
  <tr class='$trClass'>
$thHtml  </tr>
</thead>";

        return $html;
    }


    /**
     * Get list html except pager
     *
     * @return  string
     */
    protected function getListTable()
    {
        $tableClass = $this->getClass('table');
        $tableId = $this->getId('table');

        $head = $this->getListHead();
        $body = $this->getListBody();

        $stringUtil = UtilContainer::getInstance()->getString();
        $head = $stringUtil->indent($head, 2);
        $body = $stringUtil->indent($body, 2);

        $html = "<table class='$tableClass' id='$tableId'>
$head

$body
</table>";

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
        $rowCount = $listDto->getRowCount();
        $maxPage = ceil($rowCount / $pageSize);
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
            ['{rowCount}', '{pageSize}', '{page}', '{maxPage}'],
            [$rowCount, $pageSize, $page, $maxPage],
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
