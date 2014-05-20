<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Comment\Form;

use Zend\InputFilter\InputFilter;

class PostFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name'          => 'content',
            'allow_empty'   => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        foreach (array(
                     'id',
                     'root',
                     'reply'
                 ) as $intElement
        ) {
            $this->add(array(
                'name'          => $intElement,
                'allow_empty'   => true,
                'filters'       => array(
                    array(
                        'name'  => 'Int',
                    ),
                ),
            ));
        }

        foreach (array(
                     'module',
                     'type',
                     'item',
                     'markup',
                     'redirect'
                 ) as $stringElement
        ) {
            $this->add(array(
                'name'          => $stringElement,
                'allow_empty'   => true,
                'filters'       => array(
                    array(
                        'name'  => 'StringTrim',
                    ),
                ),
            ));
        }
    }
}
