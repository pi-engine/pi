<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Pi\Form;

use Zend\Form\Fieldset as ZendFieldset;

/**
 * Form Field
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
        $elements = array(
            'active'    => array(),
            'hidden'    => array(),
        );

        foreach ($this->byName as $key => $value) {
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
