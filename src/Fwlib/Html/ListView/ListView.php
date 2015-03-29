<?php
namespace Fwlib\Html\ListView;

use Fwlib\Config\ConfigAwareTrait;

/**
 * ListView
 *
 * Migrate from old ListTable.
 *
 *
 * Config class and id will be used in html, css and js.
 *
 * Class is classname of root element, and classname prefix of other child
 * elements. should not be empty.
 *
 * Id is identify of a list, the actual html id will prefix with class, should
 * not be empty.
 *
 * Example:
 *  <div class='listView' id='listView-1'>
 *    <div class='listView__pager' id='listView-1__pager'>
 *
 *
 * @copyright   Copyright 2003-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ListView
{
    use ConfigAwareTrait;


    /**
     * @var FitterInterface
     */
    protected $fitter;

    /**
     * @var ListDto
     */
    protected $listDto;


    /**
     * Fit data and title
     *
     * @return  static
     */
    protected function fitTitleAndData()
    {
        $this->getFitter()
            ->setEmptyFiller($this->getConfig('fitEmptyFiller'))
            ->setMode($this->getConfig('fitMode'))
            ->fit($this->getListDto());

        return $this;
    }


    /**
     * Get element class name
     *
     * @param   string  $name   Empty for root element
     * @return  string
     */
    protected function getClass($name = '')
    {
        $class = $this->getConfig('class');

        if (!empty($name)) {
            $class .= "__$name";
        }

        return $class;
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
             * If a value in data is empty, display with this value. Not for
             * title, which will use field name.
             * @see Fitter::$emptyFiller
             */
            'fitEmptyFiller'    => '&nbsp;',
        ];
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
     * Get element id
     *
     * @param   string  $name   Empty for root element
     * @return  string
     */
    protected function getId($name = '')
    {
        $identity = $this->getConfig('id');
        $rootId = $this->getConfig('class') .
            (empty($identity) ? '' : "-$identity");

        return empty($name) ? $rootId
            : $rootId . "__$name";
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
     * Setter of root class
     *
     * @param   string  $class
     * @return  static
     */
    public function setClass($class)
    {
        $this->setConfig('class', $class);

        return $this;
    }


    /**
     * Data can be empty, so no type hint in parameter list.
     *
     * @param   array $listData
     * @return  static
     */
    public function setData($listData)
    {
        $this->getListDto()->setData($listData);

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
     * Setter of $id
     *
     * @param   int|string  $identity
     * @return  static
     */
    public function setId($identity)
    {
        $this->setConfig('id', $identity);

        return $this;
    }


    /**
     * @param   array $listTitle
     * @return  static
     */
    public function setTitle(array $listTitle)
    {
        $this->getListDto()->setTitle($listTitle);

        return $this;
    }


    /**
     * @param   int     $totalRows
     * @return  static
     */
    public function setTotalRows($totalRows)
    {
        $this->getListDto()->setTotalRows($totalRows);

        return $this;
    }
}
