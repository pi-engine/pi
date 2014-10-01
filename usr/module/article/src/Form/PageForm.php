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

/**
 * Page form for manipulating form instance
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class PageForm extends BaseForm
{
    /**
     * Fields to render
     *
     * @var array
     */
    protected $fields = array();

    /** @var string Config file identifier */
    protected $configIdentifier = 'form';

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
                $fields = Pi::api('page', $module)->loadFields($configFile);
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
            'id',
        );
        foreach ($hidden as $key) {
            $fields[] = array(
                'name' => $key,
                'type' => 'hidden',
            );
        }

        $fields[] = array(
            'name'       => 'security',
            'type'       => 'csrf',
        );
        $fields[] = array(
            'name'       => 'submit',
            'type'       => 'submit',
        );
        
        foreach ($fields as $field) {
            $this->add($field);
        }
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
}
