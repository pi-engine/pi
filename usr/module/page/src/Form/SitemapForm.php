<?php

namespace Module\Page\Form;

use Pi\Form\Form as BaseForm;

class SitemapForm extends BaseForm
{
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    public function init()
    {
        // Save
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Submit'),
            )
        ));
    }
}
