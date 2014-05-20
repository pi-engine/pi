<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Form;

use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Class for initializing form of edit application page
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */ 
class AppEditForm extends BaseForm
{
    /**
     * Initalizing form 
     */
    public function init()
    {
        $this->add(array(
            'name'       => 'title',
            'options'    => array(
                'label'     => __('Application Title'),
            ),
            'attributes' => array(
                'type'      => 'text',
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
            'name'       => 'appkey',
            'attributes' => array(
                'id'        => 'appkey',
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
