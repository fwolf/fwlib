<?php
namespace Fwlib\Db;

/**
 * Simple key-value code dictionary
 *
 * Has only 2 columns, first is code/key, second is name/title/value etc.
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class KeyValueDictionary extends CodeDictionary
{
    /**
     * {@inheritdoc}
     */
    protected $columns = [self::COL_CODE, self::COL_TITLE];

    /**
     * {@inheritdoc}
     */
    protected $dictionary = [];

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = self::COL_CODE;

    /**
     * {@inheritdoc}
     */
    protected $table = '';


    /**
     * {@inheritdoc}
     */
    protected function fixDictionaryIndex()
    {
        $dictionary = $this->dictionary;
        $this->dictionary = [];

        foreach ($dictionary as $key => $value) {
            $keyCol = $this->columns[0];
            $valCol = $this->columns[1];

            $this->dictionary[$key] = [
                $keyCol => $key,
                $valCol => $value,
            ];
        }
    }


    /**
     * {@inheritdoc}
     */
    public function getSingleColumn($column = self::COL_TITLE)
    {
        return parent::getSingleColumn($column);
    }
}
