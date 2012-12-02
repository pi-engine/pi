<?php
/**
 * Navigation page form input filter
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @subpackage      Form
 * @version         $Id$
 */

namespace Module\System\Form;

use Pi;
use Zend\InputFilter\InputFilter;

class NavPageFilter extends InputFilter
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
                    'name'  => 'Pi\\Filter\\Uri',
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

        /*
        $this->add(array(
            'name'      => 'node',
        ));

        $this->add(array(
            'name'      => 'position',
        ));
        */

        $this->add(array(
            'name'      => 'navigation',
            'required'  => true,
        ));
    }
}
