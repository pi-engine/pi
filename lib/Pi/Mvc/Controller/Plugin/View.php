<?php
/**
 * Controller plugin view class as proxy to viewmodel and viewhelper
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\Mvc
 * @version         $Id$
 */

namespace Pi\Mvc\Controller\Plugin;

use Zend\View\Model\ViewModel as Model;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\InjectApplicationEventInterface;

/**
 * View plugin
 *
 * Assign variables to view model
 * <code>
 *  $this->view(array('key' => 'value'));
 *  $this->view()->assign('key', 'value');
 *  $this->view()->assign(array('key' => 'value'));
 * </code>
 *
 * Set page layout
 * <code>
 *  $this->view()->setLayout('layout-simple');
 * </code>
 *
 * Set page template
 * <code>
 *  $this->view()->setTemplate('page-template');
 *  // Disable template
 *  $this->view()->setTemplate(false);
 * </code>
 *
 * Set head title
 * <code>
 *  $this->view()->headTitle('Set custom title');
 * </code>
 *
 * Set head keywords, default as set by overwriting
 * <code>
 *  $this->view()->headKeywords('keyword, keyword, keyword'[, 'set']);
 *  $this->view()->headKeywords('keyword, keyword, keyword', 'append');
 *  $this->view()->headKeywords('keyword, keyword, keyword', 'prepend');
 *
 *  $this->view()->headKeywords(array('keyword', 'keyword', 'keyword')[, 'set']);
 *  $this->view()->headKeywords(array('keyword', 'keyword', 'keyword'), 'append');
 *  $this->view()->headKeywords(array('keyword', 'keyword', 'keyword'), 'prepend');
 * </code>
 *
 * Set head description, default as set by overwriting
 * <code>
 *  $this->view()->headKeywords('Custom description of the page.'[, 'set']);
 *  $this->view()->headKeywords('Custom description of the page.', 'append');
 *  $this->view()->headKeywords('Custom description of the page.', 'prepend');
 * </code>
 *
 * Load a view helper
 * <code>
 *  $helper = $this->view()->helper('helpername');
 * </code>
 *
 * Call view helper methods
 * <code>
 *  $this->view()->css('url-to-css-resouce');
 * </code>
 */
class View extends AbstractPlugin
{
    /**
     * NULL template for not specified actions
     *
     * @var string
     */
    const NULL_TEMPLATE = '__NULL__';

    /**
     * MvcEvent
     *
     * @var MvcEvent
     */
    protected $event;

    /**
     * @var Model
     */
    protected $viewModel;

    /**
     * Invoke as a functor
     *
     * If no arguments are given, return the view plugin
     * Otherwise, attempts to set variables for that view model.
     *
     * @param  null|array|Traversable $variables
     * @param  array|Traversable $options
     * @return View|Model
     */
    public function __invoke($variables = null, $options = null)
    {
        if (null !== $variables || null !== $options) {
            return $this->getViewModel($variables, $options);
        }
        return $this;
    }

    /**
     * Get the event
     *
     * @return MvcEvent
     * @throws \DomainException if unable to find event
     */
    protected function getEvent()
    {
        if ($this->event) {
            return $this->event;
        }

        $controller = $this->getController();
        if (!$controller instanceof InjectApplicationEventInterface) {
            throw new \DomainException('ViewModel plugin requires a controller that implements InjectApplicationEventInterface');
        }

        $event = $controller->getEvent();
        if (!$event instanceof MvcEvent) {
            $params = $event->getParams();
            $event  = new MvcEvent();
            $event->setParams($params);
        }
        $this->event = $event;

        return $this->event;
    }

    /**
     * Set View Model
     *
     * @param  Model $viewModel
     * @return View
     */
    public function setViewModel(Model $viewModel)
    {
        $this->viewModel = $viewModel;
        return $this;
    }

    /**
     * Create ViewModel
     *
     * @param  null|array|Traversable $variables
     * @param  array|Traversable $options
     * @return Model
     */
    public function getViewModel($variables = null, $options = array())
    {
        if (!$this->viewModel) {
            $this->viewModel = new Model($variables, $options);
            $this->viewModel->setCaptureTo('content');
        } elseif ($variables || $options) {
            if ($variables) {
                $this->assign($variables);
            }
            if ($options) {
                $this->viewModel->setOptions($options);
            }
        }
        return $this->viewModel;
    }

    /**
     * Set template for root model
     *
     * @param string $template
     * @return View
     */
    public function setLayout($template)
    {
        $this->getEvent()->getViewModel()->setTemplate($template);
        return $this;
    }

    /**
     * Set the view model template
     *
     * @see Pi\Mvc\View\InjectTemplateListener::injectTemplate()
     * @param  string $template
     * @param  string $system
     * @return ViewModel
     */
    public function setTemplate($template, $module = '')
    {
        // Set module prefix and section folder
        if ($template) {
            if (false === strpos($template, ':')) {
                $module = $module ?: $this->getController()->getModule();
                $template = $module . ':' . $this->getEvent()->getApplication()->getSection() . '/' . $template;
            }
        } else {
            $template = static::NULL_TEMPLATE;
        }
        $this->getViewModel()->setTemplate($template);
        return $this;
    }

    /**
     * Assign variables to view model
     *
     * @param string|array $variable    variable name or array of variables
     * @param mixed $value value to assign
     * @return View
     */
    public function assign($variable, $value = null)
    {
        if (is_array($variable)) {
            $this->getViewModel()->setVariables($variable);
        } elseif (is_string($variable) && null !== $value) {
            $this->getViewModel()->setVariable($variable, $value);
        }

        return $this;
    }

    /**
     * Check if view model is available
     * @return bool
     */
    public function hasViewModel()
    {
        return $this->viewModel ? true : false;
    }

    /**
     * Set head title
     *
     * @param string $title     Head title
     * @param string $setType   Position, default as append
     * @return View|AbstractPlugin
     */
    public function headTitle($title = null, $setType = null)
    {
        if (func_num_args() == 0) {
            return $this->helper('headTitle');
        }
        $title = strip_tags($title);
        $this->helper('headTitle')->__invoke($title, $setType);
        return $this;
    }

    /**
     * Set head description
     *
     * @param string $description   Head description
     * @param string $placement     Position, default as set
     * @return View
     */
    public function headDescription($description, $placement = null)
    {
        $description = strip_tags($description);
        $this->helper('headMeta')->__invoke($description, 'description', 'name', array(), $placement);
        return $this;
    }

    /**
     * Set head keywords
     *
     * @param string|array $keywords  Head keywords
     * @param string $placement Position, default as set
     * @return View
     */
    public function headKeywords($keywords, $placement = null)
    {
        if (is_array($keywords)) {
            $keywords = implode(', ', $keywords);
        }
        $keywords = strip_tags($keywords);
        $this->helper('headMeta')->__invoke($keywords, 'keywords', 'name', array(), $placement);
        return $this;
    }

    /**
     * Overloading: proxy to helpers
     *
     * Proxies to the attached plugin broker to retrieve, return, and potentially
     * execute helpers.
     *
     * * If the helper does not define __invoke, it will be returned
     * * If the helper does define __invoke, it will be called as a functor
     *
     * @param  string $method
     * @param  array $argv
     * @return mixed
     */
    public function __call($method, $argv)
    {
        $helper = $this->helper($method);
        if (is_callable($helper)) {
            return call_user_func_array($helper, $argv);
        }
        return $helper;
    }

    /**
     * Load view helper
     *
     * @param string $name
     * @return  AbstractPlugin
     */
    public function helper($name)
    {
        $render = $this->getController()->getServiceLocator()->get('ViewManager')->getRenderer();
        $helper = $render->plugin($name);
        return $helper;
    }
}
