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
 * Simple search form class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class SimpleSearchForm extends BaseForm
{
    public function init()
    {
        $this->add(array(
            'name'       => 'keyword',
            'options'    => array(
                'label'     => '',
            ),
            'attributes' => array(
                'type'      => 'text',
            ),
        ));

        $this->add(array(
            'name'       => 'submit',
            'attributes' => array(               
                'value'     => __('Search'),
            ),
            'type'       => 'submit',
        ));
    }
}
