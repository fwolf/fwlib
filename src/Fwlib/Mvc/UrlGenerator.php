<?php
namespace Fwlib\Mvc;

use Fwlib\Util\UtilContainer;

/**
 * UrlGenerator
 *
 * Should only work on http/https protocol.
 *
 * @copyright   Copyright 2014-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class UrlGenerator implements UrlGeneratorInterface
{
    /**
     * Get parameters
     *
     * Initial value is $_GET.
     *
     * @type    string[]
     */
    protected $parameters = array();

    /**
     * Url components generated by parse_url()
     *
     * Notice: when generate url, the query component is not used.
     *
     * Keys:
     *  - scheme
     *  - host
     *  - port
     *  - user
     *  - pass
     *  - path
     *  - query(parameters)
     *  - fragment(#)
     *
     * @type    string[]
     */
    protected $urlComponents = array();


    /**
     * Constructor
     */
    public function __construct()
    {
        $httpUtil = UtilContainer::getInstance()->getHttp();

        $this->urlComponents = parse_url($httpUtil->getSelfUrl(true));

        $this->parameters = $_GET;
    }


    /**
     * {@inheritdoc}
     */
    public function getFullLink($title, $rawHtml = '')
    {
        if (!empty($rawHtml)) {
            $rawHtml = ' ' . $rawHtml;
        }

        return "<a href='" . $this->getFullUrl() . "'" . $rawHtml . ">" .
            $title . "</a>";
    }


    /**
     * {@inheritdoc}
     *
     * http_build_url() maybe not usable.
     */
    public function getFullUrl()
    {
        $components = $this->urlComponents;
        $arrayUtil = UtilContainer::getInstance()->getArray();
        $url = '';

        $url .= array_key_exists('scheme', $components)
            ? $components['scheme'] . '://' : '//';

        $url .= array_key_exists('user', $components)
            ? $components['user'] .
                (array_key_exists('pass', $components)
                    ? ':' . $components['pass']
                    : '')
                . '@'
            : '';

        $url .= $arrayUtil->getIdx($components, 'host', '');

        $url .= $arrayUtil->getIdx($components, 'path', '/');

        if (!empty($this->parameters)) {
            $url .= '?' . http_build_query($this->parameters);
        }

        if (array_key_exists('fragment', $components)) {
            $url .= '#' . $components['fragment'];
        }

        return $url;
    }


    /**
     * {@inheritdoc}
     */
    public function getLink($title, $rawHtml = '')
    {
        if (!empty($rawHtml)) {
            $rawHtml = ' ' . $rawHtml;
        }

        return "<a href='" . $this->getUrl() . "'" . $rawHtml . ">" .
            $title . "</a>";
    }


    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        if (empty($this->parameters)) {
            return '';
        }

        return '?' . http_build_query($this->parameters);
    }


    /**
     * @param   string  $queryString
     * @return  string[]
     */
    protected function parseQueryParameters($queryString)
    {
        $parameters = array();

        parse_str($queryString, $parameters);

        return $parameters;
    }


    /**
     * Reset stored information
     *
     * @return  static
     */
    protected function reset()
    {
        $this->parameters = array();

        $this->urlComponents = array();

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function setBaseUrl($url)
    {
        $components = parse_url($url);

        unset($components['query']);
        unset($components['fragment']);

        $this->urlComponents = array_merge($this->urlComponents, $components);

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function setFullUrl($url)
    {
        $this->reset();

        $components = parse_url($url);

        if (array_key_exists('query', $components)) {
            $this->parameters = $this->parseQueryParameters(
                $components['query']
            );
        }

        $this->urlComponents = $components;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function unsetAllParameters()
    {
        $this->parameters = array();

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function unsetParameter($name)
    {
        unset($this->parameters[$name]);

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    public function unsetParameters(array $names)
    {
        $this->parameters = array_diff_key(
            $this->parameters,
            array_fill_keys($names, null)
        );

        return $this;
    }
}
