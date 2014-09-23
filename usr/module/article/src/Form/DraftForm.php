<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form;

use Pi;
use Pi\Form\Form as BaseForm;
use Zend\InputFilter\InputFilter as DraftInputFilter;
use Zend\Form\FormInterface;

/**
 * Draft form for manipulating form instance
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DraftForm extends BaseForm
{
    /**
     * Fields to render
     *
     * @var array
     */
    protected $fields = array();

    /** @var string Config file identifier */
    protected $configIdentifier = 'draft';

    /** @var string InputFilter class */
    protected $inputFilterClass = '';
    
    /** @var current module name */
    protected $module;

    /**
     * {@inheritDoc}
     *
     * @param array|string $fields
     */
    public function __construct($name, $fields = array())
    {
        if (!$fields || !is_array($fields)) {
            if ($fields && is_string($fields)) {
                $configFile = $fields;
            } else {
                $configFile = $this->configIdentifier;
            }
            if ($configFile) {
                $module = Pi::service('module')->current();
                $fields = Pi::api('form', $module)->loadFields($configFile);
            }
        }
        if (empty($this->module)) {
            $this->module = Pi::service('module')->current();
        }

        $this->fields = $fields;
        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $fields = $this->fields;
        
        $hidden = array(
            'time_update', 'time_submit', 'uid', 'id', 'article', 'jump',
        );
        foreach ($hidden as $key) {
            $fields[] = array(
                'name' => $key,
                'type' => 'hidden',
            );
        }
        //$enableTag = Pi::config('enable_tag', $this->module);
        //if (!empty($enableTag)) {
            $fields[] = array(
                'name'    => 'tag',
                'type'    => 'tag',
                'options' => array(
                    'label'  => __('Tags'),
                ),
            );
        //}
        $fields[] = array(
            'name'       => 'security',
            'type'       => 'csrf',
        );
        
        foreach ($fields as $field) {
            $this->add($field);
        }
        
        // Initiate image element data
        $urls['save_draft'] = Pi::service('url')->assemble('default', array(
            'controller' => 'draft',
            'action'     => 'save-image',
        ));
        $urls['remove_draft'] = Pi::service('url')->assemble('default', array(
            'controller' => 'draft',
            'action'     => 'remove-image',
        ));
        $this->get('image')->setOption('urls', $urls);
    }

    /**
     * Load input filter and assign to form
     *
     * @param array $filters
     *
     * @return $this
     */
    public function loadInputFilter(array $filters = array())
    {
        if (!$filters) {
            $filters = Pi::api('form', $this->module)->loadFilters(
                $this->configIdentifier
            );
        }
        foreach (array('tag') as $key) {
            $filters[] = array(
                'name'     => $key,
                'required' => false,
            );
        }
        $inputFilter = $this->initInputFilter();
        foreach ($filters as $filter) {
            $inputFilter->add($filter);
        }
        $this->setInputFilter($inputFilter);

        return $this;
    }

    /**
     * Instantiate InputFilter
     *
     * @return DraftInputFilter
     */
    protected function initInputFilter()
    {
        if ($this->inputFilterClass) {
            $inputFilter = new $this->inputFilterClass;
        } else {
            $inputFilter = new DraftInputFilter;
        }

        return $inputFilter;
    }

    /**
     * {@inheritDoc}
     *
     * Canonize compound data
     */
    public function setData($data)
    {
        $data = (array) $data;
        $compounds = Pi::registry('field', $this->module)->read('compound');

        $result = array();
        foreach ($data as $key => $value) {
            if (is_array($value) && isset($compounds[$key])) {
                foreach ($value as $fName => $fValue) {
                    $fieldName = Pi::api('form', $this->module)
                        ->assembleCompoundFieldName($key, $fName);
                    $result[$fieldName] = $fValue;
                }
            } else {
                $result[$key] = $value;
            }
        }
        parent::setData($result);

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * Canonize compound data
     */
    public function getData($flags = FormInterface::VALUES_NORMALIZED)
    {
        $data = parent::getData($flags);
        $result = array();
        foreach ($data as $key => $value) {
            $tmp = Pi::api('form', $this->module)->parseCompoundFieldName($key);
            if ($tmp) {
                $result[$tmp[0]][0][$tmp[1]] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
