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

use Zend\Form\Element\Textarea;

/**
 * Custom Textarea element with custom editor
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Editor extends Textarea
{
    /**
     * Seed attributes
     * @var array
     */
    protected $attributes = array(
        'type' => 'editor',
    );
}
