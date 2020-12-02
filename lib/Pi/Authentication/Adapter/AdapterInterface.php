<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Authentication\Adapter;

use Laminas\Authentication\Adapter\ValidatableAdapterInterface as LaminasAdapterInterface;

/**
 * Pi authentication adapter interface
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
interface AdapterInterface extends LaminasAdapterInterface
{
    /**
     * Set options
     *
     * @param array $options
     * @return void
     */
    public function setOptions($options = []);

    /**
     * Set result data
     *
     * @param array|object $resultRow
     * @return self
     */
    public function setResultRow($resultRow = []);

    /**
     * Returns the result row
     *
     * @param  string|array $returnColumns
     * @param  string|array $omitColumns
     * @return array
     */
    public function getResultRow($returnColumns = null, $omitColumns = null);
}
