<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Tag\Block;

use Pi;

class Block
{
    /**
     * Top tag block
     */
    public static function top($options = [], $module = null)
    {
        $limit = $options['item_page'] ? intval($options['item_page']) : 10;
        $tags  = Pi::service('tag')->top($limit);
        array_walk($tags, function (&$tag) {
            $tag['link'] = Pi::service('tag')->render($tag['term']);
        });

        return [
            'tags' => $tags,
        ];
    }

    /**
     * Tag cloud block
     */
    public static function cloud($options = [], $module = null)
    {
        $fontSizes = [
            'max' => isset($options['max_font_size']) ? intval($options['max_font_size']) : 22,
            'min' => isset($options['min_font_size']) ? intval($options['min_font_size']) : 13,
        ];
        $color     = isset($options['color']) ? $options['color'] : '_black';
        $limit     = isset($options['item_page']) ? intval($options['item_page']) : 20;
        $data      = Pi::service('tag')->top($limit);
        $counts    = ['min' => null, 'max' => null];
        $tags      = [];
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
                    $tagColor .= dechex(rand(0, 15));
                }
            } else {
                $tagColor .= '000000';
            }
            $tag['color'] = $tagColor;
            $tag['size']  = floor(
                (($fontSizes['max'] - $fontSizes['min']) * $tag['count'])
                / ($counts['max'] - $counts['min'])
            );
            $tag['url']   = Pi::service('tag')->url($tag['term']);
        });
        ksort($tags);

        return [
            'tags' => $tags,
        ];
    }

    /**
     * Latest tag block
     */
    public static function news($options = [], $module = null)
    {
        $limit     = $options['item_page'] ? intval($options['item_page']) : 10;
        $modelLink = Pi::model('link', 'tag');
        $select    = $modelLink->select()
            ->order(['time DESC'])
            ->group('term')
            ->limit($limit);
        $rowset    = $modelLink->selectWith($select);
        $tags      = [];
        foreach ($rowset as $row) {
            $tags[] = [
                'time' => _date($row['time']),
                'link' => Pi::service('tag')->render($row['term']),
            ];
        }

        return [
            'tags' => $tags,
        ];
    }
}
