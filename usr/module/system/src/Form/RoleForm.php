<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Pi\Form\Form as BaseForm;

/**
 * Role form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RoleForm extends BaseForm
{
    /** @var string Section name */
    protected $section = 'front';

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the form
     * @param string $section
     */
    public function __construct($name = null, $section = 'front')
    {
        $this->section = $section;
        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->add([
            'name'       => 'name',
            'options'    => [
                'label' => __('Name'),
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        $this->add([
            'name'       => 'title',
            'options'    => [
                'label' => __('Title'),
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        $this->add([
            'name'       => 'section',
            'type'       => 'select',
            'options'    => [
                'label'         => __('Section'),
                'value_options' => [
                    'front' => __('Front'),
                    'admin' => __('Admin'),
                ],
            ],
            'attributes' => [
                //'type'  => 'select',
                'value' => $this->section,
            ],
        ]);

        /*
        $this->add(array(
            'name'          => 'order',
            'options'       => array(
                'label' => __('Display order'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            ),
        ));
        */

        $this->add([
            'name'       => 'id',
            'attributes' => [
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name'       => 'active',
            'attributes' => [
                'type'  => 'hidden',
                'value' => '1',
            ],
        ]);

        /*
        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));
        */

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
        ]);
    }
}
