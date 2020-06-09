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

use Laminas\Form\Element\Textarea as LaminasTextarea;

/**
 * {@inheritDoc}
 */
class Textarea extends LaminasTextarea
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes
        = [
            'type' => 'textarea',
            'rows' => 3,
        ];
}
