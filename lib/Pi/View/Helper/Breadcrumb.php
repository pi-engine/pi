<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHtmlElement;
/**
 * Helper for rendering module breadcrumbs
 *
 * Usage
 *
 * ```
 *  $content = $this->breadcrumb(
 *      array(
 *          'module'    => <module>,
 *          'separator' => <separator>,
 *          'prefix'    => array()
 *      ),
 *  );
 *
 *  $breadcrumbs = $this->breadcrumb()
 *      ->setSeparator(' &gt; ')
 *      ->setModule('demo')
 *      ->setPrefix(array(
 *          array(
 *              'label' => <label>,
 *              'href'  => <href>,
 *          ),
 *          array(
 *              'label' => <label>,
 *              'href'  => <href>,
 *          ),
 *      ));
 *  $content = $breadcrumbs->render();
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Breadcrumb extends AbstractHtmlElement
{
    /**
     * Breadcrumbs separator string
     *
     * @var string
     */
    protected $separator = ' &gt; ';

    /**
     * Module to load
     *
     * @var string
     */
    protected $module = '';

    /**
     * Items to prepend
     *
     * @var array
     */
    protected $prefix = array();

    /**
     * Renders module breadcrumbs
     *
     * @param array|null $options
     *
     * @return string|Breadcrumbs
     */
    public function __invoke($options = null)
    {
        if (null === $options) {
            return $this;
        }

        $content = $this->render($options);

        return $content;
    }

    /**
     * Renders breadcrumbs content
     *
     * @param array $options
     *
     * @return string
     */
    public function render(array $options = array())
    {
        $result = '';
        $module = isset($options['module'])
            ? $options['module']
            : $this->module;
        $module = $module ?: Pi::service('module')->current();

        $class = sprintf('Custom\Module\%s\Api\Breadcrumbs', ucfirst($module));
        if (!class_exists($class)) {
            $class = sprintf('Module\%s\Api\Breadcrumbs', ucfirst($module));
        }
        if (class_exists($class)) {
            $bcHandler = new $class($module);
            $data = $bcHandler->load();
            if ($data) {
                $prefix = isset($options['prefix'])
                    ? $options['prefix']
                    : $this->prefix;

                $data = $prefix + $data;
                $separator = isset($options['separator'])
                    ? $options['separator']
                    : $this->separator;
            }
        }

        return $result;
    }

    /**
     * Sets breadcrumb separator
     *
     * @param string $separator separator string
     * @return $this
     */
    public function setSeparator($separator)
    {
        if (is_string($separator)) {
            $this->separator = $separator;
        }

        return $this;
    }

    /**
     * Returns breadcrumb separator
     *
     * @return string  breadcrumb separator
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * Sets breadcrumb prefix
     *
     * @param array $prefix
     * @return $this
     */
    public function setPrefix(array $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Returns breadcrumb prefix
     *
     * @return array
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Sets module
     *
     * @param string $module
     * @return $this
     */
    public function setModule($module)
    {
        if (is_string($module)) {
            $this->module = $module;
        }

        return $this;
    }

    /**
     * Returns module
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

}
