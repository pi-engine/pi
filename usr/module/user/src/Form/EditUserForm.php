<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

//use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Class for initializing form of profile edit
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class EditUserForm extends BaseForm
{
    /** @var array List of user fields */
    protected $fields;

    /** @var string Form name */
    protected $name;

    public function __construct($name, $fields)
    {
        $this->fields = $fields;
        $this->name   = $name;
        parent::__construct($this->name);
    }

    public function init()
    {
        foreach ($this->fields as $field) {
            $this->add($field);
        }

        $this->add(array(
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => array(
                'value' => __('Submit'),
            ),
        ));
    }
}