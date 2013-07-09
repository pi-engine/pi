<?php
/**
 * Demo module configs
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
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\Demo
 * @version         $Id$
 */


/**
 * Config definition
 * With category and configs
 * <code>
 *  return array(
 *      'category'  => array(
 *          array(
 *              'name'  => 'category_name',
 *              'title' => 'Category Title'
 *              'order' => 1,
 *          ),
 *          array(
 *              'name'  => 'category_b',
 *              'title' => 'Category B Title'
 *              'order' => 2,
 *          ),
 *          ...
 *      ),
 *      'item'     => array(
 *          // Config of input textbox
 *          'config_name_a' => array(
 *              'title'         => 'Config title A',
 *              'category'      => 'cate',
 *              'description'   => '',
 *              'value'         => 'a config',
 *              'edit'          => 'input'
 *              'filter'        => 'text',
 *          ),
 *          // 'edit' default as 'input'
 *          'config_name_ab' => array(
 *              'title'         => 'Config title AB',
 *              'category'      => 'cate',
 *              'description'   => '',
 *              'value'         => 'a config',
 *              'filter'        => 'text',
 *          ),
 *          // Config with select edit type
 *          'config_name_b' => array(
 *              'title'         => 'Config title B',
 *              'description'   => '',
 *              'value'         => 'option_a',
 *              'edit'          => array(
 *                  'type'      => 'select'
 *                  'options'   => array(
 *                      'options'   => array(
 *                          'option_a'  => 'Option A',
 *                          'option_b'  => 'Option B',
 *                      ),
 *                  ),
 *              ),
 *              'filter'        => 'filtertype',
 *          ),
 *          // Config with custom edit element
 *          'config_name_c' => array(
 *              'title'         => 'Config title C',
 *              'category'      => 'general',
 *              'description'   => '',
 *              'value'         => '',
 *              'edit'          => array(
 *                  'type'          => 'Module\Demo\Form\Element\ConfigTest',
 *                  'attributes'    => array(
 *                      'att'   => 'attValue',
 *                  ),
 *              ),
 *              'filter'        => 'filtertype',
 *          ),
 *          // Config not show on edit pages
 *          'config_name_d' => array(
 *              'title'         => 'Config title D',
 *              'category'      => 'general',
 *              'description'   => '',
 *              'value'         => 'a config',
 *              'filter'        => 'text',
 *              'visible'       => 0,                       // Not show on edit page
 *          ),
 *          // Orphan configs
 *          'config_name_e' => array(
 *              'title'         => 'Config title E',
 *              'category'      => '',                      // Not managed by any category
 *              'description'   => '',
 *              'value'         => 'a config',
 *              'edit'          => 'SpecifiedEditElement',
 *              'filter'        => 'text',
 *          ),
 *
 *          ...
 *      )
 *  );
 * </code>
 * Only with configs
 * <code>
 *  return array(
 *          'config_name'   => array(
 *              'title'         => 'Config title',
 *              'category'      => '',
 *              'description'   => '',
 *              'value'         => '',
 *          ),
 *          ...
 *  );
 * </code>
 */


/**
 * Configs
 */
return array(
    // Categories for config edit or display
    'category'  => array(
        array(
            'title' => 'General',
            'name'  => 'general',
        ),
        array(
            'title' => 'Test',
            'name'  => 'test'
        ),
    ),
    // Config items
    'item'         => array(
        'item_per_page' => array(
            'category'      => 'general',
            'title'         => 'Item per page',
            'description'   => 'Number of items on one page.',
            'value'         => 10,
            'edit'          => array(
                'type'      => 'select',
                'options'   => array(
                    'options'   => array(
                        10  => '10',
                        20  => '20',
                        50  => '50',
                    ),
                ),
            ),
        ),

        'test'  => array(
            'category'      => 'test',
            'title'         => 'Test Conf',
            'description'   => 'An example for configuration.',
            'value'         => 'Configuration text for testing'
        ),

        'add'   => array(
            'category'      => 'general',
            'title'         => 'Add Item',
            'description'   => 'An example for adding configuration.',
            'edit'          => array(
                'type'      => 'select',
                'attributes'    => array(
                    'multiple'  => true,
                ),
                'options'   => array(
                    'options'   => array(
                        1   => 'One',
                        2   => 'Two',
                        3   => 'Three',
                    ),
                ),
            ),
            'filter'        => 'array',
            'value'         => array(1, 2),
        )
    )
);
