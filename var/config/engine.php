<?php
/**
 * Pi Engine application specifications
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
 * @version         $Id$
 */

// Application configs
return array(
    // application configs
    'config'    => array(
        //Site specific identifier, you should not change it after installation
        'identifier'    => 'pieca8',

        //Salt for hashing
        'salt'          => '0ad4396951c58d0075c9d76ae9045485',

        //Run mode. Potential values: production - for production, debug - for users debugging, development - for developers, close - for maintenance
        'environment'   => 'development',
    ),

    // System persist storage configs
    'persist'   => array(
        'storage'   => 'apc',
        'namespace' => 'eca8',
        'options'   => array(
        ),
    ),
);
