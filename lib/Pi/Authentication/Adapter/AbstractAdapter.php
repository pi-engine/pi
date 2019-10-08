<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Authentication\Adapter;

use Zend\Authentication\Adapter\AbstractAdapter as ZendAbstractAdapter;

/**
 * Db authentication abstract adapter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractAdapter extends ZendAbstractAdapter implements
    AdapterInterface
{
    /** @var array Options */
    protected $options = [];

    /**
     * Results of database authentication query
     *
     * @var array
     */
    protected $resultRow = [];

    /**
     * {@inheritDoc}
     */
    public function setOptions($options = [])
    {
        foreach ($options as $key => $val) {
            $method = 'set' . str_replace('_', '', $key);
            if (method_exists($this, $method)) {
                $this->$method($val);
                unset($options[$key]);
            }
        }
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function setResultRow($resultRow = [])
    {
        $this->resultRow = (array)$resultRow;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getResultRow($returnColumns = null, $omitColumns = null)
    {
        if (null === $returnColumns
            && isset($this->options['return_columns'])
        ) {
            $returnColumns = (array)$this->options['return_columns'];
        } elseif (null === $omitColumns && isset($this->options['omit_columns'])) {
            $omitColumns = (array)$this->options['omit_columns'];
        }
        $omitColumns = $omitColumns ?: [];

        $return = [];
        $data   = $this->resultRow;
        if ($returnColumns) {
            foreach ((array)$returnColumns as $returnColumn) {
                if (isset($data[$returnColumn])) {
                    $return[$returnColumn] = $data[$returnColumn];
                }
            }
            return $return;
        }

        $omitColumns = (array)$omitColumns;
        foreach ($data as $resultColumn => $resultValue) {
            if (!in_array($resultColumn, $omitColumns)) {
                $return[$resultColumn] = $resultValue;
            }
        }

        return $return;
    }

}
