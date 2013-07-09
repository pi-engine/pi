<?php
/**
 * Module block form
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
use Zend\InputFilter\InputFilter;
use Pi\Db\RowGateway\RowGateway as BlockRow;

class BlockModuleForm extends BaseForm
{
    protected $root;

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the element
     * @param BlockRow $root Root block to be cloned
     */
    public function __construct($name = null, $root = null)
    {
        $this->root = $root;
        parent::__construct($name);
    }

    /**
     * Retrieve input filter used by this form.
     *
     * Attaches defaults from attached elements, if no corresponding input
     * exists for the given element in the input filter.
     *
     * @return InputFilterInterface
     */
    /*
    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new InputFilter;
        }
        return $this->filter;
    }
    */

    public function getConfigs()
    {
        $configs = array();
        foreach ($this->root->config as $config) {
            $configs[] = $config->name;
        }
        return $configs;
    }

    public function init()
    {
        $this->add(array(
            'name'          => 'title',
            'options'       => array(
                'label' => __('Title'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'title_hidden',
            'type'          => 'checkbox',
            'options'       => array(
                'label' => __('Hide title'),
            ),
            'attributes'    => array(
                'description'   => __('Hide block title from display.'),
            )
        ));

        $this->add(array(
            'name'          => 'name',
            'options'       => array(
                'label' => __('Unique name'),
            ),
            'attributes'    => array(
                'type'          => 'text',
                'description'   => __('Set a unique name to be called as widget or leave as blank.'),
            )
        ));

        $this->add(array(
            'name'          => 'description',
            'options'       => array(
                'label' => __('Description'),
            ),
            'attributes'    => array(
                'type'          => 'text',
                'description'   => __('Set a hint to distinguish the block.'),
            )
        ));

        $this->add(array(
            'name'          => 'subline',
            'options'       => array(
                'label' => __('Subline'),
            ),
            'attributes'    => array(
                'type'          => 'textarea',
                'description'   => __('For block header subline. HTML is allowed.'),
            )
        ));

        $this->add(array(
            'name'          => 'class',
            'options'       => array(
                'label' => __('Style class'),
            ),
            'attributes'    => array(
                'type'          => 'text',
                'description'   => __('Specified block container css class.'),
            )
        ));

        $this->add(array(
            'name'          => 'cache_ttl',
            'type'          => 'cacheTtl',
            'options'       => array(
                'label' => __('Cache TTL'),
            ),
        ));

        $this->add(array(
            'name'          => 'cache_level',
            'type'          => 'cacheLevel',
            'options'       => array(
                'label' => __('Cache level'),
            ),
        ));

        $this->addConfigFieldset();

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $this->add(array(
            'name'  => 'root',
            'type'  => 'hidden',
            'attribues' => array(
                'value' => $this->root->id,
            ),
        ));

        $this->add(array(
            'name'  => 'id',
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

    protected function addConfigFieldset()
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
            $attributes = !empty($edit['attributes']) ? $edit['attributes'] : array();
            $attributes['value'] = isset($config['value']) ? $config['value'] : null;
            $attributes['description'] = empty($config['description']) ? '' : __($config['description']);

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

        //$configFieldset->prepareElement($this);
        //$this->add($configFieldset);
    }

    public function isValid()
    {
        $inputFilter = $this->getInputFilter();

        $inputFilter->add(array(
            'name'          => 'title',
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        $inputFilter->add(array(
            'name'          => 'name',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
            'validators'    => array(
                new \Module\System\Validator\BlockNameDuplicate(),
            ),
        ));

        $inputFilter->add(array(
            'name'          => 'description',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        $inputFilter->add(array(
            'name'          => 'subline',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        $inputFilter->add(array(
            'name'          => 'class',
            'required'      => false,
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));

        $inputFilter->add(array(
            'name'      => 'cache_ttl',
            'required'  => false,
        ));

        $inputFilter->add(array(
            'name'      => 'cache_level',
            'required'  => false,
        ));

        $inputFilter->add(array(
            'name'          => 'id',
            'required'      => true,
            'allow_empty'   => true,
        ));

        $inputFilter->add(array(
            'name'          => 'root',
            'required'      => true,
            'allow_empty'   => true,
        ));

        $inputFilter->add(array(
            'name'          => 'title_hidden',
            'required'      => true,
            'allow_empty'   => true,
        ));

        $this->addConfigFilter($inputFilter);

        return parent::isValid();
    }

    protected function addConfigFilter($inputFilter)
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
    }
}
