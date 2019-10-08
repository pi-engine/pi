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
 * Page adding form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PageAddForm extends BaseForm
{
    /** @var string Module name */
    protected $module;

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the form
     * @param string $module Page module
     */
    public function __construct($name = null, $module = null)
    {
        $this->module = $module;
        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
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
            'attributes' => [
                'type'  => 'hidden',
                'value' => 'front',
            ],
        ]);

        $this->add([
            'name'    => 'controller',
            'options' => [
                'label'  => __('Controller'),
                'module' => $this->module,
            ],
            'type'    => 'Module\System\Form\Element\Controller',
        ]);

        $this->add([
            'name'    => 'action',
            'options' => [
                'label' => __('Action'),
            ],
        ]);

        $this->add([
            'name' => 'security',
            'type' => 'csrf',
        ]);

        $this->add([
            'name'       => 'module',
            'attributes' => [
                'type'  => 'hidden',
                'value' => $this->module,
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
        ]);
    }
}
