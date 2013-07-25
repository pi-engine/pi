<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Filter;

use Pi;
use Zend\Filter\AbstractFilter;

/**
 * URI filter
 *
 * Tranform URI to full URI
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Uri extends AbstractFilter
{
    /**
     * Filter options
     * @var array
     */
    protected $options = array(
        'allowRelative' => false,
    );

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Transform text
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        if ($this->options['allowRelative'] || empty($value)) {
            return $value;
        }

        if (!preg_match('/^(http[s]?:\/\/|\/\/)/i', $value)) {
            $value = Pi::url('www') . '/' . ltrim($value, '/');
        }

        return $value;
    }
}
