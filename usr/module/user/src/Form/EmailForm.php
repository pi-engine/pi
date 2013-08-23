<?php
/**
 * Email form
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
 * @author          Liu Chuang <liuchuang@eefocus.com>
 * @since           3.0
 * @package         Module\User
 * @subpackage      Form
 * @version         $Id$
 */

namespace Module\User\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class EmailForm extends BaseForm
{
    public function init()
    {
        $this->add(array(
            'name'       => 'email-new',
            'options'    => array(
                'label' => __('New email'),
            ),
            'attributes' => array(
                'type' => 'text',
            ),
        ));

        $this->add(array(
            'name'          => 'credential',
            'options'       => array(
                'label' => __('Current password'),
            ),
            'attributes'    => array(
                'type'  => 'password',
            )
        ));

        $this->add(array(
            'name'  => 'identity',
            'type'  => 'hidden',
        ));

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
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