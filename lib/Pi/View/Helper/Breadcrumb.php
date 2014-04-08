<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
    protected $separator = '';

    /**
     * Module to load
     *
     * @var string
     */
    protected $module = '';

    /**
     * HTML attributes
     *
     * @var array
     */
    protected $attributes = array();

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
        $data   = array();

        $module = isset($options['module'])
            ? $options['module']
            : $this->module;
        $module = $module ?: Pi::service('module')->current();

        $class = sprintf('Custom\%s\Api\Breadcrumbs', ucfirst($module));
        if (!class_exists($class)) {
            $directory = Pi::service('module')->directory($module);
            $class = sprintf('Module\%s\Api\Breadcrumbs', ucfirst($directory));
        }
        if (class_exists($class)) {
            $bcHandler = new $class($module);
            $data = $bcHandler->load();
        }
        if ($data) {
            $prefix = isset($options['prefix'])
                ? $options['prefix']
                : $this->prefix;

            $data = array_merge($prefix, $data);
            $separator = isset($options['separator'])
                ? $options['separator']
                : $this->separator;
            $attribs = isset($options['attributes'])
                ? $options['attributes']
                : $this->attributes;

            $pattern = '<ol class="breadcrumb"%s>' . PHP_EOL . '%s' . PHP_EOL . '</ol>';
            $patternLink = '<li><a href="%s">%s</a></li>' . PHP_EOL;
            $patternLabel = '<li>%s</li>' . PHP_EOL;

            $elements = '';
            foreach ($data as $item) {
                if (empty($item['href'])) {
                    $elements .= sprintf($patternLabel, _escape($item['label']));
                } else {
                    $elements .= sprintf($patternLink,$item['href'],  _escape($item['label']));
                }
            }
            $attributes = $attribs ? $this->htmlAttribs($attribs) : '';
            $result = sprintf($pattern, $attributes, $elements);
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
     * Sets breadcrumb html attributes
     *
     * @param array $attribs
     * @return $this
     */
    public function setAttributes(array $attribs)
    {
        $this->attributes = $attribs;

        return $this;
    }

    /**
     * Returns breadcrumb html attribs
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
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
