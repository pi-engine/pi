<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Tag\Block;

use Pi;

class Block
{
    /**
     * Top tag block
     */
    public static function top($options = array(), $module = null)
    {
        $limit = $options['item_page'] ? intval($options['item_page']) : 10;
        $tags = Pi::service('tag')->top($limit);
        array_walk($tags, function (&$tag) {
            $tag['link'] = Pi::service('tag')->render($tag['term']);
        });

        return array(
            'tags' => $tags,
        );
    }

    /**
     * Tag cloud block
     */
    public static function cloud($options = array(), $module = null)
    {
        $fontSizes = array(
            'max' => isset($options['max_font_size']) ? intval($options['max_font_size']) : 22,
            'min' => isset($options['min_font_size']) ? intval($options['min_font_size']) : 13,
        );
        $color = isset($options['color']) ? $options['color'] : '_black';
        $limit = isset($options['item_page']) ? intval($options['item_page']) : 20;
        $data = Pi::service('tag')->top($limit);
        $counts = array('min' => null, 'max' => null);
        $tags = array();
        foreach ($data as $tag) {
            $tags[$tag['term']] = $tag;
            if (null === $counts['min'] || $tag['count'] < $counts['min']) {
                $counts['min'] = $tag['count'];
            }
            if (null === $counts['max'] || $tag['count'] > $counts['max']) {
                $counts['max'] = $tag['count'];
            }
        }

        array_walk($tags, function (&$tag) use ($color, $counts, $fontSizes) {
            $tagColor = '#';
            if ($color == '_color') {
                for ($i = 0; $i < 6; $i++) {
                    $tagColor .= dechex(rand(0,15));
                }
            } else {
                $tagColor .= '000000';
            }
            $tag['color'] = $tagColor;
            $tag['size'] = floor(
                (($fontSizes['max'] - $fontSizes['min']) * $tag['count'])
                / ($counts['max'] - $counts['min'])
            );
            $tag['url'] = Pi::service('tag')->url($tag['term']);
        });
        ksort($tags);

        return array(
            'tags'          => $tags,
        );
    }

    /**
     * Latest tag block
     */
    public static function news($options = array(), $module = null)
    {
        $limit = $options['item_page'] ? intval($options['item_page']) : 10;
        $modelLink = Pi::model('link', 'tag');
        $select = $modelLink->select()
            ->order(array('time DESC'))
            ->group('term')
            ->limit($limit);
        $rowset = $modelLink->selectWith($select);
        $tags = array();
        foreach ($rowset as $row) {
            $tags[] = array(
                'time'  => _date($row['time']),
                'link'   => Pi::service('tag')->render($row['term']),
            );
        }

        return array(
            'tags'  => $tags,
        );
    }
}
