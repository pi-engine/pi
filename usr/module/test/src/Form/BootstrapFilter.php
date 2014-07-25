<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-7-24
 * Time: 下午4:19
 */

namespace Module\Test\Form;

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-7-23
 * Time: 下午1:42
 */

namespace Module\Test\Form;


use Zend\InputFilter\InputFilter;
use Zend\Form;



class BootstrapFilter extends InputFilter{
    public function __construct()
    {
        $this->add(array(
            'name'        => 'username',
            'required'    => true,
            'filters'     => array(
                array(
                    'name'    => 'StringTrim',
                ),
            ),
            'validators'  => array(
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'min'     => 5,
                        'max'     => 25,
                    ),
                ),
            ),
        ));

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
        ));
    }

} 