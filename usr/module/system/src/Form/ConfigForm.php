<?php
/**
 * Configuration form
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

class ConfigForm extends BaseForm
{
    protected $module;
    protected $name = 'config';
    protected $configs;

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the element
     * @param string $module
     */
    public function __construct($configs, $module)
    {
        $this->module   = $module;
        $this->configs  = $configs;
        parent::__construct($this->name);
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

    public function init()
    {
        foreach ($this->configs as $config) {
            $this->addElement($config);
        }

        $this->add(array(
            'name'  => 'submit',
            'type'  => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            )
        ));
    }

    public function isValid()
    {
        //$this->setInputFilter(ConfigFilter);
        foreach ($this->configs as $config) {
            $this->addFilter($config);
        }

        return parent::isValid();
    }

    protected function addElement($config)
    {
        $attributes = isset($config->edit['attributes']) ? $config->edit['attributes'] : array();
        $attributes['value'] = $config->value;
        //$attributes['label'] = __($config->title);
        $attributes['description'] = __($config->description);

        $options = array(
                'label'     => __($config->title),
                'module'    => $this->module,
        );
        if (!empty($config->edit['options'])) {
            $options = array_merge($config->edit['options'], $options);
        }
        $element = array(
            'name'          => $config->name,
            'attributes'    => $attributes,
            'options'       => $options,
        );
        if (!empty($config->edit['type'])) {
            $element['type'] = $config->edit['type'];
        }

        $this->add($element);
    }

    protected function addFilter($config)
    {
        $filter = array(
            'name'          => $config->name,
            'required'      => false,
            'filters'    => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        );

        $this->getInputFilter()->add($filter);
    }
}
