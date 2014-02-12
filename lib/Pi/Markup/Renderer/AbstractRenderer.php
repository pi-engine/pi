<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Markup\Renderer;

use Pi\Markup\Parser\AbstractParser;
use Pi\Filter\FilterChain;
use Zend\Filter\AbstractFilter;
use Traversable;
use Zend\Stdlib\ArrayUtils;

/**
 * Abstract render for markup
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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

    /** @var array Options */
    protected $options = array();

    /** @var FilterChain Filters */
    protected $filterChain;

    /**
     * Constructor
     *
     * @param  array|Traversable $options
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

    /**
     * Set options
     *
     * @param array|\Traversable $options
     * @return $this
     */
    public function setOptions($options)
    {
        foreach ($options as $key => $val) {
            $this->options[$key] = $val;
        }

        return $this;
    }

    /**
     * Set filters
     *
     * @param \Zend\Filter\AbstractFilter[] $filters
     * @return $this
     */
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
     * @return $this
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
     * @return $this
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
     * @return string
     */
    abstract protected function parse($content);
}
