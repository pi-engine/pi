<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Form;

use Pi;
use Zend\InputFilter\InputFilter;

/**
 * Page adding form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PageAddFilter extends InputFilter
{
    /**
     * Constructor
     */
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
            'name'          => 'controller',
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                new \Module\System\Validator\ControllerAvailable(),
                new \Module\System\Validator\PageDuplicate(),
            ),
        ));

        $this->add(array(
            'name'          => 'action',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                new \Module\System\Validator\ActionAvailable(),
            ),
        ));

        $this->add(array(
            'name'      => 'section',
            'required'  => true,
        ));

        $this->add(array(
            'name'      => 'module',
            'required'  => true,
        ));

        $this->add(array(
            'name'      => 'cache_ttl',
            'required'  => false,
        ));

        $this->add(array(
            'name'      => 'cache_level',
            'required'  => false,
        ));
    }
}
