<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
                'filter'        => 'int',
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
                'filter'         => 'int',
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
                'filter'       => 'int',
                'value'        => 22,
            ),
            'min_font_size' => array(
                'title'        => _a('Min font size'),
                'description'  => _a('Set min font size'),
                'edit'         => 'text',
                'filter'       => 'int',
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
                'filter'        => 'int',
                'value'         => 10,
            ),
        ),
    ),
);
