<?php
namespace Fwlib\Net\Sms;

use Fwlib\Db\AdodbAwareTrait;
use Fwlib\Util\UtilContainerAwareTrait;

/**
 * SMS sent logger
 *
 * For schema of log table in db, see sms-log.sql.
 *
 * @copyright   Copyright 2010-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 * @see sms-log.sql
 */
class SmsLogger
{
    use AdodbAwareTrait;
    use UtilContainerAwareTrait;


    /**
     * Log table name
     *
     * @var string
     */
    public $table = 'sms_log';


    /**
     * Count dest number for each mobile company
     *
     * @param   array   $arDest
     * @return  array
     */
    public function countDestCompany($arDest)
    {
        $ar = [
            'cm'    => 0,
            'cu'    => 0,
            'ct'    => 0,
        ];

        $arCm = [
            134, 135, 136, 137, 138, 139, 147, 150, 151, 152, 157,
            158, 159, 182, 183, 184, 187, 188
        ];
        $arCu = [130, 131, 132, 145, 155, 156, 185, 186];
        $arCt = [133, 153, 180, 181, 189];

        foreach ($arDest as $dest) {
            $i = intval(substr($dest, 0, 3));

            if (in_array($i, $arCm)) {
                $ar['cm'] ++;

            } elseif (in_array($i, $arCu)) {
                $ar['cu'] ++;

            } elseif (in_array($i, $arCt)) {
                $ar['ct'] ++;
            }
        }

        return $ar;
    }


    /**
     * Count SMS will split to how many parts to send
     *
     * If only ascii chars include, 140 chars for 1 sms part, if has chinese
     * chars, 70 chars for 1 sms part only.
     *
     * 1 chinese char will count as 1 char.
     *
     * @param   string  $sms
     * @return  integer
     */
    public function countPart($sms = '')
    {
        if (empty($sms)) {
            return 0;
        }


        // Is there chinese in sms ?
        if (mb_strlen($sms, 'utf-8') == strlen($sms)) {
            return intval(ceil(strlen($sms) / 140));
        } else {
            return intval(ceil(mb_strlen($sms, 'utf-8') / 70));
        }
    }


    /**
     * Log sent sms with stat information
     *
     * @param   array   $arDest
     * @param   string  $sms
     * @param   integer $cat
     */
    public function log($arDest, $sms, $cat)
    {
        // Prepare data array
        $logData = [];
        $countDestCompany = $this->countDestCompany($arDest);

        $logData['uuid']        = $this->generateUuid();
        $logData['st']          = date('Y-m-d H:i:s');
        $logData['cat']         = $cat;
        $logData['cnt_dest']    = count($arDest);
        $logData['cnt_dest_cm'] = $countDestCompany['cm'];
        $logData['cnt_dest_cu'] = $countDestCompany['cu'];
        $logData['cnt_dest_ct'] = $countDestCompany['ct'];
        $logData['dest']        = implode(',', $arDest);
        $logData['cnt_part']    = $this->countPart($sms);
        $logData['sms']         = $sms;

        // Save to db
        $this->db->write($this->table, $logData, 'I');
    }


    /**
     * Generate an UUID
     *
     * @return  string
     */
    protected function generateUuid()
    {
        return $this->getUtilContainer()->getUuidBase36()->generate();
    }
}
