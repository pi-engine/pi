<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Api;

use Pi;
use Pi\Application\AbstractApi;
use Module\User\Form\UserForm;

/**
 * User profile form manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Form extends AbstractApi
{
    /**
     * @{inheritDoc}
     */
    protected $module = 'user';

    /**
     * Load form from with fields, supporting custom form
     *
     * @param string $name
     * @param bool $withFilter  To set InputFilter
     *
     * @return UserForm
     */
    public function loadForm($name, $withFilter = false)
    {
        $class = str_replace(' ', '', ucwords(
            str_replace(array('-', '_', '.', '\\', '/'), ' ', $name)
        ));
        $formClass = $class . 'Form';
        $formClassName = 'Custom\User\Form\\' . $formClass;
        if (!class_exists($formClassName)) {
            $formClassName = 'Module\User\Form\\' . $formClass;
            if (!class_exists($formClassName)) {
                $formClassName = 'Module\User\Form\UserForm';
            }
        }

        if ($withFilter) {
            list($elements, $filters) = $this->loadFields($name, $withFilter);
        } else {
            $elements   = $this->loadFields($name, $withFilter);
            $filters    = array();
        }

        $form = new $formClassName($name, $elements);
        if ($withFilter && $form instanceof UserForm) {
            $form->loadInputFilter($filters);
        }

        return $form;
    }

    /**
     * Load form elements from field config, supporting custom configs
     *
     * @param string $name
     * @param bool $withFilter  To return filters
     *
     * @return array
     */
    public function loadFields($name, $withFilter = false)
    {
        $elements   = array();
        $filters    = array();
        $filePath   = sprintf('user/config/form.%s.php', $name);
        $file       = Pi::path('custom/module') . '/' . $filePath;
        if (!file_exists($file)) {
            $file = Pi::path('module') . '/' . $filePath;
        }
        $config     = include $file;
        $meta       = Pi::registry('field', $this->module)->read();
        foreach ($config as $value) {
            if (is_string($value)) {
                if (isset($meta[$value]) &&
                    $meta[$value]['type'] == 'compound'
                ) {
                    $compoundElements = $this->getCompoundElement($value);
                    foreach ($compoundElements as $element) {
                        if ($element) {
                            $elements[] = $element;
                        }
                    }
                    if ($withFilter) {
                        $compoundFilters = $this->getCompoundFilter($value);
                        foreach ($compoundFilters as $filter) {
                            if ($filter) {
                                $filters[] = $filter;
                            }
                        }
                    }
                } else {
                    $element = $this->getElement($value);
                    if ($element) {
                        $elements[] = $element;
                    }
                    if ($withFilter) {
                        $filter = $this->getFilter($value);
                        if ($filter) {
                            $filters[] = $filter;
                        }
                    }
                }
            } else {
                if ($value['element']) {
                    $elements[] = $value['element'];
                }
                if ($withFilter) {
                    if (isset($value['filter']) && $value['filter']) {
                        $filters[] = $value['filter'];
                    }
                }
            }
        }

        if ($withFilter) {
            $result = array(
                'elements'  => $elements,
                'filters'   => $filters,
            );
        } else {
            $result = $elements;
        }

        return $result;
    }

    /**
     * Load form filters from config, supporting custom configs
     *
     * @param string $name
     *
     * @return array
     */
    public function loadFilters($name)
    {
        $filters    = array();
        $filePath   = sprintf('user/config/form.%s.php', $name);
        $file       = Pi::path('custom/module') . '/' . $filePath;
        if (!file_exists($file)) {
            $file = Pi::path('module') . '/' . $filePath;
        }
        $config     = include $file;
        $meta       = Pi::registry('field', $this->module)->read();
        foreach ($config as $value) {
            if (is_string($value)) {
                if (isset($meta[$value]) &&
                    $meta[$value]['type'] == 'compound'
                ) {
                    $compoundFilters = $this->getCompoundFilter($value);
                    foreach ($compoundFilters as $filter) {
                        if ($filter) {
                            $filters[] = $filter;
                        }
                    }
                } else {
                    $filter = $this->getFilter($value);
                    if ($filter) {
                        $filters[] = $filter;
                    }
                }
            } else {
                if (isset($value['filter']) && $value['filter']) {
                    $filters[] = $value['filter'];
                }
            }
        }

        return $filters;
    }

    /**
     * Canonize form element for a field
     *
     * @param array $data
     * @return array
     */
    protected function canonizeElement($data)
    {
        $element = $data['edit']['element'];
        $element['name'] = $data['name'];
        if (isset($data['edit']['options']) &&
            $data['edit']['options']
        ) {
            $element['options'] = $data['edit']['options'];
        } else {
            $element['options'] = array();
        }
        $element['options']['label'] = $data['title'];
        if (isset($data['edit']['attributes'])) {
            $element['attributes'] = $data['edit']['attributes'];
        }

        return $element;
    }

    /**
     * Canonize form element filter for a field
     *
     * @param array $data
     * @return array
     */
    protected function canonizeFilter($data)
    {
        $result = array();
        if (isset($data['edit']['filters']) ||
            isset($data['edit']['validators']) ||
            isset($data['edit']['required'])
        ) {
            $result = array(
                'name'  => $data['name'],
            );
            if (isset($data['edit']['required'])) {
                $result['required'] = $data['edit']['required'];
            }
        }


        if (isset($data['edit']['filters'])) {
            $result['filters'] = $data['edit']['filters'];
        }
        if (isset($data['edit']['validators'])) {
            $result['validators'] = $data['edit']['validators'];
        }

        return $result;
    }

    /**
     * Get form element for field
     *
     * @param string $name
     * @return array
     */
    public function getElement($name)
    {
        $element = array();
        $elements = Pi::registry('field', $this->module)->read();
        if (isset($elements[$name]) && isset($elements[$name]['edit'])) {
            $element = $this->canonizeElement($elements[$name]);
        }

        return $element;
    }

    /**
     * Get form filter for field
     *
     * @param string $name
     * @return array
     */
    public function getFilter($name)
    {
        $result = array(
            'name'  => $name,
        );
        $elements = Pi::registry('field', $this->module)->read();
        if (isset($elements[$name]) && isset($elements[$name]['edit'])) {
            $result = $this->canonizeFilter($elements[$name]);
        }

        return $result;
    }

    /**
     * Get a compound field element if specified, or a compound's all fields
     * if field name is not specified
     *
     * @param string $compound
     * @param string $field
     * @return array
     */
    public function getCompoundElement($compound, $field = '')
    {
        $result = array();
        $elements = Pi::registry('compound_field', $this->module)->read($compound);
        if ($field) {
            if (isset($elements[$field])) {
                $result = $this->canonizeElement($elements[$field]);
            }
        } else {
            foreach ($elements as $key => $element) {
                $result[$key] = $this->canonizeElement($element);
            }
        }

        return $result;
    }

    /**
     * Get a compound field element if specified, or a compound's all fields
     * if field name is not specified
     *
     * @param string $compound
     * @param string $field
     * @return array
     */
    public function getCompoundFilter($compound, $field = '')
    {
        $result = array();
        $elements = Pi::registry('compound_field', $this->module)->read($compound);
        if ($field) {
            if (isset($elements[$field])) {
                $result = $this->canonizeFilter($elements[$field]);
            }
        } else {
            foreach ($elements as $key => $element) {
                $result[$key] = $this->canonizeFilter($element);
            }
        }

        return $result;
    }
}
