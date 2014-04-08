<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Validator;

use Zend\Validator\Date as ZendDate;

class Date extends ZendDate
{
    /**
     * {@inheritDoc}
     */
    public function isValid($value)
    {
        if (!$value) {
            return true;
        }
        return parent::isValid($value);
    }
}
