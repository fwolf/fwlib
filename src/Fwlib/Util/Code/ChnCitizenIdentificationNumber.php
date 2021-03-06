<?php
namespace Fwlib\Util\Code;

use Fwlib\Util\UtilContainerAwareTrait;

/**
 * China citizen identification number
 *
 * Abbr: Cin, CinCode
 * Synonym: Resident Identity number
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 * @see https://zh.wikisource.org/wiki/GB_11643-1999_公民身份号码
 */
class ChnCitizenIdentificationNumber
{
    use UtilContainerAwareTrait;


    /**
     * Title of genders
     *
     * @var array
     */
    protected $genderMap = ['男', '女'];

    /**
     * Generate pin code
     *
     * @return  string
     */
    public function generate()
    {
        $cin = '';


        // First 6 bit: 行政区划代码
        // @link http://www.stats.gov.cn/tjbz/index.htm

        // Province
        $province = array_merge(
            range(11, 15),
            range(21, 23),
            range(31, 37),
            range(41, 46),
            range(50, 54),
            range(61, 65),
            [71, 81, 82]
        );
        $k = array_rand($province);
        $cin .= $province[$k];

        // City
        $cin .= sprintf('%02s', mt_rand(0, 10));

        // Country
        $country = array_merge(
            range(0, 5),
            range(21, 31),
            range(81, 85)
        );
        $k = array_rand($country);
        $cin .= sprintf('%02s', $country[$k]);


        // 7-14, date
        $cin .= date('Ymd', mt_rand(strtotime('1970-1-1'), time()));


        // Sequence number
        $cin .= sprintf('%03s', mt_rand(1, 200));


        // Compute for correct pin
        $cin = $this->to18(substr($cin, 0, 6) . substr($cin, 8), 19);

        return $cin;
    }


    /**
     * Get birthday from code
     *
     * @param   string  $cin
     * @return  string  Format: Y-m-d
     */
    public function getBirthday($cin)
    {
        $cin = $this->to18($cin);

        $birthday = substr($cin, 6, 4) . '-' . substr($cin, 10, 2) .
            '-' . substr($cin, 12, 2);

        return $birthday;
    }


    /**
     * Get gender from code
     *
     * @param   string  $cin
     * @return  string
     */
    public function getGender($cin)
    {
        $cin = $this->to18($cin);
        $seq = intval(substr($cin, 14, 3));

        return (0 == $seq % 2)
            ? $this->genderMap[1]
            : $this->genderMap[0];
    }


    /**
     * Convert 18 bit pin to 15 bit long
     *
     * @param   string  $cin
     * @return  string
     */
    public function to15($cin)
    {
        if (18 != strlen($cin)) {
            return $cin;
        }

        return substr($cin, 0, 6) . substr($cin, 8, 9);
    }


    /**
     * Convert 15 bit pin to 18 bit long
     *
     * @param   string  $cin
     * @param   int     $prefix
     * @return  string
     */
    public function to18($cin, $prefix = 19)
    {
        if (15 != strlen($cin)) {
            // Error, which value should I return ?
            return $cin;
        }

        $s = substr($cin, 0, 6) . strval($prefix) . substr($cin, 6);

        $check = $this->getUtilContainer()->getIso7064()
            ->encode($s, '112', false);

        return $s . $check;
    }


    /**
     * Validate pin code, for 18 bit only
     *
     * @param   string  $cin
     * @return  boolean
     */
    public function validate($cin)
    {
        if (18 != strlen($cin)) {
            return false;
        }

        $prefix = substr($cin, 6, 2);
        return ($cin == $this->to18($this->to15($cin), $prefix));
    }
}
