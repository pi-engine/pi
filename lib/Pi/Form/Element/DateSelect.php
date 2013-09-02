<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Pi\Form\Element;

use Pi;
use Zend\Form\Element\DateSelect as ZendDateSelect;

/**
 * Date select element
 *
 * Supports auto-detection of locale
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class DateSelect extends ZendDateSelect
{
    /**
     * {@inheritDoc}
     */
    public function setValue($value)
    {
        if (class_exists('\\DateTime')) {
            return parent::setValue($value);
        }

        if (is_numeric($value)) {
            $value = date('Y-m-d', (int) $value);
        }
        if (is_string($value)) {
            list($year, $month, $day) = exlode('-', $value);
            $value = array(
                'year'  => $year,
                'month' => $month,
                'day'   => $day,
            );
        }

        $this->yearElement->setValue($value['year']);
        $this->monthElement->setValue($value['month']);
        $this->dayElement->setValue($value['day']);
    }

    /**
     * {@inheritDoc}
     */
    protected function getValidator()
    {
        if (class_exists('\\DateTime')) {
            return parent::getValidator();
        }

        return $this->validator;
    }

}
