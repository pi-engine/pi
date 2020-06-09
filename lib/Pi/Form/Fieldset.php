<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form;

use Laminas\Form\Fieldset as ZendFieldset;

/**
 * Form Field
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 *
 * ToDo : fix for zend version 2.4.9
 */
class Fieldset extends ZendFieldset
{
    /**
     * {@inheritdoc}
     */
    public function getFormFactory()
    {
        if (null === $this->factory) {
            $this->setFormFactory(new Factory());
        }

        return $this->factory;
    }

    /**
     * Get list of elements
     *
     * Element list associated with active and hidden
     *
     *  - active: string[]
     *  - hidden: string[]
     *
     * @return array
     */
    public function elementList()
    {
        $elements = [
            'active' => [],
            'hidden' => [],
        ];

        foreach ($this->elements as $key => $value) {
            $type = $value->getAttribute('type');
            if ('hidden' == $type) {
                $elements['hidden'][] = $value;
            } else {
                $elements['active'][] = $value;
            }
        }

        return $elements;
    }
}
