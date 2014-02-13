<?php
/**
 * Tag module default API class
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
 * @author          Chuang Liu <liuchuang@eefocus.com>
 * @since           3.0
 * @package         Module\Tag
 * @version         $Id$
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
