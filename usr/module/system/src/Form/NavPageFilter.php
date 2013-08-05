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
 * Navigation page form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class NavPageFilter extends InputFilter
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
            'name'          => 'route',
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        $this->add(array(
            'name'          => 'module',
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
        ));

        $this->add(array(
            'name'          => 'action',
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        $this->add(array(
            'name'          => 'uri',
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
                array(
                    'name'  => 'Pi\Filter\Uri',
                ),
            ),
        ));

        $this->add(array(
            'name'      => 'active',
        ));

        $this->add(array(
            'name'      => 'target',
        ));

        $this->add(array(
            'name'      => 'id',
        ));

        $this->add(array(
            'name'      => 'navigation',
            'required'  => true,
        ));
    }
}
