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
        $render = $this->getController()->getServiceLocator()->get('ViewManager')->getRenderer();
        $helper = $render->plugin($method);
        if (is_callable($helper)) {
            return call_user_func_array($helper, $argv);
        }
        return $helper;
    }
}
