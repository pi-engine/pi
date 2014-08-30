<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Tag\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Zend\Db\Sql\Expression;

/**
 * Tag draft API
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @author Liu Chuang <liuchuangww@gmail.com>
 */
class Draft extends Api
{
    /**
     * Get tags of a draft item or multi-items
     *
     * @param string        $module Module name
     * @param string|array  $item   Item identifier
     * @param string        $type   Item type, default as ''
     *
     * @return string[]
     */
    public function get($module, $item, $type = '')
    {
        $result = array();

        $items  = (array) $item;
        $rowset = Pi::model('draft', $this->module)->select(array(
            'module'    => $module,
            'type'      => $type,
            'item'      => $items,
        ));
        foreach ($rowset as $row) {
            $result[$row['item']][] = $row['term'];
        }
        if (is_scalar($item)) {
            if (isset($result[$item])) {
                $result = $result[$item];
            } else {
                $result = array();
            }
        }

        return $result;
    }

    /**
     * Add tags of a draft item
     *
     * @param string       $module Module name
     * @param string       $item   Item identifier
     * @param string       $type   Item type, default as ''
     * @param array|string $tags   Tags to add
     * @param int          $time   Time adding the tags
     *
     * @return bool
     */
    public function add($module, $item, $type, $tags, $time = 0)
    {
        $type = $type ?: '';
        $time = $time ?: time();
        $tags = $this->canonize($tags);
        if (!$tags) {
            return true;
        }

        $modelLink  = Pi::model('draft', $this->module);

        foreach ($tags as $index => $tag) {
            // Insert data to link table
            $row = $modelLink->createRow(array(
                'term'      => $tag,
                'module'    => $module,
                'type'      => $type,
                'item'      => $item,
                'time'      => $time,
                'order'     => $index
            ));
            $row->save();
        }

        return true;
    }

    /**
     * Update tag list of a draft item
     *
     * @param string       $module Module name
     * @param string       $item   Item identifier
     * @param string       $type   Item type, default as ''
     * @param array|string $tags   Tags to add
     * @param int          $time   Time adding new tags
     *
     * @return bool
     */
    public function update($module, $item, $type, $tags, $time = 0)
    {
        $type       = $type ?: '';
        $where = array(
            'module'    => $module,
            'type'      => $type,
            'item'      => $item,
        );
        Pi::model('draft', $this->module)->delete($where);
        $result = $this->add($module, $item, $type, $tags, $time);

        return $result;
    }

    /**
     * Delete tags of an item
     *
     * @param string $module Module name
     * @param string $item   Item identifier
     * @param string $type   Item type, default as ''
     *
     * @return bool
     */
    public function delete($module, $item, $type = '')
    {
        $type = $type ?: '';
        Pi::model('draft', $this->module)->delete(array(
            'module'    => $module,
            'type'      => $type,
            'item'      => $item,
        ));

        return true;
    }
}
