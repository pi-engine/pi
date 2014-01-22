<?php
/**
 * Tag module navigation config
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
    // 'translate' => 'navigation',
    // Admin menu.
    'front'     => false,
    'admin'     => array(
        'top'   => array(
            'label'         => _t('Top tags'),
            'route'         => 'admin',
            'controller'    => 'index',
            'action'        => 'top',
        ),
        'new'   => array(
            'label'         => _t('New tags'),
            'route'         => 'admin',
            'controller'    => 'index',
            'action'        => 'new',
        ),
        'items'    => array(
            'label'         => _t('Tagged items'),
            'route'         => 'admin',
            'controller'    => 'index',
            'action'        => 'link',
        ),
    ),
);
