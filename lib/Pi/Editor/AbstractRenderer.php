<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Editor;

use Pi;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\Form\ElementInterface;

/**
 * Editor renderer abstract class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractRenderer
{
    /**
     * File name of config data
     *
     * @var string
     */
    protected $configFile = '';

    /** @var array Options */
    protected $options = array();

    /** @var array Renderer attributes */
    protected $attributes = array();

    /**
     * View renderer
     *
     * @var Renderer
     */
    protected $view;

    /**
     * Constructor
     *
     * @param  array $confg Options and attributes
     */
    public function __construct($config = array())
    {
        if (!empty($this->configFile)) {
            $configDefault = Pi::config()->load($this->configFile);
            if (isset($configDefault['options'])) {
                if (isset($config['options'])) {
                    $config['options'] = array_merge(
                        $configDefault['options'],
                        $config['options']
                    );
                } else {
                    $config['options'] = $configDefault['options'];
                }
            }
            if (isset($configDefault['attributes'])) {
                if (isset($config['attributes'])) {
                    $config['attributes'] = array_merge(
                        $configDefault['attributes'],
                        $config['attributes']
                    );
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
     * @return $this
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
     * @return $this
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
     * @return null|mixed
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
