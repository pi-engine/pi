<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Model\User\RowGateway;

use Pi;
use Pi\Db\RowGateway\RowGateway;
use Pi\Filter\FilterChain;

/**
 * User profile abstract row gateway
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractFieldRowGateway extends RowGateway
{
    /**
     * Profile meta data
     * @var array
     */
    protected $meta;

    /**
     * Get field meta list
     *
     * @return array
     */
    abstract protected function getMetaList();

    /**
     * Get meta data of a key or all set
     *
     * @param string|null $key
     * @return array
     */
    protected function getMeta($key = null)
    {
        if (!isset($this->meta)) {
            $this->meta = $this->getMetaList();
        }

        if ($key) {
            $result = isset($this->meta[$key]) ? $this->meta[$key] : null;
        } else {
            $result = $this->meta;
        }

        return $result;
    }

    /**
     * Filter value for display
     *
     * @param string|string[] $col
     * @return mixed|mixed[]
     */
    public function filter($col = null)
    {
        $result = array();
        if (!$col) {
            $cols = array_keys($this->getMeta());
        } else {
            $cols = (array) $col;
        }

        foreach ($cols as $field) {
            $ret = $this->filterField($field);
            if (null !== $ret) {
                $result[$field] = $ret;
            }
        }

        if (is_scalar($col)) {
            $result = isset($result[$col]) ? $result[$col] : null;
        }

        return $result;
    }

    /**
     * Filter a field value
     *
     * @param string $field
     * @return mixed
     */
    protected function filterField($field)
    {
        $value = $this[$field];
        if (null !== $value) {
            $meta = $this->getMeta($field);
            if (isset($meta['filter'])) {
                $value = $this->getFilter($meta['filter'])->filter($value);
            }
        }

        return $value;
    }

    /**
     * Load filter handler
     *
     * @param array $filters
     *
     * @return FilterChain
     */
    protected function getFilter($filters)
    {

        $filterChain = new FilterChain;
        foreach ($filters as $filter) {
            $filterChain->attachByName($filter);
        }

        return $filterChain;
    }
}
