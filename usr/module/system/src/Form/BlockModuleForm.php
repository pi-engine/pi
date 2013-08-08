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
use Zend\InputFilter\InputFilter;
use Pi\Db\RowGateway\RowGateway as BlockRow;

/**
 * Block module form
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class BlockModuleForm extends BaseForm
{
    /** @var BlockRow Root block model */
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
     * Get block configs
     *
     * @return string[]
     */
    public function getConfigs()
    {
        $configs = array();
        foreach ($this->root->config as $config) {
            $configs[] = $config->name;
        }
        return $configs;
    }

    /**
     * {@inheritDoc}
     */
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
                'description'   =>
                    __('For block header subline. HTML is allowed.'),
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

    /**
     * Add config fieldset
     *
     * @return void
     */
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
    }

    /**
     * {@inheritDoc}
     */
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

    /**
     * Add filter
     *
     * @param InputFilter $inputFilter
     * @return void
     */
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
