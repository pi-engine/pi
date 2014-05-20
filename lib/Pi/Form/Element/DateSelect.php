<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\Element;

//use Pi;
use Zend\Form\Element\DateSelect as ZendDateSelect;
use Pi\Validator\Date as DateValidator;

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
        /*
        if (class_exists('\\DateTime')) {
            return parent::setValue($value);
        }
        */

        if (is_numeric($value)) {
            $value = date('Y-m-d', (int) $value);
        }
        if (is_string($value)) {
            $data = $value ? explode('-', $value) : array(0, 0, 0);
            $value = array(
                'year'  => $data[0],
                'month' => $data[1],
                'day'   => $data[2],
            );
        }

        $this->yearElement->setValue($value['year']);
        $this->monthElement->setValue($value['month']);
        $this->dayElement->setValue($value['day']);
    }

    /**
     * {@inheritDoc}
     */
    public function getInputSpecification()
    {
        $spec = parent::getInputSpecification();
        $spec['filters'] = array(
            array(
                'name'    => 'Callback',
                'options' => array(
                    'callback' => function ($date) {
                            // Convert the date to a specific format
                            if (is_array($date)) {
                                $date = array_filter($date);
                                if ($date) {
                                    $date = $date['year'] . '-' . $date['month'] . '-' . $date['day'];
                                } else {
                                    $date = '';
                                }
                            }

                            return $date;
                        }
                )
            )
        );

        return $spec;
    }

    /**
     * {@inheritDoc}
     */
    protected function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = new DateValidator(array('format' => 'Y-m-d'));
        }

        return $this->validator;
    }

}
