<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Navigation\Page;

use Pi;
use Zend\Navigation\Page as ZendPage;
use Zend\Navigation\Page\Mvc as ZendMvcPage;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\Navigation\Exception;
use Zend\Mvc\ModuleRouteListener;

/**
 * Mvc page
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Mvc extends ZendMvcPage
{
    /**
     * {@inheritDoc}
     * @var bool|null
     */
    protected $active = null;

    /**
     * Module name to use when assembling URL
     * @var string
     */
    protected $module;

    /**
     * Section name to use when assembling URL
     * @var string
     */
    protected $section;

    /**
     * Returns whether page should be considered active or not
     *
     * @param  bool $recursive  [optional] whether page should be considered
     *      active if any child pages are active. Default is false.
     * @return bool             whether page should be considered active
     * @see Zend\Navigation\Page\AbstractPage::isActive()
     */
    public function isAbstractActive($recursive = false)
    {
        //if (!$this->active && $recursive) {
        if (null === $this->active && $recursive) {
            foreach ($this->pages as $page) {
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

    /**
     * Adds a page to the container
     *
     * This method will inject the container as the given page's parent by
     * calling {@link Page\AbstractPage::setParent()}.
     *
     * @param AbstractPage|array|Traversable $page  page to add
     *
     * @return this
     * @throws Exception\InvalidArgumentException if page is invalid
     * @see Pi\Navigation\Navigation::addPage()
     * @see Pi\Navigation\Page\Uri::addPage()
     */
    public function addPage($page)
    {
        if ($page === $this) {
            throw new Exception\InvalidArgumentException(
                'A page cannot have itself as a parent'
            );
        }

        if (!$page instanceof ZendPage\AbstractPage) {
            if (!is_array($page) && !$page instanceof Traversable) {
                throw new Exception\InvalidArgumentException(
                    'Invalid argument: $page must be an instance of '
                    . 'Zend\Navigation\Page\AbstractPage or Traversable,'
                    . ' or an array'
                );
            }
            $page = AbstractPage::factory($page);
        }

        $hash = $page->hashCode();

        if (array_key_exists($hash, $this->index)) {
            // page is already in container
            return $this;
        }

        // adds page to container and sets dirty flag
        $this->pages[$hash] = $page;
        $this->index[$hash] = $page->getOrder();
        $this->dirtyIndex = true;

        // inject self as page parent
        $page->setParent($this);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isActive($recursive = false)
    {
        //if (!$this->active) {
        if (null === $this->active) {
            $reqParams = array();

            /**#@+
             * Added by Taiwen Jiang
             */
            $this->routeMatch = $this->getRouteMatch();
            /**#@-*/

            if ($this->routeMatch instanceof RouteMatch) {
                $reqParams  = $this->routeMatch->getParams();

                $originalController = ModuleRouteListener::ORIGINAL_CONTROLLER;
                if (isset($reqParams[$originalController])) {
                    $reqParams['controller'] = $reqParams[$originalController];
                }

                $myParams   = $this->params;
                /**#@+
                 * Added by Taiwen Jiang
                 */
                if (null !== $this->section) {
                    $myParams['section'] = $this->section;
                }
                if (null !== $this->module) {
                    $myParams['module'] = $this->module;
                }
                /**#@-*/
                if (null !== $this->controller) {
                    $myParams['controller'] = $this->controller;
                }
                if (null !== $this->action) {
                    $myParams['action'] = $this->action;
                }

                if (null !== $this->getRoute()) {
                    /**#@+
                     * Added by Taiwen Jiang
                     */
                    if (!empty($myParams['module'])
                        && $myParams['module'] === $reqParams['module']
                        && empty($myParams['controller'])
                        && empty($myParams['index'])
                    ) {
                        $section = isset($myParams['section']) ? $myParams['section'] : '';
                        if ($section == $this->routeMatch->getParam('section')) {
                            $this->active = true;
                            return $this->active;
                        }
                    }
                    if ($this->routeMatch->getMatchedRouteName()
                            === $this->getRoute()
                        && (count(array_intersect_assoc($reqParams, $myParams))
                            == count($myParams)
                        )
                    ) {
                        $this->active = true;
                        return $this->active;
                    } else {
                        return $this->isAbstractActive($recursive);
                    }
                    /**#@-*/

                    if ($this->routeMatch->getMatchedRouteName()
                            === $this->getRoute()
                        && (count(array_intersect_assoc($reqParams, $myParams))
                            == count($myParams)
                        )
                    ) {
                        $this->active = true;
                        return $this->active;
                    } else {
                        return parent::isActive($recursive);
                    }
                }
                /**#@+
                 * Added by Taiwen Jiang
                 */
                return $this->isAbstractActive($recursive);
                /**#@-*/
            }

            $myParams = $this->params;

            /**#@+
             * Added by Taiwen Jiang
             */
            if (null !== $this->module) {
                $myParams['module'] = $this->module;
            }
            /**#@-*/

            if (null !== $this->controller) {
                $myParams['controller'] = $this->controller;
            } else {
                /**
                 * @todo In ZF1, this was configurable and pulled
                 * from the front controller
                 */
                $myParams['controller'] = 'index';
            }

            if (null !== $this->action) {
                $myParams['action'] = $this->action;
            } else {
                /**
                 * @todo In ZF1, this was configurable and pulled
                 * from the front controller
                 */
                $myParams['action'] = 'index';
            }

            if (count(array_intersect_assoc($reqParams, $myParams))
                == count($myParams)
            ) {
                $this->active = true;
                return true;
            }

            /**#@+
             * Added by Taiwen Jiang
             */
            return $this->isAbstractActive($recursive);
            /*#@-*/
        }
        /**#@+
         * Modified by Taiwen Jiang
         */
        return $this->active;
        /**#@-*/
        return parent::isActive($recursive);
    }

    /**
     * {@inheritDoc}
     */
    public function getHref()
    {
        if ($this->hrefCache) {
            return $this->hrefCache;
        }

        $router = $this->router;
        if (null === $router) {
            $router = static::$defaultRouter;
        }

        if (!$router instanceof RouteStackInterface) {
            throw new Exception\DomainException(
                __METHOD__
                . ' cannot execute as no Zend\Mvc\Router\RouteStackInterface'
                . ' instance is composed'
            );
        }

        if ($this->useRouteMatch() && $this->getRouteMatch()) {
            $rmParams = $this->getRouteMatch()->getParams();

            if (isset($rmParams[ModuleRouteListener::ORIGINAL_CONTROLLER])) {
                $rmParams['controller'] =
                    $rmParams[ModuleRouteListener::ORIGINAL_CONTROLLER];
                unset($rmParams[ModuleRouteListener::ORIGINAL_CONTROLLER]);
            }

            if (isset($rmParams[ModuleRouteListener::MODULE_NAMESPACE])) {
                unset($rmParams[ModuleRouteListener::MODULE_NAMESPACE]);
            }

            $params = array_merge($rmParams, $this->getParams());
        } else {
            $params = $this->getParams();
        }


        /**#@+
         * Added by Taiwen Jiang
         */
        if (($param = $this->getSection()) != null) {
            $params['section'] = $param;
        }
        if (($param = $this->getModule()) != null) {
            $params['module'] = $param;
        }
        /**#@-*/

        if (($param = $this->getController()) != null) {
            $params['controller'] = $param;
        }

        if (($param = $this->getAction()) != null) {
            $params['action'] = $param;
        }

        switch (true) {
            case ($this->getRoute() !== null || static::getDefaultRoute() !== null):
                $name = ($this->getRoute() !== null) ? $this->getRoute() : static::getDefaultRoute();
                break;
            case ($this->getRouteMatch() !== null):
                $name = $this->getRouteMatch()->getMatchedRouteName();
                break;
            default:
                throw new Exception\DomainException(
                    'No route name could be found'
                );
        }

        $options = array('name' => $name);

        // Add the fragment identifier if it is set
        $fragment = $this->getFragment();
        if (null !== $fragment) {
            $options['fragment'] = $fragment;
        }

        if (null !== ($query = $this->getQuery())) {
            $options['query'] = $query;
        }


        /**#@+
         * Modified by Taiwen Jiang
         */
        try {
           $url = $router->assemble($params, $options);
        } catch (\Exception $e) {
            $url = '';
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
        /**#@-**/

        return $this->hrefCache = $url;
    }

    /**#@+
     * Added by Taiwen Jiang
     */
    /**
     * Sets section name to use when assembling URL
     *
     * @see getHref()
     *
     * @param  string|null $section    section name
     * @return $this
     */
    public function setSection($section)
    {
        $this->section = $section;
        $this->hrefCache  = null;

        return $this;
    }

    /**
     * Returns section name to use when assembling URL
     *
     * @see getHref()
     *
     * @return string|null  module name or null
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Sets module name to use when assembling URL
     *
     * @see getHref()
     *
     * @param  string|null $module    module name
     * @return Mvc   fluent interface, returns self
     * @throws Exception\InvalidArgumentException
     *      if invalid module name is given
     */
    public function setModule($module)
    {
        if (null !== $module && !is_string($module)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $module must be a string or null'
            );
        }

        $this->module = $module;
        $this->hrefCache  = null;

        return $this;
    }

    /**
     * Returns module name to use when assembling URL
     *
     * @see getHref()
     *
     * @return string|null  module name or null
     */
    public function getModule()
    {
        return $this->module;
    }
    /**#@-*/

    /**
     * {@inheritDoc}
     */
    public function getRouteMatch()
    {
        /**#@+
         * Modified by Taiwen Jiang
         */
        return $this->routeMatch ?: Pi::service('url')->getRouteMatch();
        /**#@-*/
        return $this->routeMatch;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouter()
    {
        /**#@+
         * Modified by Taiwen Jiang
         */
        return $this->router ?: Pi::service('url')->getRouter();
        /**#@-*/
        return $this->router;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            array(
                /**#@+
                 * Added by Taiwen Jiang
                 */
                'section'      => $this->getSection(),
                'module'       => $this->getModule(),
                /**#@-*/
            )
        );
    }
}
