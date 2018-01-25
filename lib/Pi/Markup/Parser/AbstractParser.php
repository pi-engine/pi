<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Markup\Parser;

use Pi\Filter\FilterChain;
use Traversable;
use Zend\Stdlib\ArrayUtils;

/**
 * Markup abstract parser class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractParser
{
    /** @var array Options */
    protected $options = [];

    /** @var FilterChain Filters */
    protected $filterChain;

    /** @var array */
    protected $filters = [];

    /**
     * Constructor
     *
     * @param array|\Traversable $options
     */
    public function __construct($options = [])
    {
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
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (isset($options['filters'])) {
            $this->setFilters($options['filters']);
            unset($options['filters']);
        }

        foreach ($options as $key => $val) {
            $this->options[$key] = $val;
        }

        return $this;
    }

    /**
     * Set filters
     *
     * @param array $filters
     *
     * @return $this
     */
    public function setFilters(array $filters)
    {
        $this->filterChain = new FilterChain;
        $this->addFilters($filters);

        return $this;
    }

    /**
     * Set filters
     *
     * @param array $filters
     *
     * @return $this
     */
    public function addFilters(array $filters)
    {
        if (!$this->filterChain instanceof FilterChain) {
            $this->filterChain = new FilterChain;
        }

        foreach ($filters as $name => $options) {
            if (false === $options) {
                continue;
            }
            if (isset($options['priority'])) {
                $priority = $options['priority'];
                unset($options['priority']);
            } else {
                $priority = null;
            }
            if (is_string($name)) {
                if (isset($this->filters[$name])) {
                    continue;
                }
                $this->filterChain->attachByName($name, $options, $priority);
                $this->filters[$name] = true;
            } else {
                $this->filterChain->attach($name, $options, $priority);
            }
        }

        return $this;
    }

    /**
     * Parse content
     *
     * @param string $value
     *
     * @return string
     */
    abstract protected function parseContent($value);

    /**
     * Parse a string
     *
     * @param string $value
     *
     * @return string
     */
    public function parse($value)
    {
        $value = $this->parseContent($value);
        if ($this->filterChain instanceof FilterChain) {
            $value = $this->filterChain->filter($value);
        }

        return $value;
    }
}
