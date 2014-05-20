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

use Zend\Form\Element\Textarea as ZendTextarea;

/**
 * {@inheritDoc}
 */
class Textarea extends ZendTextarea
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = array(
        'type'  => 'textarea',
        'rows'  => 3,
    );
}
