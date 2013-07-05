<?php
/**
 * Navigation form
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
use Pi\Form\Form as BaseForm;

class NavForm extends BaseForm
{
    /*
    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new NavFilter;
        }
        return $this->filter;
    }
    */

    public function init()
    {
        $this->add(array(
            'name'          => 'name',
            'options'       => array(
                'label' => __('Unique name'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'title',
            'options'       => array(
                'label' => __('Title'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        /*
        $this->add(array(
            'name'          => 'active',
            'options'       => array(
                'label' => __('Active'),
            ),
            'type'  => 'checkbox',
            'attributes'    => array(
                'value'     => '1',
            ),
        ));
        */

        $this->add(array(
            'name'          => 'section',
            'attributes'    => array(
                'type'      => 'hidden',
                'value'     => 'front',
            ),
        ));

        $this->add(array(
            'name'          => 'id',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => '',
            ),
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
