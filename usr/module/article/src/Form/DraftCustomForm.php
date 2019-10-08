<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form;

use Module\Article\Controller\Admin\SetupController;
use Pi\Form\Form as BaseForm;

/**
 * Custom draft form class
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DraftCustomForm extends BaseForm
{
    /**
     * Form elements in draft edit page
     * @var array
     */
    protected $items = [];

    /**
     * Saved custom elements
     * @var array
     */
    protected $custom = [];

    public function __construct($name, $options = [])
    {
        $this->items  = isset($options['elements'])
            ? $options['elements'] : [];
        $this->custom = isset($options['custom'])
            ? $options['custom'] : [];
        parent::__construct($name);
    }

    /**
     * Initialize form
     */
    public function init()
    {
        $this->add([
            'name'       => 'mode',
            'options'    => [
                'label' => __('Form Mode'),
            ],
            'attributes' => [
                'value'   => SetupController::FORM_MODE_EXTENDED,
                'options' => [
                    SetupController::FORM_MODE_NORMAL   => __('Normal'),
                    SetupController::FORM_MODE_EXTENDED => __('Extended'),
                    SetupController::FORM_MODE_CUSTOM   => __('Custom'),
                ],
            ],
            'type'       => 'radio',
        ]);

        foreach ($this->items as $name => $title) {
            $this->add([
                'name'       => $name,
                'options'    => [
                    'label' => $title,
                ],
                'attributes' => [
                    'value' => in_array($name, $this->custom) ? 1 : 0,
                ],
                'type'       => 'checkbox',
            ]);
        }

        $this->add([
            'name'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
            'type'       => 'submit',
        ]);
    }
}
