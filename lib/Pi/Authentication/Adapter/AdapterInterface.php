<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Authentication\Adapter;

use Pi;
use Zend\Authentication\Adapter\ValidatableAdapterInterface
    as ZendAdapterInterface;

/**
 * Pi authentication adapter interface
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
interface AdapterInterface extends ZendAdapterInterface
{
    /**
     * Set options
     *
     * @param array $options
     * @return void
     */
    public function setOptions($options = array());

    /**
     * Set result data
     *
     * @param array|object $resultRow
     * @return self
     */
    public function setResultRow($resultRow = array());

    /**
     * Returns the result row
     *
     * @param  string|array $returnColumns
     * @param  string|array $omitColumns
     * @return array
     */
    public function getResultRow($returnColumns = null, $omitColumns = null);
}
