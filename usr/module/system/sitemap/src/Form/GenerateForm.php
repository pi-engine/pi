<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Sitemap\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class GenerateForm extends BaseForm
{

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new GenerateFilter;
        }
        return $this->filter;
    }

    public function init()
    {
        // file
        $this->add(array(
            'name' => 'file',
            'options' => array(
                'label' => __('File'),
            ),
            'attributes' => array(
                'type' => 'text',
                'description' => __('Set file name as filename.xml'),
            )
        ));
        // start
        $this->add(array(
            'name' => 'start',
            'options' => array(
                'label' => __('Start'),
            ),
            'attributes' => array(
                'type' => 'text',
            )
        ));
        // end
        $this->add(array(
            'name' => 'end',
            'options' => array(
                'label' => __('End'),
            ),
            'attributes' => array(
                'type' => 'text',
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