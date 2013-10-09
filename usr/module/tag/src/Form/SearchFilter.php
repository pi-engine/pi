<?php
/**
 * Tag form
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
 * @author          Chuang Liu <liuchuang@eefocus.com>
 * @since           3.0
 * @package         Module\Tag
 * @subpackage      Form
 * @version         $Id$
 */

namespace Module\Tag\Form;

use Pi;
use Zend\InputFilter\InputFilter;

class SearchFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name'          => 'tagname',
            'required'      => true,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));
    }
}
