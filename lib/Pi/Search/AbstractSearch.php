<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Search;

/**
 * Abstract class for module search
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractSearch extends AbstractModuleAwareness
{
    /**
     * Search query
     *
     * @param array|string $terms
     * @param int $limit
     * @param int $offset
     *
     * @return ResultSet
     */
    abstract public function query($terms, $limit= 0, $offset = 0);

    /**
     * Build search resultset
     *
     * @param int   $total
     * @param array $data
     *
     * @return ResultSet
     */
    public function buildResult($total, array $data)
    {
        $result = new ResultSet($total, $data);

        return $result;
    }
}
