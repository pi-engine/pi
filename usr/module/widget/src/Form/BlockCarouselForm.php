<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Widget\Form;

use Pi;
use Pi\Form\Form as BaseForm;
use Zend\InputFilter\InputFilter;
use Module\Widget\Validator\WidgetNameDuplicate;
use Module\Widget\Form\Element\CarouselTemplate as CarouselTemplate;

class BlockCarouselForm extends BaseForm
{
    /**
     * Retrieve input filter used by this form.
     *
     * Attaches defaults from attached elements, if no corresponding input
     * exists for the given element in the input filter.
     *
     * @return InputFilterInterface
     */
    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new InputFilter;
        }

        return $this->filter;
    }

    public function init()
    {
        $this->add(array(
            'name'          => 'title',
            'options'       => array(
                'label' => __('Title'),
            ),
            'attributes'    => array(
                'type'  => 'text',
                'required'  => true,
            )
        ));

        $this->add(array(
            'name'          => 'name',
            'options'       => array(
                'label' => __('Unique name'),
            ),
            'attributes'    => array(
                'type'          => 'text',
                'required'  => true,
            )
        ));

        $this->add(array(
            'name'          => 'description',
            'options'       => array(
                'label' => __('Description'),
            ),
            'attributes'    => array(
                'type'          => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'template',
            'options'       => array(
                'label' => __('Template'),
            ),
            'type'          =>  'Module\Widget\Form\Element\CarouselTemplate',
            'attributes'    => array(
                'required'  => true,
            )
        ));

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $this->add(array(
            'name'  => 'id',
            'type'  => 'hidden',
        ));

        $this->add(array(
            'name'  => 'content',
            'type'  => 'hidden',
        ));

        $this->add(array(
            'name'  => 'title_hidden',
            'type'  => 'hidden',
            'attributes'    => array(
                'value' => '1',
            ),
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            )
        ));
    }

    public function isValid()
    {
        $inputFilter = $this->getInputFilter();

        $inputFilter->add(array(
            'name'          => 'title',
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        $inputFilter->add(array(
            'name'          => 'name',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                new WidgetNameDuplicate,
            ),
        ));

        $inputFilter->add(array(
            'name'          => 'description',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        $inputFilter->add(array(
            'name'          => 'template',
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        $inputFilter->add(array(
            'name'          => 'id',
            'required'      => true,
            'allow_empty'   => true,
        ));

        $inputFilter->add(array(
            'name'          => 'content',
            'required'      => true,
            'allow_empty'   => true,
        ));

        $inputFilter->add(array(
            'name'          => 'title_hidden',
            'required'      => true,
            'allow_empty'   => true,
        ));

        return parent::isValid();
    }
}
