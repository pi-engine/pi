<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
namespace Module\User\Form;

use Pi;
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
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));
        // title
        $this->add(array(
            'name' => 'version',
            'options' => array(
                'label' => __('Version'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => 'xx.xx format only',
                'required' => true,
            )
        ));

        $this->add(array(
            'name' => 'filename',
            'options' => array(
                'label' => __('File'),
            ),
            'attributes' => array(
                'type' => 'file',
                'description' => 'PDF only',
                'required' => false,
            )
        ));

        $this->add(array(
            'name' => 'active_at',
            'type' => 'datepicker',
            'options' => array(
                'label' => __('Active at'),
                'datepicker' => array(
                    'format' => 'yyyy-mm-dd',
                    'autoclose' => true,
                    'todayBtn' => true,
                    'todayHighlight' => true,
                    'weekStart' => 1,
                ),
            ),
            'attributes' => array(
                'description' => '',
                'required' => true,
            )
        ));

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