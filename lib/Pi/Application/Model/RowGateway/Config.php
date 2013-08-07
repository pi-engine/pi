<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Model\RowGateway;

use Pi\Db\RowGateway\RowGateway;

/**
 * Config row gateway
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Config extends RowGateway
{
    /**
     * Columns to be encoded
     *
     * @var array
     */
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
        if (!empty($data['filter']) && isset($data['value'])) {
            $data['value'] = $this->encodeValueColumn(
                $data['value'],
                $data['filter']
            );
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
            $data['value'] = $this->decodeValueColumn(
                $data['value'],
                $data['filter']
            );
        }
        return parent::decode($data);
    }

    /**
     * Decode value column
     *
     * @param mixed     $value
     * @param string    $filter
     * @return mixed
     */
    protected function decodeValueColumn($value, $filter)
    {
        $options = null;
        $filterId = null;
        switch ($filter) {
            case 'int':
            case 'number_int':
                $filter = 'number_int';
                break;
            case 'float':
            case 'number_float':
                $filter = 'number_float';
                break;
            case 'array':
            case 'decode':
                $options = array($this, 'decodeValue');
                $filterId = FILTER_CALLBACK;
                break;
            case 'textarea':
            case 'special_chars':
            case 'text':
            case 'string':
                $filter = null;
                break;
            default:
                break;
        }

        return $this->filterValue($value, $filter, $filterId, $options);
    }

    /**
     * Encode value column
     *
     * @param mixed     $value
     * @param string    $filter
     * @return mixed
     */
    protected function encodeValueColumn($value, $filter)
    {
        $options = null;
        $filterId = null;
        switch ($filter) {
            case 'int':
            case 'number_int':
                $filter = 'number_int';
                break;
            case 'float':
            case 'number_float':
                $filter = 'number_float';
                break;
            case 'array':
            case 'encode':
                $filter = array($this, 'encodeValue');
                break;
            case 'textarea':
            case 'special_chars':
            case 'text':
            case 'string':
                $filter = null;
                break;
            default:
                break;
        }

        return $this->filterValue($value, $filter, $filterId, $options);
    }

    /**
     * Filters a value according to filter, filter_id and options
     *
     * @param mixed     $value
     * @param mixed     $filter
     * @param int       $filterId
     * @param mixed     $options
     * @return mixed
     */
    protected function filterValue($value, $filter, $filterId, $options)
    {
        if (!$filter && !$filterId) {
            return $value;
        }
        if (null === $filterId) {
            $filterId = is_string($filter) ? filter_id($filter) : null;
        }
        if ($filterId) {
            if (null === $options) {
                $value = filter_var($value, $filterId);
            } else {
                $value = filter_var($value, $filterId,
                    array('options' => $options));
            }
        } elseif (is_callable($filter)) {
            $value = call_user_func($filter, $value);
        }

        return $value;
    }
}
