<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Navigation
 */

namespace Zend\Navigation\Page;

use Zend\Navigation\Exception;

/**
 * Represents a page that is defined by specifying a URI
 *
 * @category   Zend
 * @package    Zend_Navigation
 * @subpackage Page
 */
class Uri extends AbstractPage
{
    /**
     * Page URI
     *
     * @var string|null
     */
    protected $uri = null;

    /**
     * Sets page URI
     *
     * @param  string $uri                page URI, must a string or null
     *
     * @return Uri   fluent interface, returns self
     * @throws Exception\InvalidArgumentException  if $uri is invalid
     */
    public function setUri($uri)
    {
        if (null !== $uri && !is_string($uri)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $uri must be a string or null'
            );
        }

        $this->uri = $uri;
        return $this;
    }

    /**
     * Returns URI
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Returns href for this page
     *
     * Includes the fragment identifier if it is set.
     *
     * @return string
     */
    public function getHref()
    {
        $uri = $this->getUri();

        $fragment = $this->getFragment();
        if (null !== $fragment) {
            if ('#' == substr($uri, -1)) {
                return $uri . $fragment;
            } else {
                return $uri . '#' . $fragment;
            }
        }

        return $uri;
    }

    /**
     * Returns an array representation of the page
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            array(
                'uri' => $this->getUri(),
            )
        );
    }

    /**#@+
     * Added by Taiwen Jiang
     */
    /**
     * Returns whether page should be considered active or not
     *
     * @param  bool $recursive  [optional] whether page should be considered
     *                          active if any child pages are active. Default is
     *                          false.
     * @return bool             whether page should be considered active or not
     */
    public function ____isActive($recursive = false)
    {
        if (null === $this->active) {
            $request = Pi::engine()->application()->getRequest();
            $reqPath = $request->getPathInfo();
            $uriPath = parse_url($this->getUri(), PHP_URL_PATH);
            if (substr($uriPath, -1 * strlen($reqPath)) == $reqPath) {
            //if (!strcmp($reqPath, $uriPath)) {
                $this->active = true;
                if ($uriQuery = parse_url($this->getUri(), PHP_URL_QUERY)) {
                    $reqParams = $request->getParams();
                    parse_str($uriQuery, $uriParams);
                    foreach ($uriParams as $key => $val) {
                        if (!isset($reqParams[$key]) || $reqParams[$key] !== $val) {
                            $this->active = false;
                            break;
                        }
                    }
                }
                if ($this->active == true) {
                    return true;
                }
            }
        }


        if (null === $this->active && $recursive) {
            foreach ($this->active as $page) {
                if ($page->isActive(true)) {
                    $this->active = true;
                    return true;
                }
            }
            $this->active = false;
            return false;
        }

        return $this->active;
    }
    /**#@-*/
}