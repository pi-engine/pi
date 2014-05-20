<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form;

use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Class for initializing form of add author page
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */ 
class AuthorEditForm extends BaseForm
{
    /**
     * Initalizing form 
     */
    public function init()
    {
        $this->add(array(
            'name'       => 'name',
            'options'    => array(
                'label'     => __('Name'),
            ),
            'attributes' => array(
                'id'        => 'name',
                'type'      => 'text',
            ),
        ));

        $this->add(array(
            'name'       => 'placeholder',
            'options'    => array(
                'label'     => __('Photo'),
            ),
            'attributes' => array(
            ),
        ));

        $this->add(array(
            'name'       => 'description',
            'options'    => array(
                'label'     => __('Biography'),
            ),
            'attributes' => array(
                'id'        => 'bio',
                'type'      => 'textarea',
            ),
        ));

        $this->add(array(
            'name'       => 'photo',
            'attributes' => array(
                'type'      => 'hidden',
            ),
        ));

        $this->add(array(
            'name'       => 'security',
            'type'       => 'csrf',
        ));

        $this->add(array(
            'name'       => 'id',
            'attributes' => array(
                'id'        => 'id',
                'type'      => 'hidden',
            ),
        ));

        $this->add(array(
            'name'       => 'fake_id',
            'attributes' => array(
                'id'        => 'fake_id',
                'type'      => 'hidden',
            ),
        ));

        $this->add(array(
            'name'       => 'submit',
            'attributes' => array(               
                'value'     => __('Submit'),
            ),
            'type'       => 'submit',
        ));
    }
}
