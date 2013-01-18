<?php
/**
 * Demo module ACL config
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

return array(
    'resources' => array(
        'front'    => array(
            // test
            'test'  => array(
                'title'         => __('Test resource'),
                'privileges'    => array(
                    'read'  => array(
                        'title'     => __('Read privilege'),
                        'access'    => array(
                            'guest'     => 1,
                            'member'    => 1,
                        )
                    ),
                    'write'  => array(
                        'title' => __('Write privilege'),
                        'access'    => array(
                            'guest'     => 0,
                            'member'    => 1,
                        )
                    ),
                    'manage'  => array(
                        'title' => __('Management privilege'),
                        'access'    => array(
                            'guest'     => 0,
                            'moderator' => 1,
                        )
                    ),
                )
            ),
        ),
    ),
);
