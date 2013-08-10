<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\View\Http;

use Zend\View\Model\ViewModel;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\ClearableModelInterface;

/**
 * Erroneous strategy listener
 *
 * Prepare for error ViewModel, should be performed prior to
 *
 * - `Pi\Mvc\View\Http\ViewStrategyListener::injectTemplate()`
 *      whose priority is -89
 * - `\Zend\Mvc\View\Http\InjectTemplateListener::injectTemplate()`
 *      whose priority is -90
 *
 * RouteNotFound is handled by:
 *  `Zend\Mvc\View\Http\RouteNotFoundStrategy::prepareNotFoundViewModel()`
 *  whose priority is -90
 *
 * @see Pi\Mvc\View\Http\ViewStrategyListener::injectTemplate()
 * @see \Zend\Mvc\View\Http\InjectTemplateListener::injectTemplate()
 * @see \Zend\Mvc\View\Http\RouteNotFoundStrategy::prepareNotFoundViewModel()
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ErrorStrategy extends AbstractListenerAggregate
{
    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $sharedEvents->attach(
            'Zend\Stdlib\DispatchableInterface',
            MvcEvent::EVENT_DISPATCH,
            array($this, 'prepareErrorViewModel'),
            -85
        );

        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_DISPATCH,
            array($this, 'prepareErrorViewModel'),
            -85
        );
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_RENDER,
            array($this, 'prepareErrorViewModel'),
            100
        );
    }

    /**
     * Create and return a view model for erroneous result
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function prepareErrorViewModel(MvcEvent $e)
    {
        // Do nothing if no error in the event
        $error = $e->getError();
        if (empty($error)) {
            return;
        }

        // Do nothing if the result is a response object
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }

        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();

        $templateName = '';
        switch ($statusCode) {
            case 401:
            case 403:
                $templateName = 'denied_template';
                break;
            case 404:
                // Handled by RouteNotFoundStrategy
                break;
            case 503:
            default:
                if ($statusCode >= 400) {
                    $templateName = 'error_tempalte';
                }
            break;
        }
        if (!$templateName) {
            return;
        }

        $viewModel = null;
        if (!$result instanceof ViewModel) {
            $viewModel = new ViewModel;
        } else {
            $viewModel = $result;
        }

        if (!$viewModel->getTemplate()) {
            $config = $e->getApplication()->getServiceManager()->get('Config');
            $viewConfig = $config['view_manager'];
            $template = isset($viewConfig[$templateName])
                ? $viewConfig[$templateName] : 'error';
            $viewModel->setTemplate($template);
        }

        if (!$viewModel->getVariable('message')) {
            $errorMessage = $e->getError();
            if (!is_string($errorMessage)) {
                $errorMessage = '';
            }
            $viewModel->setVariable('message', $errorMessage ?: '');
        }
        $viewModel->setVariable('code', $statusCode);

        $e->setResult($viewModel);

        // Inject error ViewModel to root ViewModel in case
        // InjectViewModelListener is not triggered
        $model = $e->getViewModel();
        if ($model instanceof ClearableModelInterface) {
            $model->clearChildren();
        }
        $model->addChild($viewModel);
    }
}
