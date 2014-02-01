<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Form;

use Pi;
use Pi\Form\Form as BaseForm;
use Zend\InputFilter\InputFilter as UserInputFilter;


/**
 * Abstract User form with support for predefined user profile fields
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AbstractUserForm extends BaseForm
{
    /**
     * Fields to render
     *
     * @var array
     */
    protected $fields = array();

    /** @var string Config filename */
    protected $configFile = '';

    /** @var string InputFilter class */
    protected $inputFilterClass = '';

    /**
     * {@inheritDoc}
     *
     * @param array $fields
     */
    public function __construct($name = 'register', array $fields = array())
    {
        if (!$fields && $this->configFile) {
            $fields = Pi::api('form', 'user')->loadFields($this->configFile);
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
            $filters = Pi::api('form', 'user')->loadFilters($this->configFile);
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
}