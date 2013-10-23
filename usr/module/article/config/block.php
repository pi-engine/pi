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
        'title'       => _t('All Categories'),
        'description' => _t('Listing the parent category and its children'),
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
                'value'        => _t('None'),
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
                'title'        => _t('List Count'),
                'description'  => _t('The max categories to display'),
                'filter'       => 'number_int',
                'value'        => 18,
            ),
            'day-range'        => array(
                'title'        => _t('Day Range'),
                'description'  => _t('Day range'),
                'filter'       => 'number_int',
                'value'        => 7,
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
    'newest-published-article' => array(
        'title'       => _t('Newest Published Articles'),
        'description' => _t('Listing the newest published articles of topic or non-topic'),
        'render'      => 'block::newestPublishedArticles',
        'template'    => 'newest-published-articles',
        'config'      => array(
            'list-count'       => array(
                'title'        => _t('List Count'),
                'description'  => _t('The max articles to display'),
                'edit'         => 'text',
                'filter'       => 'number_int',
                'value'        => 10,
            ),
            'category'         => array(
                'title'        => _t('Category'),
                'description'  => _t('Which category article want to list'),
                'edit'         => array(
                    'type'        => 'Module\Article\Form\Element\CategoryWithRoot',
                ),
                'filter'       => 'string',
                'value'        => 0,
            ),
            'is-topic'         => array(
                'title'        => _t('Is Topic'),
                'description'  => _t('Whether to list topic articles'),
                'edit'         => array(
                    'type'        => 'checkbox',
                    'attributes'  => array(
                        'value'      => 0,
                    ),
                ),
                'filter'       => 'number_int',
            ),
            'topic'            => array(
                'title'        => _t('Topic'),
                'description'  => _t('Which topic article want to list'),
                'edit'         => array(
                    'type'        => 'Module\Article\Form\Element\Topic',
                ),
                'filter'       => 'string',
                'value'        => 0,
            ),
            'column-number'    => array(
                'title'        => _t('List Column Number'),
                'description'  => _t('Whether to display only one column or two columns'),
                'edit'         => array(
                    'type'        => 'radio',
                    'attributes'  => array(
                        'options'    => array(
                            'single'    => _t('Single column'),
                            'double'    => _t('Double columns'),
                        ),
                    ),
                ),
                'filter'       => 'string',
                'value'        => 'single',
            ),
            'block-style'      => array(
                'title'        => _t('Template Style'),
                'description'  => _t('The template style of list'),
                'edit'         => array(
                    'type'        => 'radio',
                    'attributes'  => array(
                        'options'    => array(
                            'basic'        => _t('basic'),
                            'common'       => _t('Common'),
                            'summary'      => _t('With summary'),
                            'feature'      => _t('With feature'),
                            'all-featured' => _t('All with feature'),
                            'all-summary'  => _t('All with summary'),
                            'rank'         => _t('With rank number'),
                        ),
                    ),
                ),
                'filter'       => 'string',
                'value'        => 'basic',
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
            'max_subject_length' => array(
                'title'         => _t('Subject length'),
                'description'   => _t('Maximum length of subject'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 80,
            ),
            'max_summary_length' => array(
                'title'         => _t('Summary length'),
                'description'   => _t('Maximum length of summary'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 255,
            ),
        ),
    ),
    'recommended-slideshow'    => array(
        'title'       => _t('Recommended Articles With Slideshow'),
        'title_hidden' => 1,
        'description' => _t('Listing a slideshow and recommended articles'),
        'render'      => 'block::recommendedSlideshow',
        'template'    => 'recommended-slideshow',
        'config'      => array(
            'articles'         => array(
                'title'        => _t('Article ID'),
                'description'  => _t('Articles want to list'),
                'edit'         => 'text',
                'filter'       => 'string',
                'value'        => 0,
            ),
            'block-style'      => array(
                'title'        => _t('Template Style'),
                'description'  => _t('The template style of list'),
                'edit'         => array(
                    'type'        => 'radio',
                    'attributes'  => array(
                        'options'    => array(
                            'basic'     => _t('Basic'),
                            'common'    => _t('Common'),
                            'summary'   => _t('With summary'),
                        ),
                    ),
                ),
                'filter'       => 'string',
                'value'        => 'common',
            ),
            'images'           => array(
                'title'        => _t('Image ID'),
                'description'  => _t('Images to display'),
                'edit'         => 'text',
                'filter'       => 'string',
                'value'        => 'image/default-recommended.png',
            ),
            'image-link'       => array(
                'title'        => _t('Image Link'),
                'description'  => _t('URL to redirect when click image'),
                'edit'         => 'textarea',
                'filter'       => 'string',
                'value'        => '',
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
            'max_subject_length' => array(
                'title'         => _t('Subject length'),
                'description'   => _t('Maximum length of subject'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 80,
            ),
            'max_summary_length' => array(
                'title'         => _t('Summary length'),
                'description'   => _t('Maximum length of summary'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 255,
            ),
        ),
    ),
    'custom-article-list'      => array(
        'title'       => _t('Custom Article List'),
        'description' => _t('Listing custom articles'),
        'render'      => 'block::customArticleList',
        'template'    => 'custom-article-list',
        'config'      => array(
            'articles'         => array(
                'title'        => _t('Article ID'),
                'description'  => _t('Articles want to list'),
                'edit'         => 'text',
                'filter'       => 'string',
                'value'        => 0,
            ),
            'column-number'    => array(
                'title'        => _t('List Column Number'),
                'description'  => _t('Whether to display only one column or two columns'),
                'edit'         => array(
                    'type'        => 'radio',
                    'attributes'  => array(
                        'options'    => array(
                            'single'    => _t('Single column'),
                            'double'    => _t('Double columns'),
                        ),
                    ),
                ),
                'filter'       => 'string',
                'value'        => 'single',
            ),
            'block-style'      => array(
                'title'        => _t('Template Style'),
                'description'  => _t('The template style of list'),
                'edit'         => array(
                    'type'        => 'radio',
                    'attributes'  => array(
                        'options'    => array(
                            'basic'        => _t('basic'),
                            'common'       => _t('Common'),
                            'summary'      => _t('With summary'),
                            'feature'      => _t('With feature'),
                            'all-featured' => _t('All with feature'),
                            'all-summary'  => _t('All with summary'),
                            'rank'         => _t('With rank number'),
                        ),
                    ),
                ),
                'filter'       => 'string',
                'value'        => 'common',
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
            'max_subject_length' => array(
                'title'         => _t('Subject length'),
                'description'   => _t('Maximum length of subject'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 80,
            ),
            'max_summary_length' => array(
                'title'         => _t('Summary length'),
                'description'   => _t('Maximum length of summary'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 255,
            ),
        ),
    ),
    'submitter-statistics'     => array(
        'title'       => _t('Submitter Statistics'),
        'description' => _t('Listing the total article count of submitters'),
        'render'      => 'block::submitterStatistics',
        'template'    => 'submitter-statistics',
        'config'      => array(
            'list-count'       => array(
                'title'        => _t('List Count'),
                'description'  => _t('The max articles to display'),
                'edit'         => 'text',
                'filter'       => 'number_int',
                'value'        => 10,
            ),
        ),
    ),
    'newest-topic'             => array(
        'title'       => _t('Newest Topic'),
        'description' => _t('Listing the newest topic'),
        'render'      => 'block::newestTopic',
        'template'    => 'newest-topic',
        'config'      => array(
            'list-count'       => array(
                'title'        => _t('List Count'),
                'description'  => _t('The max articles to display'),
                'edit'         => 'text',
                'filter'       => 'number_int',
                'value'        => 10,
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
            'max_title_length'  => array(
                'title'         => _t('Title length'),
                'description'   => _t('Maximum length of title'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 80,
            ),
            'max_description_length' => array(
                'title'         => _t('Description length'),
                'description'   => _t('Maximum length of description'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 80,
            ),
        ),
    ),
    'hot-article'              => array(
        'title'       => _t('Hot Articles'),
        'description' => _t('Listing the hotest articles'),
        'render'      => 'block::hotArticles',
        'template'    => 'hot-article',
        'config'      => array(
            'list-count'       => array(
                'title'        => _t('List Count'),
                'description'  => _t('The max articles to display'),
                'edit'         => 'text',
                'filter'       => 'number_int',
                'value'        => 10,
            ),
            'is-topic'         => array(
                'title'        => _t('Is Topic'),
                'description'  => _t('Whether to list topic articles'),
                'edit'         => array(
                    'type'        => 'checkbox',
                    'attributes'  => array(
                        'value'      => 0,
                    ),
                ),
                'filter'       => 'number_int',
            ),
            'day-range'        => array(
                'title'        => _t('Day Range'),
                'description'  => _t('Day range'),
                'edit'         => 'text',
                'filter'       => 'number_int',
                'value'        => 7,
            ),
            'column-number'    => array(
                'title'        => _t('List Column Number'),
                'description'  => _t('Whether to display only one column or two columns'),
                'edit'         => array(
                    'type'        => 'radio',
                    'attributes'  => array(
                        'options'    => array(
                            'single'    => _t('Single column'),
                            'double'    => _t('Double columns'),
                        ),
                    ),
                ),
                'filter'       => 'string',
                'value'        => 'single',
            ),
            'block-style'      => array(
                'title'        => _t('Template Style'),
                'description'  => _t('The template style of list'),
                'edit'         => array(
                    'type'        => 'radio',
                    'attributes'  => array(
                        'options'    => array(
                            'basic'        => _t('basic'),
                            'common'       => _t('Common'),
                            'summary'      => _t('With summary'),
                            'feature'      => _t('With feature'),
                            'all-featured' => _t('All with feature'),
                            'all-summary'  => _t('All with summary'),
                            'rank'         => _t('With rank number'),
                        ),
                    ),
                ),
                'filter'       => 'string',
                'value'        => 'common',
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
            'max_subject_length' => array(
                'title'         => _t('Subject length'),
                'description'   => _t('Maximum length of subject'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 80,
            ),
            'max_summary_length' => array(
                'title'         => _t('Summary length'),
                'description'   => _t('Maximum length of summary'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 255,
            ),
        ),
    ),
    'simple-search'             => array(
        'title'       => _t('Simple Search'),
        'title_hidden'  => 1,
        'description' => _t('Search form for searching articles by article title'),
        'render'      => 'block::simpleSearch',
        'template'    => 'simple-search',
        'class'       => 'block-noborder',
    ),
);
