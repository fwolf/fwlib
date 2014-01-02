<?php
namespace Fwlib\Validator\Constraint;

use Fwlib\Base\AbstractServiceContainer;
use Fwlib\Base\ReturnValue;
use Fwlib\Validator\AbstractConstraint;
use Fwlib\Util\UtilAwareInterface;
use Fwlib\Util\UtilContainer;

/**
 * Constraint Url
 *
 * The validate url is called by HTTP POST, should return a json encoded
 * string, with structure same as Fwlib\Base\ReturnValue::info, code less than
 * 0 means validate fail, and data is array of fail messsage.
 *
 * Additional $constraintData is string, the format is:
 *
 * - url
 * - url, [inputName,]
 *
 * Validate value should be an array, mostly come from $_POST. For the 1st
 * format, the whole value is throwed to target url; For the 2nd format, it
 * will build a new array, only include index same as inputName in value
 * array, this helps reduce post data size, and raise security.
 *
 * If validate value is empty, the validate totally depends on url result,
 * this maybe usefull in some special situation.
 *
 * @codeCoverageIgnore
 *
 * @package     Fwlib\Validator\Constraint
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-12-12
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

    protected function getUtil($name)
    {
        if (is_null($this->utilContainer)) {
            $this->setUtilContainer(null);
        }

        return $this->utilContainer->get($name);
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

    public function setUtilContainer(UtilContainer $utilContainer = null)
    {
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
    public $messageTemplate = array(
        'curlFail'  => 'The input validate url request fail',
        'default'   => 'The input must pass validate',
        'invalidType'   => 'The input must be Array',
        'urlEmpty'  => 'The input need url target for validate',
    );


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


        $httpUtil = $this->getUtil('HttpUtil');
        $selfUrl = $httpUtil->getSelfUrl(true);
        if (false !== strpos($selfUrl, '?')) {
            $url{0} = '&';
        } else {
            $url{0} = '?';
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
        if ('HTTP' != strtoupper(substr($url, 0, 4))) {
            $url = $this->getFullUrl($url);
        }

        if (empty($ar)) {
            // All value will be posted
            $postData = $value;

        } else {
            // Build post data array
            $postData = array();
            $arrayUtil = $this->getUtil('Array');
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
