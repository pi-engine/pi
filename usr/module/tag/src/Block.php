<?php
/**
 * Tag module block class
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

namespace Module\Tag;

use Pi;

class Block
{
    /**
     * Top tag block
     */
    public static function top($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        $offset = 0;
        $limit = $options['item_page'] ? intval($options['item_page']) : 10;

        $modelTag = Pi::model('tag', $module);
        $select = $modelTag->select()->where(array())
                                  ->order(array('count DESC'))
                                  ->offset($offset)
                                  ->limit($limit);
        $result = $modelTag->selectWith($select)->toArray();
        foreach ($result as &$row) {
            $row['url'] = Pi::engine()->application()->getRouter()->assemble(
                array(
                    'module'        => $module,
                    'controller'    => 'index',
                    'action'        => 'detail',
                    'id'            => $row['id']
                ),
                array(
                    'name'          => 'default',
                )
            );
        }

        return array(
            'data' => $result,
        );
    }

    /**
     * Tag cloud block
     */
    public static function cloud($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        // Set font size
        $maxFontSize = isset($options['max_font_size']) ? intval($options['max_font_size']) : 22;
        $minFontSize = isset($options['min_font_size']) ? intval($options['min_font_size']) : 13;
        // Set color
        $color = isset($options['color']) ? $options['color'] : '_black';
        $offset = 0;
        $limit = isset($options['item_page']) ? intval($options['item_page']) : 20;
        $model = Pi::model('tag', $module);
        $select = $model->select()->where(array())
                                  ->order(array('count DESC'))
                                  ->offset($offset)
                                  ->limit($limit);
        $result = $model->selectWith($select)->toArray();
        // Generation tag link url.
        foreach ($result as $row) {
            // Set tag color
            $tagColor[$row['id']] = '#';
            if ($color == '_color') {
                for ($a = 0; $a < 6; $a++) {
                    $tagColor[$row['id']] .= dechex(rand(0,15));
                }
            } else {
                $tagColor[$row['id']] .= '000000';
            }

            // Set tag url
            $url[$row['id']] = Pi::engine()->application()->getRouter()->assemble(
                array(
                    'module'        => $module,
                    'controller'    => 'index',
                    'action'        => 'detail',
                    'id'            => $row['id']
                ),
                array(
                    'name'          => 'default',
                )
            );
        }

        foreach ($result as $row) {
            $tagCloud[$row['term']] = $row['count'];
        }

        ksort($tagCloud);

        return array(
            'data'          => $tagCloud,
            'url'           => $url,
            'maxFontSize'   => $maxFontSize,
            'minFontSize'   => $minFontSize,
            'tagColor'      => $tagColor,
        );
    }

    /**
     * Latest tag block
     */
    public static function news($options = array(), $module = null)
    {
        if (!$module) {
            return false;
        }
        $offset = 0;
        $limit = $options['item_page'] ? intval($options['item_page']) : 10;
        $modelLink = Pi::model('link', $module);
        $select = $modelLink->select()->where(array())
                                  ->order(array('time DESC'))
                                  ->group('tag')
                                  ->offset($offset)
                                  ->limit($limit);
        $rowset = $modelLink->selectWith($select)->toArray();

        $modelTag = Pi::model('tag', $module);
        foreach ($rowset as $row) {
            $select = $modelTag->select()->where(array('id' => $row['tag']));
            $data[] = $modelTag->selectWith($select)->current()->toArray();
            $time[$row['tag']] = $row['time'];
        }
        foreach ($data as &$row) {
            $row['url'] = Pi::engine()->application()->getRouter()->assemble(
                array(
                    'module'        => $module,
                    'controller'    => 'index',
                    'action'        => 'detail',
                    'id'            => $row['id']
                ),
                array(
                    'name'          => 'default',
                )
            );
        }

        return array(
            'data'  => $data,
            'time'  => $time,
        );
    }
}
