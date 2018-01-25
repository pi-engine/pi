<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\Element;

//use Pi;
use Pi\Validator\Date as DateValidator;
use Zend\Form\Element\DateSelect as ZendDateSelect;

/**
 * Date select element
 *
 * Supports auto-detection of locale
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 *
 * ToDo : fix for zend version 2.4.9
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
            $value = date('Y-m-d', (int)$value);
        }
        if (is_string($value)) {
            $data  = $value ? explode('-', $value) : [0, 0, 0];
            $value = [
                'year'  => $data[0],
                'month' => $data[1],
                'day'   => $data[2],
            ];
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
        $spec            = parent::getInputSpecification();
        $spec['filters'] = [
            [
                'name'    => 'Callback',
                'options' => [
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
                    },
                ],
            ],
        ];

        return $spec;
    }

    /**
     * {@inheritDoc}
     */
    protected function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = new DateValidator(['format' => 'Y-m-d']);
        }

        return $this->validator;
    }

}
