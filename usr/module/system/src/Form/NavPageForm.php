<?php
/**
 * Navigation page form
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

class NavPageForm extends BaseForm
{
    /*
    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new NavPageFilter;
        }
        return $this->filter;
    }
    */

    public function init()
    {
        $this->add(array(
            'name'          => 'label',
            'options'       => array(
                'label' => __('Label'),
            ),
        ));

        $this->add(array(
            'name'          => 'route',
            'options'       => array(
                'label' => __('Route'),
            ),
            'attributes'    => array(
                'description'   => __('Route to assemble URI.'),
            )
        ));

        $this->add(array(
            'name'          => 'module',
            'options'       => array(
                'label' => __('Module'),
            ),
        ));

        $this->add(array(
            'name'          => 'controller',
            'options'       => array(
                'label' => __('Controller'),
            ),
        ));

        $this->add(array(
            'name'          => 'action',
            'options'       => array(
                'label' => __('Action'),
            ),
        ));

        $this->add(array(
            'name'          => 'uri',
            'options'       => array(
                'label' => __('URI'),
            ),
        ));

        $this->add(array(
            'name'          => 'target',
            'type'          => 'select',
            'options'       => array(
                'label'     => __('Open target'),
                'value_options' => array(
                    ''          => __('None'),
                    'self'      => __('Current window'),
                    '_blank'    => __('Open a new window'),
                ),
            ),
        ));

        $this->add(array(
            'name'          => 'resource',
            'options'       => array(
                'label' => __('Permission resource'),
            ),
            'attributes'    => array(
                'description'   => __('The resource identity for permission check.'),
            ),
        ));

        $this->add(array(
            'name'          => 'visible',
            'type'          => 'checkbox',
            'options'       => array(
                'label' => __('Display'),
            ),
            'attributes'    => array(
                'description'   => __('To display in menu.'),
                'value' => '1',
            ),
        ));

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $this->add(array(
            'name'          => 'id',
            'attributes'    => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name'          => 'navigation',
            'attributes'    => array(
                'type'  => 'hidden',
            ),
        ));

        /*
        $this->add(array(
            'name'          => 'node',
            'attributes'    => array(
                'type'  => 'hidden',
            ),
        ));

        $this->add(array(
            'name'          => 'position',
            'attributes'    => array(
                'type'  => 'hidden',
            ),
        ));
        */

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            )
        ));
    }
}
