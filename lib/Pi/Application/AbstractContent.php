<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application;

/**
 * Abstract class for content API
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractContent extends AbstractApi
{
    /** @var array Columns to fetch: table column => meta key */
    protected $meta = array(
        'id'        => 'id',
        'title'     => 'title',
        'content'   => 'content',
        'time'      => 'time',
        'uid'       => 'uid',
    );

    /**
     * Get list of item(s)
     *
     * - Meta of an item:
     *   - title
     *   - content
     *   - link
     *   - time
     *   - uid
     *
     * @param string[]      $variables
     * @param array         $conditions
     * @param int           $limit
     * @param int           $offset
     * @param string|array  $order
     *
     * @return array
     */
    abstract public function getList(
        array $variables,
        array $conditions,
        $limit  = 0,
        $offset = 0,
        $order  = array()
    );

    /**
     * Canonize variables against meta
     *
     * @param array $variables
     *
     * @return string[]
     */
    protected function canonizeVariables(array $variables)
    {
        $meta       = array_flip($this->meta);
        $columns    = array();
        foreach ($variables as $var) {
            if (isset($meta[$var])) {
                $columns[] = $meta[$var];
            }
        }

        return $columns;
    }

    /**
     * Canonize conditions against meta
     *
     * @param array $conditions
     *
     * @return array
     */
    protected function canonizeConditions(array $conditions)
    {
        $meta   = array_flip($this->meta);
        $result = array();
        foreach ($conditions as $var => $condition) {
            if (isset($meta[$var])) {
                $result[$meta[$var]] = $condition;
            }
        }

        return $result;
    }
}
