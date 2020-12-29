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
     *
     * @var array
     */
    protected $attributes
        = [
            'type' => 'editor',
        ];

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        if (null === $this->label) {
            $this->label = __('Text editor');
        }

        return parent::getLabel();
    }
}
