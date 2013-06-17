<?php
namespace Module\Demo\Form;

use Pi;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Regex;

class DemoFilter extends InputFilter
{
	 public function __construct() {
		$this->add(array(
            'name' => 'username',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
            'validators' => array(
            	new Regex('/^\w{5,}$/')
            ),
        ));

        $this->add(array(
            'name' => 'email',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
            'validators' => array(
                new Regex('/^[0-9a-z_][_.0-9a-z-]{0,31}@([0-9a-z][0-9a-z-]{0,30}\.){1,4}[a-z]{2,4}$/')
            ),
        ));
	 }
}