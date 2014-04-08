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

use Pi;
use Zend\Form\Element;

/**
 * Submit element
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Submit extends Element
{
    /**
     * Seed attributes
     * @var array
     */
    protected $attributes = array(
        'type'  => 'submit',
        'class' => 'btn btn-default',
    );

    /**
     * {@inheritDoc}
     */
    public function getValue()
    {
        if (null === $this->value) {
            $this->value = __('Submit');
        }

        return parent::getValue();
    }
}
