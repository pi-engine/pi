<?php
/**
 * User system password form
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

class PasswordForm extends BaseForm
{
    protected $type;

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the element
     * @param Account $user User account row
     */
    public function __construct($name = null, $type = null)
    {
        $this->type = $type;
        parent::__construct($name);
    }


    public function init()
    {
        $config = Pi::service('registry')->config->read('user', 'general');

        if (!$this->type) {
            $this->add(array(
                'name'          => 'credential',
                'options'       => array(
                    'label' => __('Current password'),
                ),
                'attributes'    => array(
                    'type'  => 'password',
                )
            ));
        }


        $this->add(array(
            'name'          => 'credential-new',
            'options'       => array(
                'label' => __('New password'),
            ),
            'attributes'    => array(
                'type'  => 'password',
            )
        ));

        $this->add(array(
            'name'          => 'credential-confirm',
            'options'       => array(
                'label' => __('Confirm password'),
            ),
            'attributes'    => array(
                'type'  => 'password',
            )
        ));

        if ($config['register_captcha']) {
            $this->add(array(
                'name'          => 'captcha',
                'type'          => 'captcha',
                'options'       => array(
                    'label'     => __('Please type the word.'),
                    'separator' => '<br />',
                )
            ));
        }

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $this->add(array(
            'name'  => 'identity',
            'type'  => 'hidden',
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
