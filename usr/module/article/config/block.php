<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Block config
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
return [
    'all-categories'           => [
        'title'       => _a('All Categories'),
        'description' => _a('Listing the parent category and its children'),
        'render'      => 'block::allCategories',
        'template'    => 'all-categories',
        'config'      => [
            'top-category'     => [
                'title'       => _t('Top-Category Count'),
                'description' => _t('The max top category count want to display'),
                'edit'        => 'text',
                'filter'      => 'number int',
                'value'       => 6,
            ],
            'sub-category'     => [
                'title'       => _t('Sub-Category Count'),
                'description' => _t('The max child category count want to display'),
                'edit'        => 'text',
                'filter'      => 'number int',
                'value'       => 2,
            ],
            'default-category' => [
                'title'       => _t('Default Category Name'),
                'description' => _t('Default category name when there is no category acquired'),
                'edit'        => 'text',
                'filter'      => 'string',
                'value'       => _a('None'),
            ],
            'target'           => [
                'title'       => _t('Target'),
                'description' => _t('Open url in which window'),
                'edit'        => [
                    'type'       => 'select',
                    'attributes' => [
                        'options' => [
                            '_blank'  => 'Blank',
                            '_parent' => 'Parent',
                            '_self'   => 'Self',
                            '_top'    => 'Top',
                        ],
                    ],
                ],
                'filter'      => 'string',
                'value'       => '_blank',
            ],
        ],
    ],
    'category-list'            => [
        'title'       => _t('Category List'),
        'description' => _t('Listing all category'),
        'render'      => 'block::categoryList',
        'template'    => 'category-list',
        'config'      => [
            'list-count' => [
                'title'       => _a('List Count'),
                'description' => _a('The max categories to display'),
                'filter'      => 'int',
                'value'       => 18,
            ],
            'target'     => [
                'title'       => _a('Target'),
                'description' => _a('Open url in which window'),
                'edit'        => [
                    'type'       => 'select',
                    'attributes' => [
                        'options' => [
                            '_blank'  => 'Blank',
                            '_parent' => 'Parent',
                            '_self'   => 'Self',
                            '_top'    => 'Top',
                        ],
                    ],
                ],
                'filter'      => 'string',
                'value'       => '_blank',
            ],
        ],
    ],
    'hot-categories'           => [
        'title'       => _t('Hot Categories'),
        'description' => _t('Listing hot categories according to their articles'),
        'render'      => 'block::hotCategories',
        'template'    => 'hot-categories',
        'config'      => [
            'list-count' => [
                'title'       => _a('List Count'),
                'description' => _a('The max categories to display'),
                'filter'      => 'int',
                'value'       => 18,
            ],
            'day-range'  => [
                'title'       => _a('Day Range'),
                'description' => _a('Day range'),
                'filter'      => 'int',
                'value'       => 7,
            ],
            'target'     => [
                'title'       => _a('Target'),
                'description' => _a('Open url in which window'),
                'edit'        => [
                    'type'       => 'select',
                    'attributes' => [
                        'options' => [
                            '_blank'  => 'Blank',
                            '_parent' => 'Parent',
                            '_self'   => 'Self',
                            '_top'    => 'Top',
                        ],
                    ],
                ],
                'filter'      => 'string',
                'value'       => '_blank',
            ],
        ],
    ],
    'newest-published-article' => [
        'title'       => _t('Newest Published Articles'),
        'description' => _t('Listing the newest published articles of topic or non-topic'),
        'render'      => 'block::newestPublishedArticles',
        'template'    => 'newest-published-articles',
        'config'      => [
            'list-count'         => [
                'title'       => _a('List Count'),
                'description' => _a('The max articles to display'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 10,
            ],
            'category'           => [
                'title'       => _a('Category'),
                'description' => _a('Which category article want to list'),
                'edit'        => [
                    'type' => 'Module\Article\Form\Element\CategoryWithRoot',
                ],
                'filter'      => 'string',
                'value'       => 0,
            ],
            'is-topic'           => [
                'title'       => _a('Is Topic'),
                'description' => _a('Whether to list topic articles'),
                'edit'        => [
                    'type'       => 'checkbox',
                    'attributes' => [
                        'value' => 0,
                    ],
                ],
                'filter'      => 'int',
            ],
            'topic'              => [
                'title'       => _a('Topic'),
                'description' => _a('Which topic article want to list'),
                'edit'        => [
                    'type' => 'Module\Article\Form\Element\Topic',
                ],
                'filter'      => 'string',
                'value'       => 0,
            ],
            'column-number'      => [
                'title'       => _a('List Column Number'),
                'description' => _a('Whether to display only one column or two columns'),
                'edit'        => [
                    'type'       => 'radio',
                    'attributes' => [
                        'options' => [
                            'single' => _a('Single column'),
                            'double' => _a('Double columns'),
                        ],
                    ],
                ],
                'filter'      => 'string',
                'value'       => 'single',
            ],
            'element'            => [
                'title'       => _a('Element to Display'),
                'description' => _a('The article element to display'),
                'edit'        => [
                    'type'       => 'multi_checkbox',
                    'attributes' => [
                        'options' => [
                            'time'    => _a('Time publish'),
                            'summary' => _a('Summary'),
                            'feature' => _a('Feature image'),
                        ],
                    ],
                ],
                'filter'      => 'array',
                'value'       => 'basic',
            ],
            'target'             => [
                'title'       => _a('Target'),
                'description' => _a('Open url in which window'),
                'edit'        => [
                    'type'       => 'select',
                    'attributes' => [
                        'options' => [
                            '_blank'  => 'Blank',
                            '_parent' => 'Parent',
                            '_self'   => 'Self',
                            '_top'    => 'Top',
                        ],
                    ],
                ],
                'filter'      => 'string',
                'value'       => '_blank',
            ],
            'max_subject_length' => [
                'title'       => _a('Subject length'),
                'description' => _a('Maximum length of subject'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 80,
            ],
            'max_summary_length' => [
                'title'       => _a('Summary length'),
                'description' => _a('Maximum length of summary'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 255,
            ],
            'description_rows'   => [
                'title'       => _a('Description rows'),
                'description' => _a('Maximum row count of description'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 2,
            ],
        ],
    ],
    'recommended-slideshow'    => [
        'title'        => _a('Recommended Articles With Slideshow'),
        'title_hidden' => 1,
        'description'  => _a('Listing a slideshow and recommended articles'),
        'render'       => 'block::recommendedSlideshow',
        'template'     => 'recommended-slideshow',
        'config'       => [
            'articles'           => [
                'title'       => _a('Article ID'),
                'description' => _a('Articles want to list'),
                'edit'        => 'text',
                'filter'      => 'string',
                'value'       => 0,
            ],
            'element'            => [
                'title'       => _a('Element to Display'),
                'description' => _a('The article element to display'),
                'edit'        => [
                    'type'       => 'multi_checkbox',
                    'attributes' => [
                        'options' => [
                            'time'    => _a('Time publish'),
                            'summary' => _a('Summary'),
                            'feature' => _a('Feature image'),
                        ],
                    ],
                ],
                'filter'      => 'array',
                'value'       => 'basic',
            ],
            'images'             => [
                'title'       => _a('Image ID'),
                'description' => _a('Images to display'),
                'edit'        => 'text',
                'filter'      => 'string',
                'value'       => 'image/default-recommended.png',
            ],
            'image-link'         => [
                'title'       => _a('Image Link'),
                'description' => _a('URL to redirect when click image'),
                'edit'        => 'textarea',
                'filter'      => 'string',
                'value'       => '',
            ],
            'image-width'        => [
                'title'       => _a('Slide image width'),
                'description' => _a('Maximum width of slide image'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 320,
            ],
            'image-height'       => [
                'title'       => _a('Slide image height'),
                'description' => _a('Maximum height of slide image'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 240,
            ],
            'target'             => [
                'title'       => _a('Target'),
                'description' => _a('Open url in which window'),
                'edit'        => [
                    'type'       => 'select',
                    'attributes' => [
                        'options' => [
                            '_blank'  => 'Blank',
                            '_parent' => 'Parent',
                            '_self'   => 'Self',
                            '_top'    => 'Top',
                        ],
                    ],
                ],
                'filter'      => 'string',
                'value'       => '_blank',
            ],
            'max_subject_length' => [
                'title'       => _a('Subject length'),
                'description' => _a('Maximum length of subject'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 80,
            ],
            'max_summary_length' => [
                'title'       => _a('Summary length'),
                'description' => _a('Maximum length of summary'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 255,
            ],
            'description_rows'   => [
                'title'       => _a('Description rows'),
                'description' => _a('Maximum row count of description'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 2,
            ],
        ],
    ],
    'custom-article-list'      => [
        'title'       => _a('Custom Article List'),
        'description' => _a('Listing custom articles'),
        'render'      => 'block::customArticleList',
        'template'    => 'custom-article-list',
        'config'      => [
            'articles'           => [
                'title'       => _a('Article ID'),
                'description' => _a('Articles want to list'),
                'edit'        => 'text',
                'filter'      => 'string',
                'value'       => 0,
            ],
            'column-number'      => [
                'title'       => _a('List Column Number'),
                'description' => _a('Whether to display only one column or two columns'),
                'edit'        => [
                    'type'       => 'radio',
                    'attributes' => [
                        'options' => [
                            'single' => _a('Single column'),
                            'double' => _a('Double columns'),
                        ],
                    ],
                ],
                'filter'      => 'string',
                'value'       => 'single',
            ],
            'element'            => [
                'title'       => _a('Element to Display'),
                'description' => _a('The article element to display'),
                'edit'        => [
                    'type'       => 'multi_checkbox',
                    'attributes' => [
                        'options' => [
                            'time'    => _a('Time publish'),
                            'summary' => _a('Summary'),
                            'feature' => _a('Feature image'),
                        ],
                    ],
                ],
                'filter'      => 'array',
                'value'       => 'basic',
            ],
            'target'             => [
                'title'       => _a('Target'),
                'description' => _a('Open url in which window'),
                'edit'        => [
                    'type'       => 'select',
                    'attributes' => [
                        'options' => [
                            '_blank'  => 'Blank',
                            '_parent' => 'Parent',
                            '_self'   => 'Self',
                            '_top'    => 'Top',
                        ],
                    ],
                ],
                'filter'      => 'string',
                'value'       => '_blank',
            ],
            'max_subject_length' => [
                'title'       => _a('Subject length'),
                'description' => _a('Maximum length of subject'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 80,
            ],
            'max_summary_length' => [
                'title'       => _a('Summary length'),
                'description' => _a('Maximum length of summary'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 255,
            ],
            'description_rows'   => [
                'title'       => _a('Description rows'),
                'description' => _a('Maximum row count of description'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 2,
            ],
        ],
    ],
    'submitter-statistics'     => [
        'title'       => _a('Submitter Statistics'),
        'description' => _a('Listing the total article count of submitters'),
        'render'      => 'block::submitterStatistics',
        'template'    => 'submitter-statistics',
        'config'      => [
            'list-count' => [
                'title'       => _a('List Count'),
                'description' => _a('The max articles to display'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 10,
            ],
        ],
    ],
    'newest-topic'             => [
        'title'       => _a('Newest Topic'),
        'description' => _a('Listing the newest topic'),
        'render'      => 'block::newestTopic',
        'template'    => 'newest-topic',
        'config'      => [
            'list-count'             => [
                'title'       => _a('List Count'),
                'description' => _a('The max articles to display'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 10,
            ],
            'target'                 => [
                'title'       => _a('Target'),
                'description' => _a('Open url in which window'),
                'edit'        => [
                    'type'       => 'select',
                    'attributes' => [
                        'options' => [
                            '_blank'  => 'Blank',
                            '_parent' => 'Parent',
                            '_self'   => 'Self',
                            '_top'    => 'Top',
                        ],
                    ],
                ],
                'filter'      => 'string',
                'value'       => '_blank',
            ],
            'max_title_length'       => [
                'title'       => _a('Title length'),
                'description' => _a('Maximum length of title'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 80,
            ],
            'max_description_length' => [
                'title'       => _a('Description length'),
                'description' => _a('Maximum length of description'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 80,
            ],
            'description_rows'       => [
                'title'       => _a('Description rows'),
                'description' => _a('Maximum row count of description'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 2,
            ],
        ],
    ],
    'hot-article'              => [
        'title'       => _a('Hot Articles'),
        'description' => _a('Listing the hotest articles'),
        'render'      => 'block::hotArticles',
        'template'    => 'hot-article',
        'config'      => [
            'list-count'         => [
                'title'       => _a('List Count'),
                'description' => _a('The max articles to display'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 10,
            ],
            'is-topic'           => [
                'title'       => _a('Is Topic'),
                'description' => _a('Whether to list topic articles'),
                'edit'        => [
                    'type'       => 'checkbox',
                    'attributes' => [
                        'value' => 0,
                    ],
                ],
                'filter'      => 'int',
            ],
            'day-range'          => [
                'title'       => _a('Day Range'),
                'description' => _a('Day range'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 7,
            ],
            'column-number'      => [
                'title'       => _a('List Column Number'),
                'description' => _a('Whether to display only one column or two columns'),
                'edit'        => [
                    'type'       => 'radio',
                    'attributes' => [
                        'options' => [
                            'single' => _a('Single column'),
                            'double' => _a('Double columns'),
                        ],
                    ],
                ],
                'filter'      => 'string',
                'value'       => 'single',
            ],
            'element'            => [
                'title'       => _a('Element to Display'),
                'description' => _a('The article element to display'),
                'edit'        => [
                    'type'       => 'multi_checkbox',
                    'attributes' => [
                        'options' => [
                            'time'    => _a('Time publish'),
                            'summary' => _a('Summary'),
                            'feature' => _a('Feature image'),
                        ],
                    ],
                ],
                'filter'      => 'array',
                'value'       => 'basic',
            ],
            'target'             => [
                'title'       => _a('Target'),
                'description' => _a('Open url in which window'),
                'edit'        => [
                    'type'       => 'select',
                    'attributes' => [
                        'options' => [
                            '_blank'  => 'Blank',
                            '_parent' => 'Parent',
                            '_self'   => 'Self',
                            '_top'    => 'Top',
                        ],
                    ],
                ],
                'filter'      => 'string',
                'value'       => '_blank',
            ],
            'max_subject_length' => [
                'title'       => _a('Subject length'),
                'description' => _a('Maximum length of subject'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 80,
            ],
            'max_summary_length' => [
                'title'       => _a('Summary length'),
                'description' => _a('Maximum length of summary'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 255,
            ],
            'description_rows'   => [
                'title'       => _a('Description rows'),
                'description' => _a('Maximum row count of description'),
                'edit'        => 'text',
                'filter'      => 'int',
                'value'       => 2,
            ],
        ],
    ],
    'simple-search'            => [
        'title'        => _a('Simple Search'),
        'title_hidden' => 1,
        'description'  => _a('Search form for searching articles by article title'),
        'render'       => 'block::simpleSearch',
        'template'     => 'simple-search',
        'class'        => 'block-noborder',
    ],
    'rss'                      => [
        'title'        => _a('RSS Link'),
        'title_hidden' => 1,
        'description'  => _a('Click to subscribe article'),
        'render'       => 'block::rss',
        'template'     => 'rss',
        'class'        => 'block-noborder',
        'config'       => [
            'target'      => [
                'title'       => _a('Target'),
                'description' => _a('Open url in which window'),
                'edit'        => [
                    'type'       => 'select',
                    'attributes' => [
                        'options' => [
                            '_blank'  => 'Blank',
                            '_parent' => 'Parent',
                            '_self'   => 'Self',
                            '_top'    => 'Top',
                        ],
                    ],
                ],
                'filter'      => 'string',
                'value'       => '_blank',
            ],
            'description' => [
                'title'       => _a('Description'),
                'description' => _a('Description after RSS logo'),
                'edit'        => 'textarea',
                'filter'      => 'string',
                'value'       => _t('Subscribe content of this website'),
            ],
        ],
    ],
];
