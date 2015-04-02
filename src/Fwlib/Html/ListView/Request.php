<?php
namespace Fwlib\Html\ListView;

use Fwlib\Html\ListView\Exception\InvalidRequestSourceException;
use Fwlib\Html\ListView\Helper\RequestParameterTrait;
use Fwlib\Util\UtilContainer;

/**
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Request implements RequestInterface
{
    use RequestParameterTrait;


    /**
     * @var string
     */
    protected $baseUrl = null;

    /**
     * @var string
     */
    protected $orderByDirParameter = 'od';

    /**
     * @var string
     */
    protected $orderByParameter = 'ob';

    /**
     * @var string
     */
    protected $pageParameter = 'p';

    /**
     * @var int
     */
    protected $pageSizeParameter = 'ps';

    /**
     * @see RequestSource
     * @var string
     */
    protected $requestSource = RequestSource::GET;


    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        if (is_null($this->baseUrl)) {
            $httpUtil = UtilContainer::getInstance()->getHttp();

            $this->baseUrl = $httpUtil->getSelfUrl(true);
        }

        return $this->baseUrl;
    }


    /**
     * {@inheritdoc}
     */
    public function getOrderBy()
    {
        $orderBy = $this->getRequest($this->getOrderByParameter());

        if (empty($orderBy)) {
            return null;
        }

        $direction =
            $this->getRequest($this->getOrderByDirectionParameter(), 'ASC');

        $direction = strtoupper($direction);

        return [$orderBy => $direction];
    }


    /**
     * {@inheritdoc}
     */
    public function getPage()
    {
        $page = intval($this->getRequest($this->getPageParameter(), 1));

        if (1 > $page) {
            $page = 1;
        }

        return $page;
    }


    /**
     * {@inheritdoc}
     */
    public function getPageSize()
    {
        return intval(
            $this->getRequest(
                $this->getPageSizeParameter(),
                ListView::PAGE_SIZE_NOT_SET
            )
        );
    }


    /**
     * Get value from request by key
     *
     * Request maybe POST, GET or others.
     *
     * @see RequestSource
     *
     * @param   string      $key
     * @param   string|int  $default
     * @return  string
     * @throws  InvalidRequestSourceException
     */
    protected function getRequest($key, $default = null)
    {
        $httpUtil = UtilContainer::getInstance()->getHttp();

        switch ($this->getRequestSource()) {
            case RequestSource::GET:
                return $httpUtil->getGet($key, $default);
                break;

            case RequestSource::POST:
                return $httpUtil->getPost($key, $default);
                break;

            default:
                throw new InvalidRequestSourceException;
        }
    }


    /**
     * {@inheritdoc}
     */
    public function getRequestSource()
    {
        return $this->requestSource;
    }


    /**
     * {@inheritdoc}
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function setRequestSource($requestSource)
    {
        $this->requestSource = $requestSource;

        return $this;
    }
}
