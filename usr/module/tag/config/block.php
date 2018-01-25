<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    // Top tag block
    'top'   => [
        'title'       => _a('Top tag list'),
        'description' => _a('Top 10 tag block'),
        'render'      => ['block', 'top'],
        'template'    => 'top',
        'config'      => [
            'item_page' => [
                'title'       => _a('Limit'),
                'description' => _a('Block display item count'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 10,
            ],
        ],
    ],
    // Top tag cloud
    'cloud' => [
        'title'       => _a('Tag Cloud'),
        'description' => _a('Top tag cloud block'),
        'render'      => ['block', 'cloud'],
        'template'    => 'top-cloud',
        'config'      => [
            'item_page'     => [
                'title'       => _a('Limit'),
                'description' => _a('Block display item count'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 20,
            ],
            'color'         => [
                'title'       => _a('Color'),
                'description' => _a('Set tag cloud color'),
                'edit'        => [
                    'type'       => 'select',
                    'attributes' => [
                        'options' => [
                            '_black' => 'Black',
                            '_color' => 'Color',
                        ],
                    ],
                ],
                'filter'      => 'string',
                'value'       => '_black',
            ],
            'max_font_size' => [
                'title'       => _a('Max font size'),
                'description' => _a('Set max font size'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 22,
            ],
            'min_font_size' => [
                'title'       => _a('Min font size'),
                'description' => _a('Set min font size'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 13,
            ],
        ],
    ],
    // New tag block
    'news'  => [
        'title'       => _a('The latest tag'),
        'description' => _a('The latest tag'),
        'render'      => 'block::news',
        'template'    => 'news',
        'config'      => [
            'item_page' => [
                'title'       => _a('Limit'),
                'description' => _a('Block display item count'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 10,
            ],
        ],
    ],
];
