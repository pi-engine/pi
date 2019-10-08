<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

//use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Class for initializing form of compound
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class CompoundForm extends BaseForm
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

        $this->add([
            'name'       => 'set',
            'type'       => 'hidden',
            'attributes' => [
                'value' => 0,
            ],
        ]);

        $this->add([
            'name'       => 'group',
            'type'       => 'hidden',
            'attributes' => [
                'value' => '',
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
        ]);

    }
}