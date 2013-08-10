<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\Controller\Plugin;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\InjectApplicationEventInterface;

/**
 * View plugin for controller
 *
 * Assign variables to view model
 *
 * ```
 *  $this->view(array('key' => 'value'));
 *  $this->view()->assign('key', 'value');
 *  $this->view()->assign(array('key' => 'value'));
 * ```
 *
 * Set page layout
 *
 * ```
 *  $this->view()->setLayout('layout-simple');
 * ```
 *
 * Set page template
 *
 * ```
 *  $this->view()->setTemplate('page-template');
 *
 *  // Disable template
 *  $this->view()->setTemplate(false);
 * ```
 *
 * Set head title
 *
 * ```
 *  $this->view()->headTitle('Set custom title');
 * ```
 *
 * Set head keywords, default as set by overwriting
 *
 * ```
 *  $this->view()->headKeywords('keyword, keyword, keyword'[, 'set']);
 *  $this->view()->headKeywords('keyword, keyword, keyword', 'append');
 *  $this->view()->headKeywords('keyword, keyword, keyword', 'prepend');
 *
 *  $this->view()->headKeywords(array('keyword', 'keyword', 'keyword'), 'set');
 *  $this->view()->headKeywords(array('keyword', 'keyword', 'keyword'),
 *      'append');
 *  $this->view()->headKeywords(array('keyword', 'keyword', 'keyword'),
 *      'prepend');
 * ```
 *
 * Set head description, default as set by overwriting
 *
 * ```
 *  $this->view()->headKeywords('Custom description of the page.'[, 'set']);
 *  $this->view()->headKeywords('Custom description of the page.', 'append');
 *  $this->view()->headKeywords('Custom description of the page.', 'prepend');
 * ```
 *
 * Load a view helper
 *
 * ```
 *  $helper = $this->view()->helper(<helper-name>);
 * ```
 *
 * Call view helper methods
 *
 * ```
 *  $this->view()->css(<css-url>);
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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

    /** @var ViewModel View model */
    protected $viewModel;

    /**
     * Invoke as a functor
     *
     * If no arguments are given, return the view plugin
     * Otherwise, attempts to set variables for that view model.
     *
     * @param  null|array|Traversable   $variables
     * @param  array|Traversable        $options
     * @return ViewModel|$this
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
            throw new \DomainException(
                'ViewModel plugin requires a controller that implements'
                . ' InjectApplicationEventInterface'
            );
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
     * @param  ViewModel $viewModel
     * @return $this
     */
    public function setViewModel(ViewModel $viewModel)
    {
        $this->viewModel = $viewModel;

        return $this;
    }

    /**
     * Create ViewModel
     *
     * @param  null|array|Traversable   $variables
     * @param  array|Traversable        $options
     * @return ViewModel
     */
    public function getViewModel($variables = null, $options = array())
    {
        if (!$this->viewModel) {
            $this->viewModel = new ViewModel($variables, $options);
            $this->viewModel->setCaptureTo('content');
        } elseif ($variables || $options) {
            if ($variables) {
                $this->viewModel->setVariables($variables);
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
     * @return $this
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
     * @param  string $module
     * @return $this
     */
    public function setTemplate($template, $module = '')
    {
        // Set module prefix and section folder
        if ($template) {
            if (false === strpos($template, ':')) {
                $module = $module ?: $this->getController()->getModule();
                $template = $module . ':'
                          . $this->getEvent()->getApplication()->getSection()
                          . '/' . $template;
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
     * @param string|array  $variable   Variable name or array of variables
     * @param mixed         $value      Value to assign
     * @return $this
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
     *
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
     * @return $this|AbstractPlugin
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
     * @param string        $description   Head description
     * @param string|null   $placement     Position, default as set
     * @return $this
     */
    public function headDescription($description, $placement = null)
    {
        $description = strip_tags($description);
        $this->helper('headMeta')->__invoke(
            $description,
            'description',
            'name',
            array(),
            $placement
        );

        return $this;
    }

    /**
     * Set head keywords
     *
     * @param string|array  $keywords  Head keywords
     * @param string|null   $placement Position, default as set
     * @return $this
     */
    public function headKeywords($keywords, $placement = null)
    {
        if (is_array($keywords)) {
            $keywords = implode(', ', $keywords);
        }
        $keywords = strip_tags($keywords);
        $this->helper('headMeta')->__invoke(
            $keywords,
            'keywords',
            'name',
            array(),
            $placement
        );

        return $this;
    }

    /**
     * Overloading: proxy to helpers
     *
     * Proxies to the attached plugin broker to retrieve, return,
     * and potentially execute helpers.
     *
     * - If the helper does not define __invoke, it will be returned
     * - If the helper does define __invoke, it will be called as a functor
     *
     * @param string    $method
     * @param array     $argv
     * @return mixed|AbstractPlugin
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
     * @return AbstractPlugin
     */
    public function helper($name)
    {
        $render = $this->getController()->getServiceLocator()
            ->get('ViewManager')->getRenderer();
        $helper = $render->plugin($name);

        return $helper;
    }
}
