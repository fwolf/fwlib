<?php
namespace Fwlib\Base;

use Fwlib\Util\UtilContainerAwareTrait;

/**
 * Return value object
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ReturnValue
{
    use UtilContainerAwareTrait;


    /**
     * Return code
     *
     * In common, 0 or positive code means success and code value is counter
     * of affected items, negative code means failed/error.
     *
     * @var int
     */
    protected $code = 0;

    /**
     * Return data if needed
     *
     * @var mixed
     */
    protected $data = null;

    /**
     * Return message
     *
     * @var string
     */
    protected $message = '';


    /**
     * Constructor
     *
     * $code can be int, or a json encoded string.
     *
     * @param   mixed   $code
     * @param   string  $message
     * @param   mixed   $data
     */
    public function __construct($code = 0, $message = '', $data = null)
    {
        if (is_int($code)) {
            $this->code    = $code;
            $this->message = $message;
            $this->data    = $data;

        } else {
            $this->loadJson($code);
        }
    }


    /**
     * Getter of code
     *
     * For check if error occurs, should use isError() instead.
     *
     * @return  int
     */
    public function getCode()
    {
        return $this->code;
    }


    /**
     * Getter of data
     *
     * @return  mixed
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * Getter of message
     *
     * @return  string
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * Get json encoded info string
     *
     * @return  string
     */
    public function getJson()
    {
        return $this->getUtilContainer()->getJson()->encodeUnicode([
            'code'    => $this->code,
            'message' => $this->message,
            'data'    => $this->data,
        ]);
    }


    /**
     * Is result means error ?
     *
     * @return  boolean
     */
    public function isError()
    {
        return 0 > $this->code;
    }


    /**
     * Load info from json encoded string
     *
     * Input json string MUST include code and message, data can be optional.
     *
     * @param   string  $json
     * @return  ReturnValue
     */
    public function loadJson($json)
    {
        $ar = $this->getUtilContainer()->getJson()->decode($json, true);

        foreach (['code', 'message'] as $v) {
            if (!isset($ar[$v])) {
                throw new \Exception("Json string to load have no $v info");
            }
        }

        $arrayUtil = $this->getUtilContainer()->getArray();
        $this->code    = $ar['code'];
        $this->message = $ar['message'];
        $this->data    = $arrayUtil->getIdx($ar, 'data', null);

        return $this;
    }


    /**
     * Setter of code
     *
     * @param   int     $code
     * @return  ReturnValue
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }


    /**
     * Setter of data
     *
     * @param   mixed   $data
     * @return  ReturnValue
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }


    /**
     * Setter of message
     *
     * @param   string  $message
     * @return  ReturnValue
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }
}
