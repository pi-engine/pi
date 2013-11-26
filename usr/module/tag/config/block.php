<?php
/**
 * Tag module block specs
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

return array(
    // Top tag block
    'top'   => array(
        'title'         => _a('Top tag list'),
        'description'   => _a('Top 10 tag block'),
        'render'        => array('block', 'top'),
        'template'      => 'top',
        'config'        => array(
            'item_page' => array(
                'title'         => _a('Limit'),
                'description'   => _a('Block display item count'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 10,
            ),
        ),
    ),
    // Top tag cloud
    'cloud' =>array(
        'title'         => _a('Tag Cloud'),
        'description'   => _a('Top tag cloud block'),
        'render'        => array('block', 'cloud'),
        'template'      => 'top-cloud',
        'config'        => array(
            'item_page' => array(
                'title'          => _a('Limit'),
                'description'    => _a('Block display item count'),
                'edit'           => 'text',
                'filter'         => 'number_int',
                'value'          => 20,
            ),
            'color'    => array(
                'title'          => _a('Color'),
                'description'    => _a('Set tag cloud color'),
                'edit'           => array(
                    'type'       => 'select',
                    'attributes' => array(
                        'options'   => array(
                            '_black'    => 'Black',
                            '_color'    => 'Color'
                        ),
                    ),
                ),
                'filter'        => 'string',
                'value'         => '_black'
            ),
            'max_font_size'  => array(
                'title'        => _a('Max font size'),
                'description'  => _a('Set max font size'),
                'edit'         => 'text',
                'filter'       => 'number_int',
                'value'        => 22,
            ),
            'min_font_size' => array(
                'title'        => _a('Min font size'),
                'description'  => _a('Set min font size'),
                'edit'         => 'text',
                'filter'       => 'number_int',
                'value'        => 13
            ),
        ),
    ),
    // New tag block
    'news'  => array(
        'title'         => _a('The latest tag'),
        'description'   => _a('The latest tag'),
        'render'        => 'block::news',
        'template'      => 'news',
        'config'        => array(
            'item_page' => array(
                'title'         => _a('Limit'),
                'description'   => _a('Block display item count'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 10,
            ),
        ),
    ),
);
