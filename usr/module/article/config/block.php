<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
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
    'hot-categories'           => array(
        'title'       => _t('Hot Categories'),
        'description' => _t('Listing hot categories according to their articles'),
        'render'      => 'block::hotCategories',
        'template'    => 'hot-categories',
        'config'      => array(
            'list-count'       => array(
                'title'        => _a('List Count'),
                'description'  => _a('The max categories to display'),
                'filter'       => 'number_int',
                'value'        => 18,
            ),
            'day-range'        => array(
                'title'        => _a('Day Range'),
                'description'  => _a('Day range'),
                'filter'       => 'number_int',
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
                'filter'       => 'number_int',
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
                'filter'       => 'number_int',
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
            'block-style'      => array(
                'title'        => _a('Template Style'),
                'description'  => _a('The template style of list'),
                'edit'         => array(
                    'type'        => 'radio',
                    'attributes'  => array(
                        'options'    => array(
                            'basic'        => _a('basic'),
                            'common'       => _a('Common'),
                            'summary'      => _a('With summary'),
                            'feature'      => _a('With feature'),
                            'all-featured' => _a('All with feature'),
                            'all-summary'  => _a('All with summary'),
                            'rank'         => _a('With rank number'),
                        ),
                    ),
                ),
                'filter'       => 'string',
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
                'filter'        => 'number_int',
                'value'         => 80,
            ),
            'max_summary_length' => array(
                'title'         => _a('Summary length'),
                'description'   => _a('Maximum length of summary'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 255,
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
            'block-style'      => array(
                'title'        => _a('Template Style'),
                'description'  => _a('The template style of list'),
                'edit'         => array(
                    'type'        => 'radio',
                    'attributes'  => array(
                        'options'    => array(
                            'basic'     => _a('Basic'),
                            'common'    => _a('Common'),
                            'summary'   => _a('With summary'),
                        ),
                    ),
                ),
                'filter'       => 'string',
                'value'        => 'common',
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
                'filter'        => 'number_int',
                'value'         => 80,
            ),
            'max_summary_length' => array(
                'title'         => _a('Summary length'),
                'description'   => _a('Maximum length of summary'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 255,
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
            'block-style'      => array(
                'title'        => _a('Template Style'),
                'description'  => _a('The template style of list'),
                'edit'         => array(
                    'type'        => 'radio',
                    'attributes'  => array(
                        'options'    => array(
                            'basic'        => _a('basic'),
                            'common'       => _a('Common'),
                            'summary'      => _a('With summary'),
                            'feature'      => _a('With feature'),
                            'all-featured' => _a('All with feature'),
                            'all-summary'  => _a('All with summary'),
                            'rank'         => _a('With rank number'),
                        ),
                    ),
                ),
                'filter'       => 'string',
                'value'        => 'common',
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
                'filter'        => 'number_int',
                'value'         => 80,
            ),
            'max_summary_length' => array(
                'title'         => _a('Summary length'),
                'description'   => _a('Maximum length of summary'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 255,
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
                'filter'       => 'number_int',
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
                'filter'       => 'number_int',
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
                'filter'        => 'number_int',
                'value'         => 80,
            ),
            'max_description_length' => array(
                'title'         => _a('Description length'),
                'description'   => _a('Maximum length of description'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 80,
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
                'filter'       => 'number_int',
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
                'filter'       => 'number_int',
            ),
            'day-range'        => array(
                'title'        => _a('Day Range'),
                'description'  => _a('Day range'),
                'edit'         => 'text',
                'filter'       => 'number_int',
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
            'block-style'      => array(
                'title'        => _a('Template Style'),
                'description'  => _a('The template style of list'),
                'edit'         => array(
                    'type'        => 'radio',
                    'attributes'  => array(
                        'options'    => array(
                            'basic'        => _a('basic'),
                            'common'       => _a('Common'),
                            'summary'      => _a('With summary'),
                            'feature'      => _a('With feature'),
                            'all-featured' => _a('All with feature'),
                            'all-summary'  => _a('All with summary'),
                            'rank'         => _a('With rank number'),
                        ),
                    ),
                ),
                'filter'       => 'string',
                'value'        => 'common',
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
                'filter'        => 'number_int',
                'value'         => 80,
            ),
            'max_summary_length' => array(
                'title'         => _a('Summary length'),
                'description'   => _a('Maximum length of summary'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 255,
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
);
