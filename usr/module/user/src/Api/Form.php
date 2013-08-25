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
use Pi\Db\RowGateway\RowGateway;

/**
 * User profile form manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Form extends AbstractApi
{
    /** @var string Module name */
    protected $module = 'user';

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
        $element['options']['label'] = $data['title'];

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
        $result = array(
            'name'  => $data['name'],
        );
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
        $elements = Pi::registry('profile', $this->module)->read();
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
        $elements = Pi::registry('profile', $this->module)->read();
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
        $elements = Pi::registry('compound', $this->module)->read($compound);
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
        $elements = Pi::registry('compound', $this->module)->read($compound);
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
