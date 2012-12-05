<?php
/**
 * Pi Engine Editor Abstract Renderer
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
 * @package         Pi\Editor
 * @version         $Id$
 */

namespace Pi\Editor;

use Pi;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\Form\ElementInterface;

abstract class AbstractRenderer
{
    /**
     * File name of config data
     * @var string
     */
    protected $configFile = '';

    protected $options = array();
    protected $attributes = array();

    /**
     * View renderer
     * @var Renderer
     */
    protected $view;

    /**
     * @param  array $confg Options and attributes
     */
    public function __construct($config = array())
    {
        if (!empty($this->configFile)) {
            $configDefault = Pi::config()->load($this->configFile);
            if (isset($configDefault['options'])) {
                if (isset($config['options'])) {
                    $config['options'] = array_merge($configDefault['options'], $config['options']);
                } else {
                    $config['options'] = $configDefault['options'];
                }
            }
            if (isset($configDefault['attributes'])) {
                if (isset($config['attributes'])) {
                    $config['attributes'] = array_merge($configDefault['attributes'], $config['attributes']);
                } else {
                    $config['attributes'] = $configDefault['attributes'];
                }
            }
        }
        if (isset($config['options'])) {
            $this->setOptions($config['options']);
        }
        if (isset($config['attributes'])) {
            $this->setAttributes($config['attributes']);
        }
    }

    /**
     * Set view renderer
     *
     * @param Renderer $view
     * @return AbstractEditor
     */
    public function setView(Renderer $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Set options for an element.
     *
     * @param  array $options
     * @return AbstractEditor
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get defined options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Return the specified option
     *
     * @param string $option
     * @return NULL|mixed
     */
    public function getOption($option)
    {
        if (!isset($this->options[$option])) {
            return null;
        }

        return $this->options[$option];
    }

    /**
     * Set value for option
     *
     * @param  string $name
     * @return AbstractEditor
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Set a single element attribute
     *
     * @param  string $key
     * @param  mixed  $value
     * @return AbstractEditor
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Retrieve a single element attribute
     *
     * @param  $key
     * @return mixed|null
     */
    public function getAttribute($key)
    {
        if (!array_key_exists($key, $this->attributes)) {
            return null;
        }
        return $this->attributes[$key];
    }

    /**
     * Does the element has a specific attribute ?
     *
     * @param  string $key
     * @return bool
     */
    public function hasAttribute($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Set many attributes at once
     *
     * Implementation will decide if this will overwrite or merge.
     *
     * @param  array $attributes
     * @return AbstractEditor
     */
    public function setAttributes($attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Retrieve all attributes at once
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Renders editor contents
     *
     * @param  ElementInterface $element
     * @return string|null
     */
    abstract public function render(ElementInterface $element);
}
