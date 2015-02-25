<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Base\AbstractServiceContainer;
use Fwlib\Base\ReturnValue;
use Fwlib\Validator\AbstractConstraint;
use Fwlib\Util\UtilAwareInterface;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\UtilContainerInterface;

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
class Url extends AbstractConstraint implements UtilAwareInterface
{
    /***************
     * Copy from Fwlib\Base\AbstractAutoNewInstance, can change to trait after
     * PHP 5.4.
     ***************/

    public $serviceContainer = null;
    protected $utilContainer = null;

    // Removed newObjXxx(), useless here
    public function __get($name)
    {
        $method = 'newInstance' . ucfirst($name);

        // @codeCoverageIgnoreStart
        if (method_exists($this, $method)) {
            $this->$name = $this->$method();
            return $this->$name;

        } else {
            $trace = debug_backtrace();
            trigger_error(
                'Undefined property via __get(): ' . $name .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
                E_USER_NOTICE
            );

            // trigger_error will terminate program run, below will not exec
            return null;

        }
        // @codeCoverageIgnoreEnd
    }

    public function checkServiceContainer($throwExceptionWhenFail = true)
    {
        if (is_null($this->serviceContainer)) {
            if ($throwExceptionWhenFail) {
                throw new \Exception('Need valid ServiceContainer.');
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    protected function getService($name)
    {
        $this->checkServiceContainer(true);

        return $this->serviceContainer->get($name);
    }


    /**
     * {@inheritdoc}
     */
    public function getUtilContainer()
    {
        if (is_null($this->utilContainer)) {
            $this->utilContainer = UtilContainer::getInstance();
        }

        return $this->utilContainer;
    }


    public function setInstance($instance, $className = null)
    {
        if (empty($className)) {
            $className = get_class($instance);
            $className = implode('', array_slice(explode('\\', $className), -1));
        }

        $className = lcfirst($className);

        $this->$className = $instance;

        return $this;
    }

    public function setServiceContainer(
        AbstractServiceContainer $serviceContainer = null
    ) {
        $this->serviceContainer = $serviceContainer;
    }

    public function setUtilContainer(
        UtilContainerInterface $utilContainer = null
    ) {
        if (is_null($utilContainer)) {
            $this->utilContainer = UtilContainer::getInstance();
        } else {
            $this->utilContainer = $utilContainer;
        }

        return $this;
    }

    /***************
     * End of copied part
     ***************/


    /**
     * {@inheritdoc}
     */
    public $messageTemplate = [
        'curlFail'  => 'The input validate url request fail',
        'default'   => 'The input must pass validate',
        'invalidType'   => 'The input must be Array',
        'urlEmpty'  => 'The input need url target for validate',
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

        $httpUtil = UtilContainer::getInstance()->getHttp();
        $selfUrl = $httpUtil->getSelfUrlWithoutParameter();

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

        $ar = explode(',', $constraintData);
        $url = trim(array_shift($ar));

        if (empty($url)) {
            $this->setMessage('urlEmpty');
            return false;
        }

        // Url must start from 'HTTP', can't use relative '?a=b' style, which
        // works well in js ajax, is common used.
        $url = $this->getFullUrl($url);

        if (empty($ar)) {
            // All value will be posted
            $postData = $value;

        } else {
            // Build post data array
            $postData = [];
            $arrayUtil = $this->getUtilContainer()->getArray();
            foreach ($ar as $v) {
                $v = trim($v);

                if (empty($v)) {
                    continue;
                }

                $postData[$v] = $arrayUtil->getIdx($value, $v, null);
            }
        }

        try {
            $curl = $this->getService('Curl');
            $curl->setoptSslVerify(false);

            $rs = $curl->post($url, $postData);
            $rv = new ReturnValue($rs);

            if ($rv->isError()) {
                // Use return data as fail message
                $data = $rv->getData();
                if (empty($data)) {
                    $this->setMessage('default');
                } else {
                    $this->message = (array)$data;
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
