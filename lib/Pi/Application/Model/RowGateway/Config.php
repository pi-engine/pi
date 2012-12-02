<?php
/**
 * Pi Config Model Row
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 * @subpackage      Model
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Model\RowGateway;
use Pi\Db\RowGateway\RowGateway;

class Config extends RowGateway
{
    protected $encodeColumns = array(
        'edit'  => true,
    );

    /**
     * Encode non-scalar columns
     *
     * @param array $data
     * @return array
     */
    protected function encode($data)
    {
        if (!empty($data['filter'])) {
            $data['value'] = $this->encodeValueColumn($data['value'], $data['filter']);
        }

        return parent::encode($data);
    }

    /**
     * Decode non-scalar columns
     *
     * @param array $data
     * @return array
     */
    public function decode($data)
    {
        if (!empty($data['filter'])) {
            $data['value'] = $this->decodeValueColumn($data['value'], $data['filter']);
        }
        return parent::decode($data);
    }

    /**
     * Decode value column
     *
     * @param mixed $value
     * @param string $filter
     * @return mixed
     */
    protected function decodeValueColumn($value, $filter)
    {
        switch($filter) {
            case 'int':
                $filter = 'number_int';
                break;
            case 'array':
                //$filter = 'json_decode';
                break;
            case 'float':
                $filter = 'number_float';
                break;
            case 'textarea':
                $filter = 'special_chars';
                break;
            case 'text':
                $filter = 'string';
                break;
            default:
                break;
        }
        if (!$filter) {
            return $value;
        }
        if ('array' == $filter) {
            $value = $this->decodeValue($value);
            return $value;
        }
        $filter_id = filter_id($filter);
        if ($filter_id) {
            $value = filter_var($value, $filter_id);
        } elseif (function_exists($filter)) {
            $value = filter_var($value, FILTER_CALLBACK, array('options' => $filter));
        }
        return $value;
    }

    /**
     * Encode value column
     *
     * @param mixed $value
     * @param string $filter
     * @return mixed
     */
    protected function encodeValueColumn($value, $filter)
    {
        switch ($filter) {
            case 'array':
                $value = json_encode($value);
                break;
            default:
                break;
        }
        return $value;
    }
}
