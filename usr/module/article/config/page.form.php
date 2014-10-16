<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Forms definition, which will display in page adding page
 * This configuration work together with page.rule configuration to decide
 * what page can be dress up.
 * 
 * Form definition @see config/form.php, but the following config is NOT SUPPORT:
 * - Compound field
 * - is_insert
 * - is_edit
 * - is_display (Set the form type to `hidden` if do not want to display it)
 * - type
 * 
 * Added field
 * - condition_level: use form name or value as condition
 * <code>
 * // Use form value as condition
 * 'condition_level' => 'value',
 * // Use form name as condition
 * 'condition_level' => 'name',
 * </code>
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    'field' => array(
        // Default form for describe page, required
        'title'     => array(
            'name'        => 'title',
            'title'       => _a('Title'),
            'is_required' => true,
            'edit'        => array(
                'attributes' => array(
                    'description'   => _a('Title for display'),
                ),
            ),
        ),
        'name'      => array(
            'name'        => 'name',
            'title'       => _a('Unique Name'),
            'edit'        => array(
                'validators' => array(
                    array(
                        'name'    => 'Module\Article\Validator\RepeatName',
                        'options' => array(
                            'table'     => 'page',
                        )
                    ),
                    array(
                        'name'    => 'Regex',
                        'options' => array(
                            'pattern'   => '/^[a-z][a-z0-9-]{4,32}$/',
                        ),
                    ),
                ),
                'attributes' => array(
                    'description'   => _a('Unique name, start with letter, and only letter, digit and `_` allowed.'),
                ),
            ),
            'is_required' => true,
        ),
        /*'parent'    => array(
            'name'        => 'parent',
            'title'       => _a('Parent'),
            'edit'        => array(
                'element'    => 'Module\Article\Form\Element\Page',
                'attributes' => array(
                    'description'   => _a('Blocks of this page will be used and current page donot need to dress up again.'),
                ),
            ),
        ),*/
        'parent'    => array(
            'name'        => 'parent',
            'edit'        => array(
                'element'    => 'hidden',
                'attributes' => array(
                    'value'         => 1,
                ),
            ),
        ),
        
        // Condition form, use to description which controller&action will be requested, optional
        'category'  => array(
            'name'        => 'category',
            'title'       => _a('Category'),
            'edit'        => array(
                'element'    => 'Module\Article\Form\Element\Category',
                'attributes' => array(
                    'description'   => _a('Page with which category to dress up.'),
                ),
                'options'    => array(
                    'blank'         => true,
                    'all'           => true,
                ),
            ),
            // Use this form name as condition
            'condition_level' => 'key',
        ),
        /*'cluster'   => array(
            'name'        => 'cluster',
            'title'       => _a('Cluster'),
            'edit'        => array(
                'element'    => 'Module\Article\Form\Element\Cluster',
                'attributes' => array(
                    'description'   => _a('Page with which cluster to dress up.'),
                ),
                'options'    => array(
                    'blank'         => true,
                    'all'           => true,
                ),
            ),
            'condition_level' => 'key',
        ),*/
        'type'      => array(
            'name'        => 'type',
            'title'       => _a('Type'),
            'edit'        => array(
                'element'    => 'select',
                'options'    => array(
                    'options'   => array(
                        'homepage'  => _a('Homepage'),
                        'list'      => _a('List page'),
                        'detail'    => _a('Detail page'),
                    ),
                ),
                'attributes' => array(
                    'description'   => _a('Which type of page to dress up.'),
                ),
            ),
            'condition_level' => 'value',
        ),
        
        // Default SEO details, optional, required as recommendation
        'seo_title' => array(
            'name'        => 'seo_title',
            'title'       => _a('SEO Title'),
            'edit'        => array(
                'attributes' => array(
                    'description'   => _a('Page title.'),
                ),
            ),
        ),
        'seo_keywords' => array(
            'name'        => 'seo_keywords',
            'title'       => _a('SEO Keywords'),
            'edit'        => array(
                'attributes' => array(
                    'description'   => _a('Keywords page meta.'),
                ),
            ),
        ),
        'seo_description' => array(
            'name'        => 'seo_description',
            'title'       => _a('SEO Description'),
            'edit'        => array(
                'element'    => 'textarea',
                'attributes' => array(
                    'description'   => _a('Description page meta.'),
                    'rows'          => 5,
                ),
            ),
        ),
        'active'          => array(
            'name'        => 'active',
            'title'       => _a('Active'),
            'edit'        => array(
                'element'    => 'checkbox',
                'attributes' => array(
                    'value'         => 1,
                ),
            ),
        ),
    ),
);
