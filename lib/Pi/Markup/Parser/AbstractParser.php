<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Markup\Parser;

/**
 * Markup abstract parser class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractParser
{
    /** @var array Options */
    protected $options = array();

    /**
     * Constructor
     *
     * @param array|\Traversable $options
     */
    public function __construct($options = array())
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
        foreach ($options as $key => $val) {
            $this->options[$key] = $val;
        }

        return $this;
    }

    /**
     * Parse a string
     *
     * @param string $value
     *
     * @return string
     */
    abstract public function parse($value);
}
