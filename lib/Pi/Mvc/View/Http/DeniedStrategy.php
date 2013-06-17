<?php
/**
 * Inject template listener
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
 * @subpackage      View
 * @version         $Id$
 */

namespace Pi\Mvc\View\Http;

use Zend\View\Model\ViewModel;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface as Response;

class DeniedStrategy extends AbstractListenerAggregate
{
    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'prepareDeniedViewModel'), -90);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'prepareDeniedViewModel'));
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, array($this, 'prepareDeniedViewModel'), 100);
    }

    /**
     * Create and return a 404 view model
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function prepareDeniedViewModel(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof Response) {
            // Already have a response or view model as the result
            return;
        }

        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        if ($statusCode != 401 && $statusCode != 403) {
            return;
        }
        $errorMessage = $e->getError();
        if ('__denied__' == $errorMessage) {
            $errorMessage = '';
        }
        if (!$result instanceof ViewModel) {
            $result = new ViewModel();
            if (is_string($errorMessage)) {
                $result->setVariable('message', $errorMessage);
            }
        }
        if ($result->getVariable('message') === null || '__denied__' == $result->getVariable('message')) {
            $result->setVariable('message', $errorMessage ?: '');
        }
        $result->setVariable('code', $statusCode);

        $config  = $e->getApplication()->getServiceManager()->get('Config');
        $viewConfig = $config['view_manager'];
        $deniedTemplate = isset($viewConfig['denied_template']) ? $viewConfig['denied_template'] : 'error-denied';
        $result->setTemplate($deniedTemplate);

        $e->getViewModel()->addChild($result);
    }
}
