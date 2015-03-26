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
 * string, with structure fit to load with {@see \Fwlib\Base\ReturnValue}, code
 * less than 0 means validate fail, and data is array of fail messages.
 *
 * Additional $constraintData is string, the format is:
 *
 * - url
 * - url, [inputNames,]
 *
 * Validate value should be an array, mostly associated like $_POST. For the
 * 1st $constraintData format, the whole value is thrown to target url as post
 * data; For the 2nd format, an array will be build, use only keys in
 * inputNames, this helps reduce post data size, and raise security.
 *
 * Validate value can be empty, validation result will still read from url,
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
        'curlFail'    => 'Validate url request fail',
        'default'     => 'Validate fail',
        'invalidType' => 'Must be Array or empty',
        'urlEmpty'    => 'Need url target for validate',
    ];


    /**
     * Get full url for given relative url string
     *
     * Parameter url can be relative, or absolute path start with '/'.
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
        $baseUrl = $httpUtil->getSelfUrlWithoutQueryString();

        if ('.' == $url[0]) {
            // Remove last filename, got path only url
            $baseUrl = substr($baseUrl, 0, strrpos($baseUrl, '/') + 1);

        } elseif ('/' == $url[0]) {
            // Absolute path, use host only
            $baseUrl = $httpUtil->getSelfHostUrl();
        }

        return $baseUrl . $url;
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
