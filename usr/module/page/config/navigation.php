<?php
/**
 * Page module navigation config
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
 * @package         Module\Page
 * @version         $Id$
 */

return array(
    'meta'  => array(
        'site'  => array(
            'title'     => 'Custom site navigation',
            'section'   => 'front',
        ),
    ),
    'item'  => array(
        'front'     => false,
        'admin'     => array(
            'list'     => array(
                'label'         => 'Page list',
                'route'         => 'admin',
                'controller'    => 'index',
                'action'        => 'index',

                'pages' => array(
                    'edit'   => array(
                        'label' => 'Edit page',
                        'route'         => 'admin',
                        'controller'    => 'index',
                        'action'        => 'edit',
                        'visible'       => 0,
                    ),
                    'delete'   => array(
                        'label' => 'Delete page',
                        'route'         => 'admin',
                        'controller'    => 'index',
                        'action'        => 'delete',
                        'visible'       => 0,
                    ),
                ),
            ),
            'add'   => array(
                'label' => 'Add a page',
                'route'         => 'admin',
                'controller'    => 'index',
                'action'        => 'add',
            ),
        ),

        // Custom navigation
        'site' => array(
            'home'     => array(
                'label'         => 'Home',
                'route'         => 'home',
            ),
            'about'     => array(
                'label'         => 'About us',
                'route'         => '.page',
                'action'        => 'about',
            ),
            'contact'     => array(
                'label'         => 'Contact us',
                'route'         => '.page',
                'action'        => 'contact',
            ),
            'term'     => array(
                'label'         => 'Terms of use',
                'route'         => '.page',
                'action'        => 'terms',
            ),
            'privacy'     => array(
                'label'         => 'Privacy guidelines',
                'route'         => '.page',
                'action'        => 'privacy',
            ),
            'join'     => array(
                'label'         => 'Join us',
                'route'         => 'user',
            ),
            'app'       => array(
                'label'         => 'Applications',
                'uri'           => '',
                'callback'      => 'Module\\Page\\Navigation::modules',
            ),
            'eefocus'   => array(
                'label'         => 'EEFOCUS',
                'uri'           => 'http://www.eefocus.com',
                'target'        => '_blank',
            ),
        ),
    ),
);
