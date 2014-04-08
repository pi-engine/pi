<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Pi;
use Pi\Form\Form as BaseForm;
use Zend\InputFilter\InputFilter;

/**
 * Config form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ConfigForm extends BaseForm
{
    /** @var string Module name */
    protected $module;

    /** @var string Form name */
    protected $name = 'config';

    /** @var array Configs */
    protected $configs;

    /**
     * Constructor
     *
     * @param array $configs
     * @param string $module
     */
    public function __construct($configs, $module)
    {
        $this->module   = $module;
        $this->configs  = $configs;
        parent::__construct($this->name);
    }

    /**
     * {@inheritDoc}
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

    /**
     * {@inheritDoc}
     */
    public function isValid()
    {
        foreach ($this->configs as $config) {
            $this->addFilter($config);
        }

        return parent::isValid();
    }

    /**
     * Add config element
     *
     * @param $config
     */
    protected function addElement($config)
    {
        $attributes = isset($config->edit['attributes'])
            ? $config->edit['attributes'] : array();
        $attributes['value'] = $config->value;
        $attributes['description'] = __($config->description);

        $options = array(
            'label'     => __($config->title),
            'module'    => $this->module,
        );
        if (!empty($config->edit['options'])) {
            $options = array_merge($config->edit['options'], $options);
        }

        $valueOptions = array();
        if (isset($options['options'])) {
            $valueOptions = $options['options'];
            unset($options['options']);
        }
        if (isset($options['value_options'])) {
            $valueOptions = $options['value_options'];
            unset($options['value_options']);
        }
        if ($valueOptions) {
            array_walk($valueOptions, function (&$opt) {
                $opt = __($opt);
            });
            $options['value_options'] = $valueOptions;
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

    /**
     * Add input filter
     *
     * @param Pi\Db\RowGateway\RowGateway $config
     */
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
