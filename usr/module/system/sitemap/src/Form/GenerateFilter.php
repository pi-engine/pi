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
use Zend\InputFilter\InputFilter;

class GenerateFilter extends InputFilter
{
    public function __construct()
    {
        // file
        $this->add(array(
            'name' => 'file',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
            'validators'    => array(
                new \Module\Sitemap\Validator\FileValidation,
            ),
        ));
        // start
        $this->add(array(
            'name' => 'start',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        // end
        $this->add(array(
            'name' => 'end',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
    }	
}	