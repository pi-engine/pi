<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Form;

use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Register form
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class RegisterForm extends BaseForm
{
    protected $fields;
    protected $name;

    public function __construct($name, $field)
    {
        $this->fields = $field;
        $this->name   = $name;
        parent::__construct($this->name);
    }

    public function init()
    {
        foreach ($this->fields as $field) {
            $this->add($field);
        }

        $this->add(array(
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => array(
                'value' => __('Submit'),
            ),
        ));

    }
}