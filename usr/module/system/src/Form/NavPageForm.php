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
use Pi\Form\Form as BaseForm;

/**
 * Navigation page form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class NavPageForm extends BaseForm
{
    /**
     * {@inheritDoc}
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
                'description'   =>
                    __('The resource identity for permission check.'),
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

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            )
        ));
    }
}
