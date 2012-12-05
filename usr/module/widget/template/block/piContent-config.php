<?php
/**
 * Widget for fetching Pi Engine content
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
 * @package         Module\Widget
 * @version         $Id$
 */

// Widget meta
return array(
    'title'         => __('Pi Engine top contents'),
    'description'   => __('Block to display Pi Engine top and hot contents'),
    //'template'      => 'pi-content',
    'render'        => array('PiContent', 'test'),
    'config'        => array(
        // text option
        'subline' => array(
            'title'         => 'Subline',
            'description'   => 'Caption for the block',
            'edit'          => 'text',
            'filter'        => 'string',
            'value'         => __('Enjoy creating and sharing'),
        ),
        // Yes or No option
        'show_github'    => array(
            'title'         => 'Github activities',
            'description'   => 'To display commit activites from github.',
            'edit'          => 'checkbox',
            //'filter'        => 'number_int',
            'value'         => '1',
        ),
    ),
    'access'        => array(
        'guest'     => 0,
        'member'    => 1,
    ),
);