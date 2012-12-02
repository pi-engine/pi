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

$config = include __DIR__ . '/application.front.php';
// Translations
$config['resource']['i18n']['translator']['global'][] = 'usr:admin';
$config['resource']['i18n']['translator']['module'][] = 'admin';
// Permission ACL
$config['resource']['acl'] = array(
    // Default access perm in case not defined
    'default'   => false,
);
// Caching
$config['resource']['cache'] = false;

return $config;
