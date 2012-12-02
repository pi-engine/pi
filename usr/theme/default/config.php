<?php
/**
 * Pi Engine default theme configuration
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
 * @package         Pi\Theme
 * @version         $Id$
 */

/**
 * A complete theme set should include following files:
 *
 * Folder and file skeleton:
 * REQUIRED for front:
 *  tmplate/layout-front.phtml - complete layout template: header, footer, body, blocks, navigation
 *  tmplate/layout-simple.phtml - error page layout: header, footer, body
 *  tmplate/layout-style.phtml - content with stylesheets
 *  tmplate/layout-content.phtml - raw content without stylesheets
 * REQUIRED for admin:
 *  tmplate/layout-admin.phtml - backoffice layout
 * OPTIONAL for front:
 *  template/page-zone.phtml - for block manipulation on a page
 *
 * Stylesheet files:
 * REQUIRED:
 *  asset/css/style.css - main css file
 *
 * Best practices:
 *  1 It is hightly recommended to use 'pi-' as prefix for all id's used in theme to avoid conflicts.
 */

return array(
    /**#@+
     * To be stored in DB
     */
    // Version
    'version'       => '1.0.0-beta.1',
    // Type of layouts available in the theme
    'type'          => 'both', // Potential value: 'both', 'admin', 'front', default as 'both'
    /**#@-**/

    // Title of the theme
    'title'         => 'Pi Default Theme',
    // Author information: name, email, website
    'author'        => 'Theme architecture: Taiwen Jiang <taiwenjiang@tsinghua.org.cn>; Resources: Pi Engine Development Team',
    // Screenshot image, relative path in asset. If no screenshot is available, static/image/screenshot.png will be used
    'screenshot'    => 'image/screenshot.png',
    // License or theme images and scripts
    'license'       => 'Creative Common License http://creativecommons.org/licenses/by/3.0/',
    // Optional description
    'description'   => 'Default theme for Pi Engine',
);
