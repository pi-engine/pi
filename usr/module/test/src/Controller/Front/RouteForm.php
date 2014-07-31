<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-7-18
 * Time: 上午11:32
 */

namespace Module\Test\Controller\Front;

use Pi;
use Pi\Form\Form as BaseForm;

class RouteForm extends BaseForm
{
    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new RouteFilter;
        }

        return $this->filter;
    }

    public function init()
    {
        $this->add(array(
            'name'          => 'name',
            'options'       => array(
                'label' => __('Route name'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'type',
            'options'       => array(
                'label' => __('Class'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'priority',
            'options'       => array(
                'label' => __('Priority'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'id',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => 0,
            )
        ));

        $this->add(array(
            'name'          => 'module',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => '',
            )
        ));

        $this->add(array(
            'name'          => 'section',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => 'front',
            )
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            )
        ));
    }
}