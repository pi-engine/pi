<?php
/**
 * Page specifications
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
 * @package         Pi\Setup
 * @since           3.0
 * @version         $Id$
 */

$pages = array(
    'presetting'     => array(
        'title' => _t("Presettings"),
        'desc'  => _t("Presettings and server configuration detection")
    ),
    'directive'     => array(
        'title' => _t("Directives"),
        'desc'  => _t("Directive settings for website")
    ),
    'database'      => array(
        'title' => _t("Database"),
        'desc'  => _t("Database settings")
    ),
    'admin'         => array(
        'title' => _t("Administrator"),
        'desc'  => _t("Administrator account creation")
    ),
    'finish'        => array(
        'title' => _t("Finish"),
        'desc'  => _t("Finishing installation process"),
        'hide'  => true,
    ),
);

return $pages;
