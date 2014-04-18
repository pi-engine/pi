<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

use Pi;
use Pi\Form\Form as BaseForm;
use Zend\InputFilter\InputFilter as UserInputFilter;
use Zend\Form\FormInterface;

/**
 * User form with support for predefined user profile fields
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class UserForm extends BaseForm
{
    /**
     * Fields to render
     *
     * @var array
     */
    protected $fields = array();

    /** @var string Config file identifier */
    protected $configIdentifier = '';

    /** @var string InputFilter class */
    protected $inputFilterClass = '';

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
                $fields = Pi::api('form', 'user')->loadFields($configFile);
            }
        }

        $this->fields = $fields;
        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        foreach ($this->fields as $field) {
            $this->add($field);
        }

        $this->add(array(
            'name'       => 'submit',
            'type'       => 'submit',
        ));
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
            $filters = Pi::api('form', 'user')->loadFilters($this->configIdentifier);
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
     * @return UserInputFilter
     */
    protected function initInputFilter()
    {
        if ($this->inputFilterClass) {
            $inputFilter = new $this->inputFilterClass;
        } else {
            $inputFilter = new UserInputFilter;
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
        $compounds = Pi::registry('field', 'user')->read('compound');

        $result = array();
        foreach ($data as $key => $value) {
            if (is_array($value) && isset($compounds[$key])) {
                foreach ($value as $fName => $fValue) {
                    $fieldName = Pi::api('form', 'user')->assembleCompoundFieldName($key, $fName);
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
            $tmp = Pi::api('form', 'user')->parseCompoundFieldName($key);
            if ($tmp) {
                $result[$tmp[0]][0][$tmp[1]] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}