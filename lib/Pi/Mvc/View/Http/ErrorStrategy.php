<?php
/**
 * Erroneous strategy listener
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

use Zend\View\Model\ViewModel;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface as Response;

class ErrorStrategy extends AbstractListenerAggregate
{
    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'prepareErrorViewModel'), -90);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'prepareErrorViewModel'), 100);
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

        if (!$viewModel->getVariable('message')) {
            $errorMessage = $e->getError();
            if (!is_string($errorMessage)) {
                $errorMessage = '';
            }
            $viewModel->setVariable('message', $errorMessage ?: '');
        }
        $viewModel->setVariable('code', $statusCode);

        $config  = $e->getApplication()->getServiceManager()->get('Config');
        $viewConfig = $config['view_manager'];
        $template = isset($viewConfig[$templateName]) ? $viewConfig[$templateName] : 'error';
        $viewModel->setTemplate($template);

        $e->setResult($viewModel);
    }
}
