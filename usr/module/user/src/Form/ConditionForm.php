<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */

namespace Module\User\Form;

use Pi\Form\Form as BaseForm;

class ConditionForm extends BaseForm
{
    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new ConditionFilter;
        }
        return $this->filter;
    }

    public function init()
    {
        // id
        $this->add([
            'name'       => 'id',
            'attributes' => [
                'type' => 'hidden',
            ],
        ]);
        // title
        $this->add([
            'name'       => 'version',
            'options'    => [
                'label' => __('Version'),
            ],
            'attributes' => [
                'type'        => 'text',
                'description' => 'xx.xx format only',
                'required'    => true,
            ],
        ]);

        $this->add([
            'name'       => 'filename',
            'options'    => [
                'label' => __('File'),
            ],
            'attributes' => [
                'type'        => 'file',
                'description' => 'PDF only',
                'required'    => true,
            ],
        ]);

        $this->add([
            'name'       => 'active_at',
            'type'       => 'datepicker',
            'options'    => [
                'label'      => __('Active at'),
                'datepicker' => [
                    'format'         => 'yyyy-mm-dd',
                    'autoclose'      => true,
                    'todayBtn'       => true,
                    'todayHighlight' => true,
                    'weekStart'      => 1,
                ],
            ],
            'attributes' => [
                'description' => '',
                'required'    => true,
            ],
        ]);

        // Save
        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
        ]);
    }
}