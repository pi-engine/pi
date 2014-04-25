<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Block config
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    'all-categories'           => array(
        'title'       => _a('All Categories'),
        'description' => _a('Listing the parent category and its children'),
        'render'      => 'block::allCategories',
        'template'    => 'all-categories',
        'config'      => array(
            'top-category'     => array(
                'title'        => _t('Top-Category Count'),
                'description'  => _t('The max top category count want to display'),
                'edit'         => 'text',
                'filter'       => 'number int',
                'value'        => 6,
            ),
            'sub-category'     => array(
                'title'        => _t('Sub-Category Count'),
                'description'  => _t('The max child category count want to display'),
                'edit'         => 'text',
                'filter'       => 'number int',
                'value'        => 2,
            ),
            'default-category' => array(
                'title'        => _t('Default Category Name'),
                'description'  => _t('Default category name when there is no category acquired'),
                'edit'         => 'text',
                'filter'       => 'string',
                'value'        => _a('None'),
            ),
            'target'           => array(
                'title'        => _t('Target'),
                'description'  => _t('Open url in which window'),
                'edit'         => array(
                    'type'        => 'select',
                    'attributes'  => array(
                        'options'    => array(
                            '_blank'    => 'Blank',
                            '_parent'   => 'Parent',
                            '_self'     => 'Self',
                            '_top'      => 'Top',
                        ),
                    ),
                ),
                'filter'        => 'string',
                'value'         => '_blank',
            ),
        ),
    ),
    'category-list'            => array(
        'title'       => _t('Category List'),
        'description' => _t('Listing all category'),
        'render'      => 'block::categoryList',
        'template'    => 'category-list',
        'config'      => array(
            'list-count'       => array(
                'title'        => _a('List Count'),
                'description'  => _a('The max categories to display'),
                'filter'       => 'int',
                'value'        => 18,
            ),
            'target'           => array(
                'title'        => _a('Target'),
                'description'  => _a('Open url in which window'),
                'edit'         => array(
                    'type'        => 'select',
                    'attributes'  => array(
                        'options'    => array(
                            '_blank'    => 'Blank',
                            '_parent'   => 'Parent',
                            '_self'     => 'Self',
                            '_top'      => 'Top',
                        ),
                    ),
                ),
                'filter'        => 'string',
                'value'         => '_blank',
            ),
        ),
    ),
    'hot-categories'           => array(
        'title'       => _t('Hot Categories'),
        'description' => _t('Listing hot categories according to their articles'),
        'render'      => 'block::hotCategories',
        'template'    => 'hot-categories',
        'config'      => array(
            'list-count'       => array(
                'title'        => _a('List Count'),
                'description'  => _a('The max categories to display'),
                'filter'       => 'int',
                'value'        => 18,
            ),
            'day-range'        => array(
                'title'        => _a('Day Range'),
                'description'  => _a('Day range'),
                'filter'       => 'int',
                'value'        => 7,
            ),
            'target'           => array(
                'title'        => _a('Target'),
                'description'  => _a('Open url in which window'),
                'edit'         => array(
                    'type'        => 'select',
                    'attributes'  => array(
                        'options'    => array(
                            '_blank'    => 'Blank',
                            '_parent'   => 'Parent',
                            '_self'     => 'Self',
                            '_top'      => 'Top',
                        ),
                    ),
                ),
                'filter'        => 'string',
                'value'         => '_blank',
            ),
        ),
    ),
    'newest-published-article' => array(
        'title'       => _t('Newest Published Articles'),
        'description' => _t('Listing the newest published articles of topic or non-topic'),
        'render'      => 'block::newestPublishedArticles',
        'template'    => 'newest-published-articles',
        'config'      => array(
            'list-count'       => array(
                'title'        => _a('List Count'),
                'description'  => _a('The max articles to display'),
                'edit'         => 'text',
                'filter'       => 'int',
                'value'        => 10,
            ),
            'category'         => array(
                'title'        => _a('Category'),
                'description'  => _a('Which category article want to list'),
                'edit'         => array(
                    'type'        => 'Module\Article\Form\Element\CategoryWithRoot',
                ),
                'filter'       => 'string',
                'value'        => 0,
            ),
            'is-topic'         => array(
                'title'        => _a('Is Topic'),
                'description'  => _a('Whether to list topic articles'),
                'edit'         => array(
                    'type'        => 'checkbox',
                    'attributes'  => array(
                        'value'      => 0,
                    ),
                ),
                'filter'       => 'int',
            ),
            'topic'            => array(
                'title'        => _a('Topic'),
                'description'  => _a('Which topic article want to list'),
                'edit'         => array(
                    'type'        => 'Module\Article\Form\Element\Topic',
                ),
                'filter'       => 'string',
                'value'        => 0,
            ),
            'column-number'    => array(
                'title'        => _a('List Column Number'),
                'description'  => _a('Whether to display only one column or two columns'),
                'edit'         => array(
                    'type'        => 'radio',
                    'attributes'  => array(
                        'options'    => array(
                            'single'    => _a('Single column'),
                            'double'    => _a('Double columns'),
                        ),
                    ),
                ),
                'filter'       => 'string',
                'value'        => 'single',
            ),
            'element'          => array(
                'title'        => _a('Element to Display'),
                'description'  => _a('The article element to display'),
                'edit'         => array(
                    'type'        => 'multi_checkbox',
                    'attributes'  => array(
                        'options'    => array(
                            'time'         => _a('Time publish'),
                            'summary'      => _a('Summary'),
                            'feature'      => _a('Feature image'),
                        ),
                    ),
                ),
                'filter'       => 'array',
                'value'        => 'basic',
            ),
            'target'           => array(
                'title'        => _a('Target'),
                'description'  => _a('Open url in which window'),
                'edit'         => array(
                    'type'        => 'select',
                    'attributes'  => array(
                        'options'    => array(
                            '_blank'    => 'Blank',
                            '_parent'   => 'Parent',
                            '_self'     => 'Self',
                            '_top'      => 'Top',
                        ),
                    ),
                ),
                'filter'        => 'string',
                'value'         => '_blank',
            ),
            'max_subject_length' => array(
                'title'         => _a('Subject length'),
                'description'   => _a('Maximum length of subject'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 80,
            ),
            'max_summary_length' => array(
                'title'         => _a('Summary length'),
                'description'   => _a('Maximum length of summary'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 255,
            ),
            'description_rows'  => array(
                'title'         => _a('Description rows'),
                'description'   => _a('Maximum row count of description'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 2,
            ),
        ),
    ),
    'recommended-slideshow'    => array(
        'title'       => _a('Recommended Articles With Slideshow'),
        'title_hidden' => 1,
        'description' => _a('Listing a slideshow and recommended articles'),
        'render'      => 'block::recommendedSlideshow',
        'template'    => 'recommended-slideshow',
        'config'      => array(
            'articles'         => array(
                'title'        => _a('Article ID'),
                'description'  => _a('Articles want to list'),
                'edit'         => 'text',
                'filter'       => 'string',
                'value'        => 0,
            ),
            'element'          => array(
                'title'        => _a('Element to Display'),
                'description'  => _a('The article element to display'),
                'edit'         => array(
                    'type'        => 'multi_checkbox',
                    'attributes'  => array(
                        'options'    => array(
                            'time'         => _a('Time publish'),
                            'summary'      => _a('Summary'),
                            'feature'      => _a('Feature image'),
                        ),
                    ),
                ),
                'filter'       => 'array',
                'value'        => 'basic',
            ),
            'images'           => array(
                'title'        => _a('Image ID'),
                'description'  => _a('Images to display'),
                'edit'         => 'text',
                'filter'       => 'string',
                'value'        => 'image/default-recommended.png',
            ),
            'image-link'       => array(
                'title'        => _a('Image Link'),
                'description'  => _a('URL to redirect when click image'),
                'edit'         => 'textarea',
                'filter'       => 'string',
                'value'        => '',
            ),
            'image-width'      => array(
                'title'        => _a('Slide image width'),
                'description'  => _a('Maximum width of slide image'),
                'edit'         => 'text',
                'filter'       => 'int',
                'value'        => 320,
            ),
            'image-height'     => array(
                'title'        => _a('Slide image height'),
                'description'  => _a('Maximum height of slide image'),
                'edit'         => 'text',
                'filter'       => 'int',
                'value'        => 240,
            ),
            'target'           => array(
                'title'        => _a('Target'),
                'description'  => _a('Open url in which window'),
                'edit'         => array(
                    'type'        => 'select',
                    'attributes'  => array(
                        'options'    => array(
                            '_blank'    => 'Blank',
                            '_parent'   => 'Parent',
                            '_self'     => 'Self',
                            '_top'      => 'Top',
                        ),
                    ),
                ),
                'filter'        => 'string',
                'value'         => '_blank',
            ),
            'max_subject_length' => array(
                'title'         => _a('Subject length'),
                'description'   => _a('Maximum length of subject'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 80,
            ),
            'max_summary_length' => array(
                'title'         => _a('Summary length'),
                'description'   => _a('Maximum length of summary'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 255,
            ),
            'description_rows'  => array(
                'title'         => _a('Description rows'),
                'description'   => _a('Maximum row count of description'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 2,
            ),
        ),
    ),
    'custom-article-list'      => array(
        'title'       => _a('Custom Article List'),
        'description' => _a('Listing custom articles'),
        'render'      => 'block::customArticleList',
        'template'    => 'custom-article-list',
        'config'      => array(
            'articles'         => array(
                'title'        => _a('Article ID'),
                'description'  => _a('Articles want to list'),
                'edit'         => 'text',
                'filter'       => 'string',
                'value'        => 0,
            ),
            'column-number'    => array(
                'title'        => _a('List Column Number'),
                'description'  => _a('Whether to display only one column or two columns'),
                'edit'         => array(
                    'type'        => 'radio',
                    'attributes'  => array(
                        'options'    => array(
                            'single'    => _a('Single column'),
                            'double'    => _a('Double columns'),
                        ),
                    ),
                ),
                'filter'       => 'string',
                'value'        => 'single',
            ),
            'element'          => array(
                'title'        => _a('Element to Display'),
                'description'  => _a('The article element to display'),
                'edit'         => array(
                    'type'        => 'multi_checkbox',
                    'attributes'  => array(
                        'options'    => array(
                            'time'         => _a('Time publish'),
                            'summary'      => _a('Summary'),
                            'feature'      => _a('Feature image'),
                        ),
                    ),
                ),
                'filter'       => 'array',
                'value'        => 'basic',
            ),
            'target'           => array(
                'title'        => _a('Target'),
                'description'  => _a('Open url in which window'),
                'edit'         => array(
                    'type'        => 'select',
                    'attributes'  => array(
                        'options'    => array(
                            '_blank'    => 'Blank',
                            '_parent'   => 'Parent',
                            '_self'     => 'Self',
                            '_top'      => 'Top',
                        ),
                    ),
                ),
                'filter'        => 'string',
                'value'         => '_blank',
            ),
            'max_subject_length' => array(
                'title'         => _a('Subject length'),
                'description'   => _a('Maximum length of subject'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 80,
            ),
            'max_summary_length' => array(
                'title'         => _a('Summary length'),
                'description'   => _a('Maximum length of summary'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 255,
            ),
            'description_rows'  => array(
                'title'         => _a('Description rows'),
                'description'   => _a('Maximum row count of description'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 2,
            ),
        ),
    ),
    'submitter-statistics'     => array(
        'title'       => _a('Submitter Statistics'),
        'description' => _a('Listing the total article count of submitters'),
        'render'      => 'block::submitterStatistics',
        'template'    => 'submitter-statistics',
        'config'      => array(
            'list-count'       => array(
                'title'        => _a('List Count'),
                'description'  => _a('The max articles to display'),
                'edit'         => 'text',
                'filter'       => 'int',
                'value'        => 10,
            ),
        ),
    ),
    'newest-topic'             => array(
        'title'       => _a('Newest Topic'),
        'description' => _a('Listing the newest topic'),
        'render'      => 'block::newestTopic',
        'template'    => 'newest-topic',
        'config'      => array(
            'list-count'       => array(
                'title'        => _a('List Count'),
                'description'  => _a('The max articles to display'),
                'edit'         => 'text',
                'filter'       => 'int',
                'value'        => 10,
            ),
            'target'           => array(
                'title'        => _a('Target'),
                'description'  => _a('Open url in which window'),
                'edit'         => array(
                    'type'        => 'select',
                    'attributes'  => array(
                        'options'    => array(
                            '_blank'    => 'Blank',
                            '_parent'   => 'Parent',
                            '_self'     => 'Self',
                            '_top'      => 'Top',
                        ),
                    ),
                ),
                'filter'        => 'string',
                'value'         => '_blank',
            ),
            'max_title_length'  => array(
                'title'         => _a('Title length'),
                'description'   => _a('Maximum length of title'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 80,
            ),
            'max_description_length' => array(
                'title'         => _a('Description length'),
                'description'   => _a('Maximum length of description'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 80,
            ),
            'description_rows'  => array(
                'title'         => _a('Description rows'),
                'description'   => _a('Maximum row count of description'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 2,
            ),
        ),
    ),
    'hot-article'              => array(
        'title'       => _a('Hot Articles'),
        'description' => _a('Listing the hotest articles'),
        'render'      => 'block::hotArticles',
        'template'    => 'hot-article',
        'config'      => array(
            'list-count'       => array(
                'title'        => _a('List Count'),
                'description'  => _a('The max articles to display'),
                'edit'         => 'text',
                'filter'       => 'int',
                'value'        => 10,
            ),
            'is-topic'         => array(
                'title'        => _a('Is Topic'),
                'description'  => _a('Whether to list topic articles'),
                'edit'         => array(
                    'type'        => 'checkbox',
                    'attributes'  => array(
                        'value'      => 0,
                    ),
                ),
                'filter'       => 'int',
            ),
            'day-range'        => array(
                'title'        => _a('Day Range'),
                'description'  => _a('Day range'),
                'edit'         => 'text',
                'filter'       => 'int',
                'value'        => 7,
            ),
            'column-number'    => array(
                'title'        => _a('List Column Number'),
                'description'  => _a('Whether to display only one column or two columns'),
                'edit'         => array(
                    'type'        => 'radio',
                    'attributes'  => array(
                        'options'    => array(
                            'single'    => _a('Single column'),
                            'double'    => _a('Double columns'),
                        ),
                    ),
                ),
                'filter'       => 'string',
                'value'        => 'single',
            ),
            'element'          => array(
                'title'        => _a('Element to Display'),
                'description'  => _a('The article element to display'),
                'edit'         => array(
                    'type'        => 'multi_checkbox',
                    'attributes'  => array(
                        'options'    => array(
                            'time'         => _a('Time publish'),
                            'summary'      => _a('Summary'),
                            'feature'      => _a('Feature image'),
                        ),
                    ),
                ),
                'filter'       => 'array',
                'value'        => 'basic',
            ),
            'target'           => array(
                'title'        => _a('Target'),
                'description'  => _a('Open url in which window'),
                'edit'         => array(
                    'type'        => 'select',
                    'attributes'  => array(
                        'options'    => array(
                            '_blank'    => 'Blank',
                            '_parent'   => 'Parent',
                            '_self'     => 'Self',
                            '_top'      => 'Top',
                        ),
                    ),
                ),
                'filter'        => 'string',
                'value'         => '_blank',
            ),
            'max_subject_length' => array(
                'title'         => _a('Subject length'),
                'description'   => _a('Maximum length of subject'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 80,
            ),
            'max_summary_length' => array(
                'title'         => _a('Summary length'),
                'description'   => _a('Maximum length of summary'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 255,
            ),
            'description_rows'  => array(
                'title'         => _a('Description rows'),
                'description'   => _a('Maximum row count of description'),
                'edit'          => 'text',
                'filter'        => 'int',
                'value'         => 2,
            ),
        ),
    ),
    'simple-search'             => array(
        'title'       => _a('Simple Search'),
        'title_hidden'  => 1,
        'description' => _a('Search form for searching articles by article title'),
        'render'      => 'block::simpleSearch',
        'template'    => 'simple-search',
        'class'       => 'block-noborder',
    ),
    'rss'             => array(
        'title'       => _a('RSS Link'),
        'title_hidden'  => 1,
        'description' => _a('Click to subscribe article'),
        'render'      => 'block::rss',
        'template'    => 'rss',
        'class'       => 'block-noborder',
        'config'      => array(
            'target'           => array(
                'title'        => _a('Target'),
                'description'  => _a('Open url in which window'),
                'edit'         => array(
                    'type'        => 'select',
                    'attributes'  => array(
                        'options'    => array(
                            '_blank'    => 'Blank',
                            '_parent'   => 'Parent',
                            '_self'     => 'Self',
                            '_top'      => 'Top',
                        ),
                    ),
                ),
                'filter'       => 'string',
                'value'        => '_blank',
            ),
            'description'      => array(
                'title'        => _a('Description'),
                'description'  => _a('Description after RSS logo'),
                'edit'         => 'textarea',
                'filter'       => 'string',
                'value'        => _t('Subscribe content of this website'),
            ),
        ),
    ),
);
