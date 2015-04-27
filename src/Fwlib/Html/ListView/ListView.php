<?php
namespace Fwlib\Html\ListView;

use Fwlib\Config\ConfigAwareTrait;
use Fwlib\Html\ListView\Helper\ClassAndIdConfigTrait;

/**
 * ListView
 *
 * Migrate from old ListTable class.
 *
 *
 * This class is also main injection/config entrance.
 *
 * If user need customize fitter, request or even renderer, they can make
 * different implement and inject in here.
 *
 * Some class used here also have their own configs, which can be change here
 * too. Although these configs are not include in default configs of this class,
 * they will be set to used class and take action there.
 *
 * @copyright   Copyright 2003-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ListView
{
    use ClassAndIdConfigTrait;
    use ConfigAwareTrait;
    use RendererAwareTrait;
    use RequestAwareTrait;
    use RetrieverAwareTrait;
    use RowDecoratorAwareTrait;


    /**
     * Return value for pageSize not set in request
     */
    const PAGE_SIZE_NOT_SET = -1;

    /**
     * Value for total rows not set
     */
    const ROW_COUNT_NOT_SET = -1;


    /**
     * @var FitterInterface
     */
    protected $fitter;

    /**
     * @var ListDto
     */
    protected $listDto;


    /**
     * Decorate list rows
     *
     * @param   ListDto $listDto
     * @return  static
     */
    protected function decorateRows(ListDto $listDto)
    {
        $rowCount = $listDto->getRowCount();

        if (!(self::ROW_COUNT_NOT_SET == $rowCount || 0 == $rowCount)) {
            $rowDecorator = $this->getRowDecorator();

            if (!is_null($rowDecorator)) {
                $rows = $listDto->getBody();

                $newRows = [];
                foreach ($rows as $row) {
                    $newRows[] = $rowDecorator($row);
                }

                $listDto->setBody($newRows);
            }
        }

        return $this;
    }


    /**
     * Fit list head and body
     *
     * @param   ListDto $listDto
     * @return  static
     */
    protected function fitHeadAndBody(ListDto $listDto)
    {
        $this->getFitter()
            ->setEmptyFiller($this->getConfig('fitEmptyFiller'))
            ->setMode($this->getConfig('fitMode'))
            ->fit($listDto);

        return $this;
    }


    /**
     * @return array
     */
    protected function getDefaultConfigs()
    {
        return [
            'class'             => 'listView',
            'id'                => 1,

            /**
             * @see FitMode
             */
            'fitMode'           => FitMode::TO_TITLE,
            /**
             * If a value in body is empty, display with this value. Not for
             * head, which will use field name.
             * @see Fitter::$emptyFiller
             */
            'fitEmptyFiller'    => '&nbsp;',

            'showTopPager'      => false,
            'showBottomPager'   => true,

            // Default/failsafe, MUST set a positive value
            'pageSize'          => 10,
        ];
    }


    /**
     * Try fill data and return ListDto
     *
     * @return  ListDto
     */
    protected function getFilledListDto()
    {
        $listDto = $this->getListDto();
        $configs = $this->getConfigs();

        if (self::ROW_COUNT_NOT_SET == $listDto->getRowCount()) {
            $retriever = $this->getRetriever();
            if (!is_null($retriever)) {
                $retriever->setConfigs($configs);

                $listDto->setBody($retriever->getListBody());
                $listDto->setRowCount($retriever->getRowCount());
            }
        }

        return $listDto;
    }


    /**
     * @return  FitterInterface
     */
    protected function getFitter()
    {
        if (is_null($this->fitter)) {
            $this->fitter = new Fitter();
        }

        return $this->fitter;
    }


    /**
     * Get html output
     *
     * @return  string
     */
    public function getHtml()
    {
        $listDto = $this->getFilledListDto();

        $this->fitHeadAndBody($listDto);

        $this->decorateRows($listDto);

        return $this->render($listDto);
    }


    /**
     * @return  ListDto
     */
    protected function getListDto()
    {
        if (is_null($this->listDto)) {
            $this->listDto = new ListDto();
        }

        return $this->listDto;
    }


    /**
     * {@inheritdoc}
     *
     * Default return a {@see Renderer} instance.
     */
    protected function getRenderer()
    {
        if (is_null($this->renderer)) {
            $this->renderer = new Renderer;
        }

        return $this->renderer;
    }


    /**
     * {@inheritdoc}
     *
     * Default return a {@see Request} instance.
     */
    protected function getRequest()
    {
        if (is_null($this->request)) {
            $this->request = new Request;
        }

        return $this->request;
    }


    /**
     * Render list data to html
     *
     * @param   ListDto $listDto
     * @return  string
     */
    protected function render(ListDto $listDto)
    {
        $configs = $this->getConfigs();

        $renderer = $this->getRenderer()
            ->setRequest($this->getRequest())
            ->setConfigs($configs)
            ->setListDto($listDto);

        return $renderer->getHtml();
    }


    /**
     * List body can be empty, so no type hint in parameter list.
     *
     * In some case, list is not paged, so total rows can be set automatic by
     * counting rows of list body.
     *
     * @param   array $listBody
     * @param   bool  $updateRowCount
     * @return  static
     */
    public function setBody($listBody, $updateRowCount = false)
    {
        $listDto = $this->getListDto();

        $listDto->setBody($listBody);

        if ($updateRowCount) {
            $listDto->setRowCount(count($listBody));
        }

        return $this;
    }


    /**
     * Setter of $fitter
     *
     * @param   FitterInterface $fitter
     * @return  static
     */
    public function setFitter(FitterInterface $fitter)
    {
        $this->fitter = $fitter;

        return $this;
    }


    /**
     * @param   array $listHead
     * @return  static
     */
    public function setHead(array $listHead)
    {
        $this->getListDto()->setHead($listHead);

        return $this;
    }


    /**
     * @param   int     $rowCount
     * @return  static
     */
    public function setRowCount($rowCount)
    {
        $this->getListDto()->setRowCount($rowCount);

        return $this;
    }
}
