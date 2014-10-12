<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Configuration defined which pages allow to add
 * 
 * Page condition: which kind of page is allowed to dress up
 * The condition will be parsed to controller&action according to rule config
 * <code>
 * 'condition' => array(
 *     '<condition_unique_key>' => array(
 *         // Use form value as condition
 *         '<form_name_1>' => '<form_value_1>',
 *         // Only use form name as condition
 *         '<form_name_2>' => '',
 *         // Or
 *         '<form_name_2>'
 *         ...
 *     ),
 * 
 *     // For example, this condition means homepage of category related articles
 *     // allows to dress up
 *     'category_homepage' => array(
 *         'category' => '',
 *         // Or
 *         'category',
 *         'type'     => 'homepage',
 *     ),
 * ),
 * </code>
 * 
 * Page rule: define which controller&action the condition will parse to
 * <code>
 * 'rule' => array(
 *     '<codition_key>' => array(
 *         'controller' => '<controller_name>',
 *         'action'     => '<action_name>',
 *     ),
 * 
 *     // For example, the following rule means category homepage will parsed to
 *     // CategoryController and indexAction
 *     'category_homepage' => array(
 *         'controller' => 'category',
 *         'action'     => 'index',
 *     ),
 * ),
 * </code>
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
return array(
    // Condition: which kind of page is allowed
    'condition' => array(
        'category_homepage' => array(
            'category',
            'type' => 'homepage',
        ),
        'category_list'     => array(
            'category',
            'type' => 'list',
        ),
        'category_detail'   => array(
            'category',
            'type' => 'detail',
        ),
    ),
    
    // Rule
    'rule'      => array(
        'category_homepage' => array(
            'controller'    => 'category',
            'action'        => 'index',
        ),
        'category_list'     => array(
            'controller'    => 'list',
            'action'        => 'index',
        ),
        'category_detail'   => array(
            'controller'    => 'article',
            'action'        => 'detail',
        ),
    ),
);
