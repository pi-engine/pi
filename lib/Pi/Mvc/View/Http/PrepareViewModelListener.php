<?php
/**
 * Prepare ViewModel listener
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
 * @package         Pi\Mvc
 * @subpackage      View
 */

namespace Pi\Mvc\View\Http;

use Pi;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface as Events;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class PrepareViewModelListener extends AbstractListenerAggregate
{
    protected $type;

    /**
     * {@inheritDoc}
     */
    public function attach(Events $events)
    {
        $sharedEvents = $events->getSharedManager();

        // Prepare ViewModel for MvcEvent
        // Must be triggered before ViewManager
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'prepareViewModel'), 20000);

        // Close debugger in case necessary
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'prepareDebugMode'),  1000);

        // Canonize ViewModel
        //$this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'canonizeViewModel'),  -70);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($this, 'canonizeViewModel'), -70);

        // Inject ViewModel, should be performed before injectTemplateListener
        //$this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'injectTemplate'),  -85);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($this, 'injectTemplate'), -85);
    }

    public function prepareViewModel(MvcEvent $e)
    {
        $type = $this->getType($e);
        if ('json' == $type) {
            $e->setViewModel(new JsonModel);
        }
    }

    /**
     * Disable debug mode for AJAX request
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function prepareDebugMode(MvcEvent $e)
    {
        // Disable error debugging for AJAX and Flash
       $request = $e->getRequest();
       if ($request->isXmlHttpRequest() || $request->isFlashRequest()) {
            Pi::service('log')->debugger(false);
        }
    }

    /**
     * Inspect the result, and cast it to a ViewModel
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function canonizeViewModel(MvcEvent $e)
    {
        $request = $e->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return;
        }

        $result = $e->getResult();

        $model = null;
        if ('json' == $this->getType($e)) {
            if ($result instanceof JsonModel) {
                $model = $result;
            } elseif ($result instanceof ViewModel) {
                $model = new JsonModel($result->getVariables());
            } elseif (ArrayUtils::hasStringKeys($result, true)) {
                $model = new JsonModel($result);
            } else {
                $model = new JsonModel;
            }
        } elseif ('ajax' == $this->getType($e)) {
            if ($result instanceof ViewModel) {
                $model = $result;
            } elseif (ArrayUtils::hasStringKeys($result, true)) {
                $model = new ViewModel($result);
            } elseif (is_scalar($result)) {
                $model = new ViewModel(array('content' => $result));
            } else {
                $model = new ViewModel(array('content' => json_encode($result)));
            }
            $model->setTerminal(true);
        }

        // Inject ViewModel
        if ($model) {
            // Skip following result handling
            $e->setResult(false);

            // Inject ViewModel
            $e->setViewModel($model);
        }
    }

    /**
     * Inject a template into the view model, if none present
     *
     * Template is derived from the controller found in the route match, and,
     * optionally, the action, if present.
     *
     * @see Pi\Mvc\Controller\Plugin\View::setTemplate()
     * @param  MvcEvent $e
     * @return void
     */
    public function injectTemplate(MvcEvent $e)
    {
        $model = $e->getResult();
        if (!$model instanceof ViewModel) {
            return;
        }

        $routeMatch = $e->getRouteMatch();

        $template = $model->getTemplate();
        // Preset variables for module templates, skip AJAX requests
        if ($template && '__NULL__' != $template) {
            $model->setVariables(array(
                'module'        => $routeMatch->getParam('module'),
                'controller'    => $routeMatch->getParam('controller'),
                'action'        => $routeMatch->getParam('action'),
            ));
        }

        if (!empty($template)) {
            return;
        }
        $engine = $e->getApplication()->getEngine();
        $template = sprintf('%s:%s/%s-%s', $routeMatch->getParam('module'), $engine->section(), $routeMatch->getParam('controller'), $routeMatch->getParam('action'));
        $model->setTemplate($template);
    }

    protected function getType(MvcEvent $e)
    {
        if ($this->type) {
            return $this->type;
        }

        $request = $e->getRequest();
        if ($request->isXmlHttpRequest()) {
            $this->type = 'ajax';
        }
        $headers = $request->getHeaders();
        if (!$headers->has('accept')) {
            return $this->type;
        }
        $accept  = $headers->get('Accept');

        // Detect JSON/JavaScript
        if (($match = $accept->match('application/json, application/javascript')) != false) {
            $typeString = $match->getTypeString();
            if ('application/json' == $typeString || 'application/javascript' == $typeString) {
                $this->type = 'json';
            }
        }

        return $this->type;
    }

}
