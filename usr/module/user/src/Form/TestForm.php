<?php

namespace Module\User\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class TestForm extends BaseForm
{
    public function init()
    {
        $this->add(array(
            'name'  => 'test',
            'type'  => 'hidden',
            'attributes' => array(
                'value' => 'dasdada'
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => 'submit',
            ),
        ));
    }
}