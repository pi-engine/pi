<?php
/**
 * Pi Engine Markup Renderer
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
 * @since           1.0
 * @package         Pi\Markup
 * @version         $Id$
 */

namespace Pi\Markup\Renderer;

use Pi\Markup\Parser\AbstractParser;
use Pi\Filter\FilterChain;
use Traversable;
use Zend\Stdlib\ArrayUtils;

/**
 * Defines the basic rendering functionality
 *
 * @category   Zend
 * @package    Zend_Markup
 * @subpackage Renderer
 */
abstract class AbstractRenderer
{
    /**
     * Parser
     *
     * @var AbstractParser
     */
    protected $parser;

    /**
     * Encoding
     *
     * @var string
     */
    protected $encoding = 'UTF-8';

    protected $options = array();
    protected $filterChain;

    /**
     * Constructor
     *
     * @param  array|Traversable $options
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (isset($options['encoding'])) {
            $this->setEncoding($options['encoding']);
            unset($options['encoding']);
        }
        if (isset($options['parser'])) {
            $this->setParser($options['parser']);
            unset($options['parser']);
        }

        if (isset($options['filters'])) {
            $this->setFilters($options['filters']);
            unset($options['filters']);
        }

        $this->setOptions($options);
    }

    public function setOptions($options)
    {
        foreach ($options as $key => $val) {
            $this->options[$key] = $val;
        }

        return $this;
    }

    public function setFilters($filters)
    {
        if (!$this->filterChain instanceof FilterChain) {
            $this->filterChain = new FilterChain;
        }

        foreach ($filters as $name => $options) {
            $this->filterChain->attachByName($name, $options);
        }

        return $this;
    }

    /**
     * Set the parser
     *
     * @param AbstractParser|string $parser
     * @return AbstractRenderer
     */
    public function setParser($parser)
    {
        $this->parser = $parser;

        return $this;
    }

    /**
     * Get the parser
     *
     * @return AbstractParser|string
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * Set the renderer's encoding
     *
     * @param string $encoding
     *
     * @return AbstractRenderer
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Get the renderer's encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Render function
     *
     * @param string $content
     *
     * @return string
     */
    public function render($content)
    {
        $content = $this->parse($content);
        if ($this->filterChain instanceof FilterChain) {
            $content = $this->filterChain->filter($content);
        }

        return $content;
    }

    /**
     * Parse content
     *
     * @param string $content
     *
     * @return string
     */
    abstract protected function parse($content);
}
