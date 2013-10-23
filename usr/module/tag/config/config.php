<?php
/**
 *  Tag module configs
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
    // Categories for config edit or display
    'category'  => array(
        array(
            'title' => 'General',
            'name'  => 'general',
        ),
    ),
    // Config items
    'item'         => array(
        // Tag list item per page
        'item_per_page' => array(
            'category'      => 'general',
            'title'         => 'Item per page',
            'description'   => 'Number of items on tag list page.',
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

        // Tag link item per page
        'detail_per_page' => array(
            'category'      => 'general',
            'title'         => 'Detail per page',
            'description'   => 'Number of items on tag detail page.',
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

        // Link list item per page
        'link_per_page' => array(
            'category'      => 'general',
            'title'         => 'Link per page',
            'description'   => 'Number of items on one relationships page.',
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
    )
);
