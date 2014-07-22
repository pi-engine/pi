<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-7-17
 * Time: 下午6:15
 */

namespace Module\Test\Form;

use Pi;
use Pi\Form\Form as BaseForm;
use Zend\Form;
use Zend\Form\Element;

class BootstrapForm extends BaseForm
{
    public function init()
    {
        // Adding a text form
        $this->add(array(
            'name'          => 'username',
            'attributes'    => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => __('Username'),
            ),
        ));

        // Adding a textarea form
        $this->add(array(
            'name'          => 'content',
            'attributes'    => array(
                'type'  => 'textarea',
                'cols'  => '50',
                'rows'  => '10',
            ),
            'options'       => array(
                'label' => __('Your details'),
            ),
        ));;

        // Adding a submit form
//        $this->add(array(
//            'name'          => 'submit',
//            'attributes'    => array(
//                'type'  => 'submit',
//                'value' => '提交',
//            )
//        ));
        $this->add(array(
            'name'          => 'id',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('提交'),
                'class' => 'btn btn-primary'
            )
        ));
    }
}