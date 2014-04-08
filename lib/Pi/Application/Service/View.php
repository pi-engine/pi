<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Zend\Mvc\View\Http\ViewManager;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewMode;

/**
 * View handling service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class View extends AbstractService
{
    /** @var ViewManager */
    protected $viewManager;

    /**
     * Load ViewManager
     *
     * @return ViewManager
     */
    public function getViewManager()
    {
        if (!$this->viewManager) {
            $this->viewManager = Pi::engine()->application()
                ->getServiceManager()->get('view_manager');
        }

        return $this->viewManager;
    }

    /**
     * Get view helper
     *
     * @param string $name
     *
     * @return AbstractHelper
     */
    public function getHelper($name)
    {
        $helper = $this->getViewManager()->getHelperManager()->get($name);

        return $helper;
    }

    /**
     * Render a template or a view model
     *
     * @param string|array|ViewModel  $template
     * @param array         $variables
     *
     * @return string
     */
    public function render($template, array $variables = array())
    {
        if ($template instanceof ViewModel) {
            $template->setVariables($variables);
        } elseif (is_array($template)) {
            $section = isset($template['section'])
                ? $template['section']
                : Pi::engine()->application()->getSection();
            $module = !empty($template['module'])
                ? $template['module']
                : Pi::service('module')->current();
            $file = $template['file'];
            $template = $module . ':'
                      . ($section ? $section . '/' : '')
                      . $file;
        }
        $content = $this->getViewManager()->getRenderer()
            ->render($template, $variables);

        return $content;
    }

    /**
     * Magic methods to ViewManager
     *
     * @param string    $method
     * @param array     $args
     *
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, array $args = array())
    {
        if (!is_callable(array($this->getViewManager(), $method))) {
            throw new \Exception(sprintf('Method %s is not defined.', $method));
        }
        $result = call_user_func_array(
            array($this->getViewManager(), $method),
            $args
        );

        return $result;
    }
}
