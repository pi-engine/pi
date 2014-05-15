<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Form;

use Pi\Form\Form;
use Zend\InputFilter\InputFilter;
//use Module\Widget\Validator\WidgetNameDuplicate;

abstract class AbstractBaseForm extends Form
{
    /** @var null|string Content type */
    protected $contentType = 'html';

    /**
     * Constructor
     *
     * @param string|int $name Optional name for the element
     * @param string $type Block type: clone, text, html, compound
     */
    public function __construct($name, $type)
    {
        $this->contentType = $type;
        parent::__construct($name);
    }

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
                'label' =>  _a('Title'),
            ),
            'attributes'    => array(
                'type'  => 'text',
                'required'  => true,
            )
        ));

        $this->add(array(
            'name'          => 'name',
            'options'       => array(
                'label' =>  _a('Unique name'),
            ),
            'attributes'    => array(
                'type'          => 'text',
                'required'  => true,
            )
        ));

        $this->add(array(
            'name'          => 'description',
            'options'       => array(
                'label' =>  _a('Description'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $template = $this->getTemplateElement();
        if ($template) {
            $this->add($template);
        }

        $content = $this->getContentElement();
        if ($content) {
            $this->add($content);
        }

        $this->add($this->getTypeElement());

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $this->add(array(
            'name'  => 'id',
            'type'  => 'hidden',
        ));

        $this->add(array(
            'name'          => 'title_hidden',
            'type'          => 'hidden',
            'attributes'    => array(
                'value' => 1,
            ),
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' =>  _a('Submit'),
                'class' => 'btn btn-primary',
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
                array(
                    'name'  => 'Module\Widget\Validator\WidgetNameDuplicate',
                ),
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

        $template = $this->getTemplateFilter();
        if ($template) {
            $inputFilter->add($template);
        }

        $content = $this->getContentFilter();
        if ($content) {
            $inputFilter->add($content);
        }

        $inputFilter->add(array(
            'name'          => 'id',
            'required'      => true,
            'allow_empty'   => true,
        ));

        $inputFilter->add(array(
            'name'          => 'title_hidden',
            'required'      => true,
            'allow_empty'   => true,
        ));

        $inputFilter->add(array(
            'name'          => 'type',
            'required'      => true,
            'allow_empty'   => true,
        ));

        return parent::isValid();
    }

    /**
     * Get form element specs for content type
     *
     * @return array
     */
    protected function getTypeElement()
    {
        return array(
            'name'  => 'type',
            'type'  => 'hidden',
            'attributes'    => array(
                'value'     => $this->contentType,
            ),
        );
    }

    /**
     * Get form element specs for template selection
     *
     * @return array
     */
    abstract protected function getTemplateElement();

    /**
     * Get form element filter specs for template selection
     *
     * @return array
     */
    abstract protected function getTemplateFilter();

    /**
     * Get form element specs for content
     *
     * @return array
     */
    abstract protected function getContentElement();

    /**
     * Get form element filter for content
     *
     * @return array
     */
    abstract protected function getContentFilter();
}
