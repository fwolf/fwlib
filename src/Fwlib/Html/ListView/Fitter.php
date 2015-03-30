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
     * In body fit process, newly created key will use this as value. This
     * will not affect head, which will use key as filler.
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
     * Skip if their key are same, or either is empty.
     *
     * @throws  InvalidFitModeException
     */
    public function fit(ListDto $listDto)
    {
        $listBody = $listDto->getBody();
        $listHead = $listDto->getHead();

        if (empty($listBody) || empty($listHead)) {
            return $listDto;
        }

        $bodyKeys = array_keys(current($listBody));
        $headKeys = array_keys($listHead);

        if ($bodyKeys == $headKeys) {
            return $listDto;
        }

        switch ($this->mode) {
            case FitMode::TO_TITLE:
                $fittedKeys = $headKeys;
                break;

            case FitMode::TO_DATA:
                $fittedKeys = $bodyKeys;
                break;

            case FitMode::INTERSECTION:
                $fittedKeys = array_intersect($headKeys, $bodyKeys);
                break;

            case FitMode::UNION:
                $fittedKeys =
                    array_unique(array_merge($headKeys, $bodyKeys));
                break;

            default:
                throw new InvalidFitModeException;
        }

        $this->fitHead($listDto, $fittedKeys);
        $this->fitBody($listDto, $fittedKeys);

        return $listDto;
    }


    /**
     * Fit each row in body with given keys
     *
     * If row index is not in given keys, it will be dropped. If given keys is
     * not in row index, it will be created with filling value.
     *
     * @param   ListDto $listDto
     * @param   array   $keys
     */
    protected function fitBody(ListDto $listDto, array $keys)
    {
        $listBody = $listDto->getBody();

        // Use first row in body as sample, need not scan all rows
        $sampleRow = current($listBody);

        $keysToDel = array_diff(array_keys($sampleRow), $keys);

        $keysToAdd = array_diff($keys, array_keys($sampleRow));

        if (empty($keysToAdd) && empty($keysToDel)) {
            return;
        }

        $deleteDummy = array_fill_keys($keys, null);
        $addDummy = array_fill_keys($keysToAdd, $this->emptyFiller);
        foreach ($listBody as &$row) {
            $row = array_intersect_key($row, $deleteDummy);
            $row = array_merge($row, $addDummy);
        }
        unset($row);

        $listDto->setBody($listBody);
    }


    /**
     * Fit head with given keys
     *
     * Drop head key not in given keys, and create new if given keys is not
     * exists in head array.
     *
     * @param   ListDto $listDto
     * @param   array   $keys
     */
    protected function fitHead(ListDto $listDto, array $keys)
    {
        $listHead = $listDto->getHead();

        // Head index not in keys list
        $listHead = array_intersect_key(
            $listHead,
            array_fill_keys($keys, null)
        );

        // Add keys not exist in head
        foreach ($keys as $key) {
            if (!array_key_exists($key, $listHead)) {
                $listHead[$key] = ucfirst($key);
            }
        }

        $listDto->setHead($listHead);
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
