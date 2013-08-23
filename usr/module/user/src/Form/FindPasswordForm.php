<?php
/**
 * Find password form
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

class FindPasswordForm extends BaseForm
{
    public function init()
    {
        $this->add(array(
            'name'       => 'email',
            'options'    => array(
                'label' => __('Email'),
            ),
            'attributes' => array(
                'type' => 'text',
            ),
        ));

        $this->add(array(
            'name'    => 'captcha',
            'options' => array(
                'label'     => __('Please type the word'),
                'separator' => '<br />',
            ),
            'type'    => 'captcha',
        ));

        $this->add(array(
            'name' => 'security',
            'type' => 'csrf',
        ));

        $this->add(array(
            'name'       => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => __('Find password'),
                'class' => 'btn',
            ),
        ));
    }
}