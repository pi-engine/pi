<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
class RenderCache extends AbstractResource
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
     * Namespace for caching
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
        //if ('page' == $type) {
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
        //} else {
            // Setup action cache strategy
            $sharedEvents = $events->getSharedManager();
            // Attach listeners to controller
            $sharedEvents->attach(
                'PI_CONTROLLER',
                MvcEvent::EVENT_DISPATCH,
                array($this, 'checkAction'),
                999
            );
            // Collect cachable content
            // Go after Zend\Mvc\View\Http\InjectTemplateListener::injectTemplate()
            $sharedEvents->attach(
                'PI_CONTROLLER',
                MvcEvent::EVENT_DISPATCH,
                array($this, 'saveAction'),
                -91
            );
        //}
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
            $this->renderCache = clone Pi::service('render_cache');
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
        // Skip cache if error occurred
        if ($e->isError()
            // Skip cache if request method is not GET
            || !$e->getRequest()->isGet()
        ) {
            return;
        }

        $cacheMeta = $this->cacheMeta($e, 'page');
        // Skip if not page cache
        if ('page' != $cacheMeta['type']) {
            return;
        }
        // Skip cache if disabled by preference
        if (empty($cacheMeta['ttl'])) {
            return;
        }

        $renderCache    = $this->renderCache('page');
        $cacheKey       = md5($e->getRequest()->getRequestUri());
        $namespace      = $e->getRouteMatch()->getParam('module');
        $renderCache->meta('key', $cacheKey)
            ->meta('namespace', $namespace)
            ->meta('level', $cacheMeta['level'])
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
                $content = $renderCache->cachedContent();
                $response = $e->getResponse()->setContent($content);

                // Check ETag for response
                if (!empty($this->options['enable_etag'])
                    && '1.1' == $response->getVersion()
                ) {
                    $etag = md5($content);
                    $response->getHeaders()->addHeaders(array(
                        'etag'          => $etag,
                        'cache-control' => 'must-revalidate, post-check=0, pre-check=0',
                    ));
                    $ifNoneMatch = $e->getRequest()->getHeader('if_none_match');
                    if ($ifNoneMatch) {
                        $ifNoneMatch = $ifNoneMatch->getFieldValue();
                        if ($ifNoneMatch && $ifNoneMatch == $etag) {
                            $response->setStatusCode(304);
                        }
                    }
                }

                $e->setResult($response);
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
        // Skip cache if error occurred
        if ($e->isError()) {
            return;
        }
        $cacheMeta = $this->cacheMeta($e, 'action');
        // Skip if not page cache
        if ('page' != $cacheMeta['type']) {
            return;
        }
        if (!$this->renderCache()->isOpened()) {
            return;
        }
        $this->renderCache()->isOpened(false);
        $response = $e->getResponse();
        // Skip if response not OK
        if (!$response instanceof Response || !$response->isOk()) {
            return;
        }

        $content = $response->getContent();
        $this->renderCache()->saveCache($content);

        // Set Etag for response header
        if (!empty($this->options['enable_etag'])
            && '1.1' == $response->getVersion()
        ) {
            $response->getHeaders()->addHeaders(array(
                'etag'          => md5($content),
                'cache-control' => 'must-revalidate, post-check=0, pre-check=0',
            ));
        }

        return;
    }

    /**
     * Check if action content is cached
     *
     * Load cache if available,
     * otherwise generated content will be stored to cache if action is cachable
     *
     * @param MvcEvent $e
     * @return void
     */
    public function checkAction(MvcEvent $e)
    {
        // Skip cache if error occurred
        if ($e->isError()
            // Skip cache if request method is not GET
            || !$e->getRequest()->isGet()
        ) {
            return;
        }
        if ($this->renderCache()->isOpened()) {
            return;
        }

        $cacheMeta = $this->cacheMeta($e, 'action');
        // Skip if not action cache
        if ('action' != $cacheMeta['type']) {
            return;
        }
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
            ->meta('level', $cacheMeta['level'])
            ->meta('ttl', $cacheMeta['ttl']);
        // Skip following dispatch events and render dispatch
        // and set cached content directly if content is cached
        if ($renderCache->isCached()) {

            if (isset($_GET['CLEAR'])) {
                Pi::service('log')->info('Action cache cleared');
                $renderCache->flushCache($namespace, $cacheKey);
                $renderCache->isOpened(true);
            } else {
                $actionData = $renderCache->cachedContent();
                $data = json_decode($actionData, true);
                if (!empty($data['template'])) {
                    $viewModel->setTemplate($data['template']);
                }
                if (!empty($data['options'])) {
                    $viewModel->setOptions($data['options']);
                }
                $viewModel->setVariables($data['variables']);
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
        // Skip cache if error occurred
        if ($e->isError()) {
            return;
        }
        $cacheMeta = $this->cacheMeta($e, 'action');
        // Skip if not action cache
        if ('action' != $cacheMeta['type']) {
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
        $data = array(
            'variables' => array(),
            'template'  => '',
            'options'   => array(),
        );
        if ($response instanceof ViewModel) {
            $variables = (array) $response->getVariables();
            if (!$this->isCachable($variables)) {
                trigger_error(
                    'Action content is not cachable.',
                    E_USER_WARNING
                );
                return;
            }
            $data = array(
                'variables' => $variables,
                'template'  => $response->getTemplate(),
                'options'   => $response->getOptions(),
            );
            //$content = Pi::service('view')->render($response);
        } elseif (is_scalar($response)) {
            $data['variables']['content'] = $response;
        } else {
            $data['variables'] = $response;
            //return;
        }

        //vd($content); exit;
        $this->renderCache()->saveCache(json_encode($data));
        $this->renderCache()->isOpened(false);

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

        $cacheInfo = array(
            'type'  => 'page',
            'ttl'   => 0,
            'level' => ''
        );
        $info = Pi::registry('page_cache')->read(
            $module,
            $this->application->getSection(),
            $type
        );
        if (empty($info)) {
            return $cacheInfo;
        }

        $key = sprintf('%s-%s-%s', $module, $controller, $action);
        if (isset($info[$key])) {
            $cacheInfo = $info[$key];
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
