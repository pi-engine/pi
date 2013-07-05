<?php
/**
 * Route form
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
 * @package         Module\Demo
 * @subpackage      Form
 * @version         $Id$
 */

namespace Module\Demo\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class RouteForm extends BaseForm
{
    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new RouteFilter;
        }
        return $this->filter;
    }

    public function init()
    {
        $this->add(array(
            'name'          => 'name',
            'options'       => array(
                'label' => __('Route name'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'type',
            'options'       => array(
                'label' => __('Class'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'priority',
            'options'       => array(
                'label' => __('Priority'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'id',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => 0,
            )
        ));

        $this->add(array(
            'name'          => 'module',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => '',
            )
        ));

        $this->add(array(
            'name'          => 'section',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => 'front',
            )
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
