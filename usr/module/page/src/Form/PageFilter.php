<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Page\Form;

use Pi;
use Zend\InputFilter\InputFilter;

class PageFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name'          => 'title',
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        $this->add(array(
            'name'          => 'name',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                array(
                    'name'      => 'Regex',
                    'options'   => array(
                        'pattern'   => '/[a-z0-9_]/',
                    ),
                ),
                new \Module\Page\Validator\PageNameDuplicate(),
            ),
        ));

        $this->add(array(
            'name'          => 'slug',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                new \Module\Page\Validator\PageSlugDuplicate(),
            ),
        ));

        $this->add(array(
            'name'          => 'markup',
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        $this->add(array(
            'name'          => 'active',
        ));

        $this->add(array(
            'name'          => 'content',
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        $this->add(array(
            'name'          => 'id',
            'required'      => false,
        ));
    }
}
