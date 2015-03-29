<?php
namespace Fwlib\Html\ListView;

use Fwlib\Html\ListView\Exception\InvalidFitModeException;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Fitter implements FitterInterface
{
    /**
     * Empty filler
     *
     * In data fit process, newly created key will use this as value. This will
     * not affect title, which will use key as filler.
     *
     * Default value also set in ListView configs with key 'fitEmptyFiller'.
     *
     * @see ListView::getDefaultConfigs()
     *
     * @var string
     */
    protected $emptyFiller = '&nbsp;';

    /**
     * Fit mode
     *
     * Default value also set in ListView configs with key 'fitMode'.
     *
     * @see ListView::getDefaultConfigs()
     * @see FitMode
     *
     * @var int
     */
    protected $mode = FitMode::TO_TITLE;


    /**
     * {@inheritdoc}
     *
     * @throws  InvalidFitModeException
     */
    public function fit(ListDto $listDto)
    {
        $listData = $listDto->getData();
        $listTitle = $listDto->getTitle();

        $dataKeys = array_keys(current($listData));
        $titleKeys = array_keys($listTitle);

        switch ($this->mode) {
            case FitMode::TO_TITLE:
                $fittedKeys = $titleKeys;
                break;

            case FitMode::TO_DATA:
                $fittedKeys = $dataKeys;
                break;

            case FitMode::INTERSECTION:
                $fittedKeys = array_intersect($titleKeys, $dataKeys);
                break;

            case FitMode::UNION:
                $fittedKeys =
                    array_unique(array_merge($titleKeys, $dataKeys));
                break;

            default:
                throw new InvalidFitModeException;
        }

        $this->fitTitle($listDto, $fittedKeys);
        $this->fitData($listDto, $fittedKeys);

        return $listDto;
    }


    /**
     * Fit each row in data with given keys
     *
     * If row index is not in given keys, it will be dropped. If given keys is
     * not in row index, it will be created with filling value.
     *
     * @param   ListDto $listDto
     * @param   array   $keys
     */
    protected function fitData(ListDto $listDto, array $keys)
    {
        $listData = $listDto->getData();

        // Use first row in data as sample, need not scan all rows
        $sampleRow = current($listData);

        $keysToDel = array_diff(array_keys($sampleRow), $keys);

        $keysToAdd = array_diff($keys, array_keys($sampleRow));

        if (empty($keysToAdd) && empty($keysToDel)) {
            return;
        }

        $deleteDummy = array_fill_keys($keys, null);
        $addDummy = array_fill_keys($keysToAdd, $this->emptyFiller);
        foreach ($listData as &$row) {
            $row = array_intersect_key($row, $deleteDummy);
            $row = array_merge($row, $addDummy);
        }
        unset($row);

        $listDto->setData($listData);
    }


    /**
     * Fit title with given keys
     *
     * Drop title value not in given keys, and create new if given keys is not
     * exists in title array.
     *
     * @param   ListDto $listDto
     * @param   array   $keys
     */
    protected function fitTitle(ListDto $listDto, array $keys)
    {
        $listTitle = $listDto->getTitle();

        // Title index not in key list
        foreach ($listTitle as $k => $v) {
            if (!in_array($k, $keys)) {
                unset($listTitle[$k]);
            }
        }

        // Key not exist in title
        foreach ($keys as $k) {
            if (!isset($listTitle[$k])) {
                // Title value is same as key
                $listTitle[$k] = $k;
            }
        }

        $listDto->setTitle($listTitle);
    }


    /**
     * {@inheritdoc}
     */
    public function setEmptyFiller($emptyFiller)
    {
        $this->emptyFiller = $emptyFiller;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }
}
