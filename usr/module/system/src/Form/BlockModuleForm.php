<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Pi;
use Pi\Db\RowGateway\RowGateway as BlockRow;
use Pi\Form\Form as BaseForm;
use Zend\InputFilter\InputFilter;

/**
 * Block module form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class BlockModuleForm extends BaseForm
{
    /** @var BlockRow Root block model */
    protected $root;

    /** @var bool Is for clone */
    protected $isClone = false;

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the element
     * @param BlockRow $root Root block to be cloned
     * @param bool $isClone
     */
    public function __construct($name = null, $root = null, $isClone = false)
    {
        $this->root    = $root;
        $this->isClone = $isClone;
        parent::__construct($name);
    }

    /**
     * Get block configs
     *
     * @return string[]
     */
    /* public function getConfigs()
    {
        $configs = array();
        foreach ($this->root->config as $config) {
            $configs[] = $config->name;
        }
        return $configs;
    } */

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
                'type'        => 'text',
                'description' => __('Block label for display.'),
            ],
        ]);

        $this->add([
            'name'       => 'title_hidden',
            'type'       => 'checkbox',
            'options'    => [
                'label' => __('Hide title'),
            ],
            'attributes' => [
                'description' => __('Hide block title from display.'),
            ],
        ]);

        $this->add([
            'name'       => 'body_fullsize',
            'type'       => 'checkbox',
            'options'    => [
                'label' => __('Full-size body'),
            ],
            'attributes' => [
                'description' => __('Display block body in full-size w/o padding.'),
            ],
        ]);

        $this->add([
            'name'       => 'name',
            'options'    => [
                'label' => __('Unique name'),
            ],
            'attributes' => [
                'type'        => 'text',
                'description' => __('A unique name to be called as widget.'),
            ],
        ]);

        $templateSpec = [
            'type'       => 'text',
            'attributes' => [
                'value'       => $this->root->template,
                'description' => __('PHTML rendering template, file extension is optional.'),
            ],
        ];
        if ('widget' == $this->root->module) {
            $spec = Pi::api('block', 'widget')->templateSpec($this->root->type);
            if (false === $spec) {
                $templateSpec = false;
            } elseif ($spec) {
                $templateSpec = array_replace($templateSpec, $spec);
            }
        }
        if (false !== $templateSpec) {
            $templateSpec = array_replace($templateSpec, [
                'name'    => 'template',
                'options' => [
                    'label' => __('Template'),
                ],
            ]);

            // Only cloned blocks are allowed to change template
            if (!$this->isClone) {
                $templateSpec['type']                   = 'text';
                $templateSpec['attributes']['readonly'] = 'readonly';
            }
            $this->add($templateSpec);
        }

        $this->add([
            'name'       => 'description',
            'options'    => [
                'label' => __('Description'),
            ],
            'attributes' => [
                'type'        => 'text',
                'description' => __('Set a hint to distinguish the block.'),
            ],
        ]);

        $this->add([
            'name'       => 'subline',
            'options'    => [
                'label' => __('Subline'),
            ],
            'attributes' => [
                'type'        => 'textarea',
                'description' => __('Header subline. HTML is allowed.'),
            ],
        ]);

        $this->add([
            'name'       => 'class',
            'options'    => [
                'label' => __('Style class'),
            ],
            'attributes' => [
                'type'        => 'text',
                'description' => __('Specified block container CSS class.'),
            ],
        ]);

        $this->add([
            'name' => 'cache_ttl',
            'type' => 'cache_ttl',
        ]);

        $this->add([
            'name' => 'cache_level',
            'type' => 'cache_level',
        ]);

        //$this->addConfigFieldset();

        // Load config field
        if ($this->root->config) {

            // extra_text
            $this->add([
                'name'    => 'extra_config',
                'type'    => 'fieldset',
                'options' => [
                    'label' => __('Configs'),
                ],
            ]);

            foreach ($this->root->config as $name => $field) {
                $edit = [];
                if (!empty($field['edit'])) {
                    if (is_string($field['edit'])) {
                        $edit['type'] = $field['edit'];
                    } else {
                        $edit = $field['edit'];
                    }
                }
                $attributes                = !empty($edit['attributes'])
                    ? $edit['attributes'] : [];
                $attributes['value']       = isset($field['value'])
                    ? $field['value'] : null;
                $attributes['description'] = empty($field['description'])
                    ? '' : __($field['description']);

                $options = [
                    'label'                     => __($field['title']),
                    'module'                    => $this->root->module,
                    'disable_inarray_validator' => true,
                ];
                if (!empty($edit['options'])) {
                    $options = array_merge($edit['options'], $options);
                }

                $element = [
                    'name'       => $name,
                    'attributes' => $attributes,
                    'options'    => $options,
                ];
                if (!empty($edit['type'])) {
                    $element['type'] = $edit['type'];
                }

                //d($element);

                $this->add($element);
            }
        }

        $this->add([
            'name' => 'security',
            'type' => 'csrf',
        ]);

        /*
        $this->add(array(
            'name'  => 'root',
            'type'  => 'hidden',
            'attribues' => array(
                'value' => $this->root->id,
            ),
        ));
        */

        $this->add([
            'name' => 'id',
            'type' => 'hidden',
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
        ]);
    }

    /**
     * Add config fieldset
     *
     * @return void
     */
    /* protected function addConfigFieldset()
    {
        if (!$this->root->config) {
            return;
        }

        $this->add(array(
            'name'  => 'config',
            'type'  => 'fieldset',
            'options'   => array(
                'label' => __('Configs'),
            )
        ));

        $configFieldset = $this->get('config');

        foreach ($this->root->config as $name => $config) {
            $edit = array();
            if (!empty($config['edit'])) {
                if (is_string($config['edit'])) {
                    $edit['type'] = $config['edit'];
                } else {
                    $edit = $config['edit'];
                }
            }
            $attributes = !empty($edit['attributes'])
                ? $edit['attributes'] : array();
            $attributes['value'] = isset($config['value'])
                ? $config['value'] : null;
            $attributes['description'] = empty($config['description'])
                ? '' : __($config['description']);

            $options = array(
                'label'     => __($config['title']),
                'module'    => $this->root->module,
            );
            if (!empty($edit['options'])) {
                $options = array_merge($edit['options'], $options);
            }

            $element = array(
                'name'          => $name,
                'attributes'    => $attributes,
                'options'       => $options,
            );
            if (!empty($edit['type'])) {
                $element['type'] = $edit['type'];
            }

            $configFieldset->add($element);
        }
    } */

    /**
     * {@inheritDoc}
     */
    public function isValid()
    {
        $inputFilter = $this->getInputFilter();

        $inputFilter->add([
            'name'    => 'title',
            'filters' => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $inputFilter->add([
            'name'       => 'name',
            'required'   => false,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                [
                    'name' => 'Module\System\Validator\BlockNameDuplicate',
                ],
            ],
        ]);

        $inputFilter->add([
            'name'     => 'description',
            'required' => false,
            'filters'  => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $inputFilter->add([
            'name'     => 'subline',
            'required' => false,
            'filters'  => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $inputFilter->add([
            'name'     => 'class',
            'required' => false,
            'filters'  => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $inputFilter->add([
            'name'     => 'cache_ttl',
            'required' => false,
        ]);

        $inputFilter->add([
            'name'     => 'cache_level',
            'required' => false,
        ]);

        $inputFilter->add([
            'name'     => 'id',
            'required' => true,
            //'allow_empty'   => true,
        ]);

        /*
        $inputFilter->add(array(
            'name'          => 'root',
            'required'      => true,
            'allow_empty'   => true,
        ));
        */

        $inputFilter->add([
            'name'        => 'title_hidden',
            'required'    => true,
            'allow_empty' => true,
        ]);

        $inputFilter->add([
            'name'        => 'body_fullsize',
            'required'    => true,
            'allow_empty' => true,
        ]);

        //$this->addConfigFilter($inputFilter);

        // Load config filter
        if ($this->root->config) {
            foreach ($this->root->config as $name => $field) {
                $element = [
                    'name'        => $name,
                    'required'    => false,
                    'allow_empty' => true,
                ];
                $inputFilter->add($element);
            }
        }

        return parent::isValid();
    }

    /**
     * Add filter
     *
     * @param InputFilter $inputFilter
     * @return void
     */
    /* protected function addConfigFilter($inputFilter)
    {
        if (!$this->root->config) {
            return;
        }
        $configInputFilter = new InputFilter;
        foreach($this->get('config')->getIterator() as $element) {
            $filter = array(
                'name'          => $element->getName(),
                'required'      => true,
                'allow_empty'   => true,
                'filters'       => array(
                    array(
                        'name'  => 'StringTrim',
                    ),
                ),
            );
            $configInputFilter->add($filter);
        }

        $inputFilter->add($configInputFilter, 'config');
    } */
}
