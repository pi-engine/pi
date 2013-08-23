<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Filter;

use Zend\Filter\DateTimeFormatter as ZendDateTimeFormatter;

/**
 * Time display filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class DateTimeFormatter extends ZendDateTimeFormatter
{
    /**
     * {@inheritDoc}
     * @see Pi\Service\I18n
     */
    protected function normalizeDateTime($value)
    {
        if (is_numeric($value)) {
            $value = (int) $value;
        }
        if ($value && is_int($value)) {
            $result = _date($value);
        } elseif (!$value) {
            $result = '';
        } else {
            $result = $value ? parent::normalizeDateTime($value) : 0;
        }

        return $result;
    }
}
