<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\ViewModel;

/**
 * Cache for view content rendering in specific contexts: page, action, block
 *
 * @see Pi\Application\Service\Render
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Render extends AbstractResource
{
    /**
     * Cache storage
     * @var \Zend\Cache\Storage\Adapter\AbstractAdapter
     */
    protected $storage;

    /**
     * Context specific render cache
     * @var Pi\Application\Service\Render
     */
    protected $renderCache;

    /**
     * Namespace for cacheing
     * @var string
     */
    protected $namespace = 'render';

    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $events = $this->application->getEventManager();
        // Setup page cache strategy
        if (!empty($this->options['page'])) {
            // Page cache check,
            // must go after access check whose priority is 9999
            $events->attach(
                MvcEvent::EVENT_DISPATCH,
                array($this, 'checkPage'),
                1000
            );
            // Page cache check,
            // must go after access check whose priority is 9999
            $events->attach(
                MvcEvent::EVENT_FINISH,
                array($this, 'savePage'),
                -9000
            );
        } elseif (!empty($this->options['action'])) {
            // Setup action cache strategy
            $sharedEvents = $events->getSharedManager();
            // Attach listeners to controller
            $sharedEvents->attach(
                'controller',
                MvcEvent::EVENT_DISPATCH,
                array($this, 'checkAction'),
                999
            );
            $sharedEvents->attach(
                'controller',
                MvcEvent::EVENT_DISPATCH,
                array($this, 'saveAction'),
                -999
            );
        }
    }

    /**
     * Render cached data
     *
     * @param string $type
     * @param bool $create
     * @return Pi\Application\Service\Render
     */
    public function renderCache($type = null, $create = false)
    {
        if (empty($this->renderCache) || $create) {
            $this->renderCache = clone Pi::service('render');
        }
        if ($type) {
            $this->renderCache->setType($type);
        }

        return $this->renderCache;
    }

    /**
     * Check if page content is cached
     *
     * Load cache if available,
     * otherwise generated content will be stored to cache if page is cacable
     *
     * @param MvcEvent $e
     * @return void
     */
    public function checkPage(MvcEvent $e)
    {
        // Skip cache if error occured
        if ($e->isError()
            // Skip cache if request method is not GET
            || !$e->getRequest()->isGet()
        ) {
            return;
        }

        $cacheMeta = $this->cacheMeta($e, 'page');
        // Skip cache if disabled by preference
        if (empty($cacheMeta['ttl'])) {
            return;
        }

        $renderCache    = $this->renderCache('page');
        $cacheKey       = md5($e->getRequest()->getRequestUri());
        $namespace      = $e->getRouteMatch()->getParam('module');
        $renderCache->meta('key', $cacheKey)
                    ->meta('namespace', $namespace)
                    ->meta('ttl', $cacheMeta['ttl']);
        // Skip following dispatch events and render dispatch
        // and set cached content directly if content is cached
        if ($renderCache->isCached()) {
            if (isset($_GET['CLEAR'])) {
                Pi::service('log')->info('Page cache cleared');
                $renderCache->flushCache($namespace, $cacheKey);
                $renderCache->isOpened(true);
            } else {
                Pi::service('log')->info('Page cached');
                $response = $e->getResponse()->setContent(
                    $renderCache->cachedContent()
                );
                return $response;
            }
        } else {
            $renderCache->isOpened(true);
        }
    }

    /**
     * Save page content to cache
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function savePage(MvcEvent $e)
    {
        // Skip cache if error occured
        if ($e->isError()) {
            return;
        }
        if (!$this->renderCache()->isOpened()) {
            return;
        }
        $response = $e->getResponse();
        // Skip if response not OK
        if (!$response instanceof Response || !$response->isOk()) {
            return;
        }

        $content = $response->getContent();
        $this->renderCache()->saveCache($content);

        return;
    }

    /**
     * Check if action content is cached
     *
     * Load cache if available,
     * otherwise generated content will be stored to cache if action is cacable
     *
     * @param MvcEvent $e
     * @return void
     */
    public function checkAction(MvcEvent $e)
    {
        // Skip cache if error occured
        if ($e->isError()
            // Skip cache if request method is not GET
            || !$e->getRequest()->isGet()
        ) {
            return;
        }

        $cacheMeta = $this->cacheMeta($e, 'action');
        // Skip cache if disabled by preference
        if (empty($cacheMeta['ttl'])) {
            return;
        }

        $renderCache    = $this->renderCache('action');
        $viewModel      = $e->getTarget()->view()->getViewModel();

        $cacheKey       = md5($e->getRequest()->getRequestUri());
        $namespace      = $e->getRouteMatch()->getParam('module');
        $renderCache->meta('key', $cacheKey)
                    ->meta('namespace', $namespace)
                    ->meta('ttl', $cacheMeta['ttl']);
        // Skip following dispatch events and render dispatch
        // and set cached content directly if content is cached
        if ($renderCache->isCached()) {
            if (isset($_GET['CLEAR'])) {
                Pi::service('log')->info('Action cache cleared');
                $renderCache->flushCache($namespace, $cacheKey);
                $renderCache->isOpened(true);
            } else {
                $content = $renderCache->cachedContent();
                if (is_array($content)) {
                    $viewModel->setVariables($content);
                } else {
                    $e->getTarget()->view()->setTemplate(false);
                    $viewModel->setVariable('content', $content);
                }
                $e->setResult($viewModel);
                $e->getTarget()->skipExecute();
                Pi::service('log')->info('Action cached');
            }
        } else {
            $renderCache->isOpened(true);
        }
    }

    /**
     * Save action content to cache
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function saveAction(MvcEvent $e)
    {
        // Skip cache if error occured
        if ($e->isError()) {
            return;
        }
        if (!$this->renderCache()->isOpened()) {
            return;
        }
        $response = $e->getResponse();
        // Skip if response not OK
        if ($response instanceof Response && !$response->isOk()) {
            return;
        }

        $response = $e->getResult();
        if ($response instanceof ViewModel) {
            $content = (array) $response->getVariables();
            if (!$this->isCachable($content)) {
                trigger_error('Action content is not cachable.',
                              E_USER_WARNING);
                return;
            }
        } elseif (is_scalar($response)) {
            $content = $response;
        } else {
            return;
        }

        $this-renderCache()->saveCache($content);

        return;
    }

    /**
     * Read cache meta: TTL and level
     *
     * @param MvcEvent $e
     * @param string   $type
     * @return bool|array
     */
    protected function cacheMeta(MvcEvent $e, $type = 'page')
    {
        $route      = $e->getRouteMatch();
        $module     = $route->getParam('module');
        $controller = $route->getParam('controller');
        $action     = $route->getparam('action');

        $cacheInfo = false;
        $info = Pi::registry('cache')->read(
            $module,
            $this->application->getSection(),
            $type
        );
        if (empty($info)) {
            return $cacheInfo;
        }

        if (isset($info[sprintf('%s-%s-%s', $module, $controller, $action)])) {
            $cacheInfo = $info[sprintf('%s-%s-%s',
                                       $module,
                                       $controller,
                                       $action)];
        } elseif (isset($info[sprintf('%s-%s', $module, $controller)])) {
            $cacheInfo = $info[sprintf('%s-%s', $module, $controller)];
        } elseif (isset($info[$module])) {
            $cacheInfo = $info[$module];
        } else {
            return $cacheInfo;
        }

        return $cacheInfo;
    }

    /**
     * Check if action content can be cached, i.e. scalar or array
     *
     * @param mixed $content
     * @return bool
     */
    protected function isCachable($content)
    {
        if (is_scalar($content)) {
            return true;
        }
        if (!is_array($content)) {
            return false;
        }
        foreach ($content as $key => $val) {
            if (!$this->isCachable($val)) {
                return false;
            }
        }

        return true;
    }
}
