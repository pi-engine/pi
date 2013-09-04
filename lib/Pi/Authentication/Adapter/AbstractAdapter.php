<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
    protected $options = array();

    /**
     * Results of database authentication query
     *
     * @var array
     */
    protected $resultRow = array();

    /**
     * {@inheritDoc}
     */
    public function setOptions($options = array())
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
    public function setResultRow($resultRow = array())
    {
        $this->resultRow = (array) $resultRow;

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
            $returnColumns = $this->options['return_columns'];
        }
        if (null === $omitColumns && isset($this->options['omit_columns'])) {
            $omitColumns = $this->options['omit_columns'];
        }
        $omitColumns = $omitColumns ?: array();

        $return = array();
        $data = $this->resultRow;
        if (null !== $returnColumns) {
            foreach ((array) $returnColumns as $returnColumn) {
                if (isset($data[$returnColumn])) {
                    $return[$returnColumn] = $data[$returnColumn];
                }
            }
            return $return;
        }

        $omitColumns = (array) $omitColumns;
        foreach ($data as $resultColumn => $resultValue) {
            if (!in_array($resultColumn, $omitColumns)) {
                $return[$resultColumn] = $resultValue;
            }
        }

        return $return;
    }

}
