<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Custom\User\Form\Element;

use Zend\Form\Element;

/**
 * Industry/sector selector
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Industry extends Element
{
    /**
     * Seed attributes
     * @var array
     */
    protected $attributes = array(
        'type'  => 'Custom\User\Form\View\Helper\Industry',
    );
}
