<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Filter;

use Laminas\Filter\DateTimeFormatter as LaminasDateTimeFormatter;

/**
 * Time display filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class DateTimeFormatter extends LaminasDateTimeFormatter
{
    /**
     * {@inheritDoc}
     * @see Pi\Service\I18n
     */
    protected function normalizeDateTime($value)
    {
        if (is_numeric($value)) {
            $value = (int)$value;
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
