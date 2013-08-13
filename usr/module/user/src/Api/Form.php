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
            $element = $elements[$name]['edit']['element'];
            $element['name'] = $name;
            $element['options']['label'] = $edit['title'];
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
            if (isset($elements[$name]['edit']['filters'])) {
                $result['filters'] = $elements[$name]['edit']['filters'];
            }
            if (isset($elements[$name]['edit']['validators'])) {
                $result['validators'] = $elements[$name]['edit']['validators'];
            }
        }

        return $result;
    }
}
