<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\Element;

use Zend\Form\Element\Text;

class LoginField extends Text
{
    /** {@inheritDoc} */
    protected $attributes = array(
        'type'  => 'LoginField',
    );

    /** @var string[] Allowed fields for authentication */
    protected $fields = array();

    /** @var  string Selected field */
    protected $field;

    /**
     * Get allowed fields for authentication
     *
     * @return string[]
     */
    public function getFields()
    {
        $fieldList = array(
            'identity'  => __('Username'),
            'email'     => __('Email'),
        );
        if (!$this->fields) {
            $fields = $this->getOption('fields')
                ?: array('identity');
            foreach ((array) $fields as $field) {
                if (isset($fieldList[$field])) {
                    $this->fields[$field] = $fieldList[$field];
                }
            }
        }

        return $this->fields;
    }

    /**
     * Get selected field
     *
     * @return string
     */
    public function getField()
    {
        $fields = $this->getFields();
        if (!$this->field || !isset($fields[$this->field])) {
            $this->field = current(array_keys($fields));
        }

        return $this->field;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        if (null === $this->label) {
            $fields = $this->getFields();
            if (1 == count($fields)) {
                $field = current(array_keys($fields));
                switch ($field) {
                    case 'identity':
                        $this->label = __('Username');
                        break;
                    case 'email':
                        $this->label = __('Email');
                        break;
                    default:
                        $this->label = __('Identity');
                        break;
                }
            } else {
                $this->label = __('Identity');
            }
        }

        return parent::getLabel();
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($value = '')
    {
        if (count($this->getFields()) > 1) {
            if (is_string($value)) {
                $value = explode(' ', trim($value), 2);
                $value = array_map('trim', $value);
            }
            parent::setValue(array_shift($value));
            if ($value) {
                $this->field = current($value);
            }

        } else {
            if (is_array($value)) {
                $value = array_shift($value);
            }
            parent::setValue($value);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getValue()
    {
        $value = parent::getValue();
        if (count($this->getFields()) > 1) {
            $value = explode(' ', trim($value), 2);
            if (1 == count($value)) {
                $value[] = $this->getField();
            }
        }

        return $value;
    }

}
