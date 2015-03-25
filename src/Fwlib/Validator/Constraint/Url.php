<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Base\ReturnValue;
use Fwlib\Base\ServiceContainerAwareTrait;
use Fwlib\Util\UtilContainerAwareTrait;
use Fwlib\Validator\AbstractConstraint;

/**
 * Constraint Url
 *
 * The validate url is called by HTTP POST, should return a json encoded
 * string, with structure same as Fwlib\Base\ReturnValue::info, code less than
 * 0 means validate fail, and data is array of fail message.
 *
 * Additional $constraintData is string, the format is:
 *
 * - url
 * - url, [inputName,]
 *
 * Validate value should be an array, mostly come from $_POST. For the 1st
 * format, the whole value is thrown to target url; For the 2nd format, it
 * will build a new array, only include index same as inputName in value
 * array, this helps reduce post data size, and raise security.
 *
 * If validate value is empty, the validate totally depends on url result,
 * this maybe useful in some special situation.
 *
 * @codeCoverageIgnore
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Url extends AbstractConstraint
{
    use ServiceContainerAwareTrait;
    use UtilContainerAwareTrait;


    /**
     * {@inheritdoc}
     */
    protected $messageTemplates = [
        'curlFail'    => 'The input validate url request fail',
        'default'     => 'The input must pass validate',
        'invalidType' => 'The input must be Array',
        'urlEmpty'    => 'The input need url target for validate',
    ];


    /**
     * Get full url for given relative url string
     *
     * @param   string  $url
     * @return  string
     */
    protected function getFullUrl($url)
    {
        if ('HTTP' == strtoupper(substr($url, 0, 4))) {
            return $url;
        }

        $httpUtil = $this->getUtilContainer()->getHttp();
        $selfUrl = $httpUtil->getSelfUrlWithoutQueryString();

        if ('.' == $url[0]) {
            // Remove last filename
            $selfUrl = substr($selfUrl, 0, strrpos($selfUrl, '/') + 1);
        } elseif ('/' == $url[0]) {
            // Get url host without any request url or get parameter
            $selfUrl = $httpUtil->getSelfHostUrl();
        }

        return $selfUrl . $url;
    }


    /**
     * {@inheritdoc}
     */
    public function validate($value, $constraintData = null)
    {
        parent::validate($value, $constraintData);

        if (!(is_array($value) || empty($value))) {
            $this->setMessage('invalidType');
            return false;
        }

        $parts = explode(',', $constraintData);
        $url = trim(array_shift($parts));

        if (empty($url)) {
            $this->setMessage('urlEmpty');
            return false;
        }

        // Url must start from 'HTTP', can't use relative '?a=b' style, which
        // works well in js ajax, is common used.
        $url = $this->getFullUrl($url);

        if (empty($parts)) {
            // All value will be posted
            $postData = $value;

        } else {
            // Build post data array
            $postData = [];
            $arrayUtil = $this->getUtilContainer()->getArray();
            foreach ($parts as $v) {
                $v = trim($v);

                if (empty($v)) {
                    continue;
                }

                $postData[$v] = $arrayUtil->getIdx($value, $v, null);
            }
        }

        try {
            $curl = $this->getServiceContainer()->getCurl();
            $curl->setoptSslVerify(false);

            $result = $curl->post($url, $postData);
            $returnValue = new ReturnValue($result);

            if ($returnValue->isError()) {
                // Use return data as fail message
                $data = $returnValue->getData();
                if (empty($data)) {
                    $this->setMessage('default');
                } else {
                    $this->messages = (array)$data;
                }

                return false;

            } else {
                return true;
            }

        } catch (\Exception $e) {
            $this->setMessage('curlFail');
            return false;
        }
    }
}
