<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Meta for Pi Theme
 *
 * Theme inherited from Default Theme
 */
return [
    // Version
    'version'     => '1.1.1',
    // Type of layouts available in the theme
    'type'        => 'front',

    // Title of the theme
    'title'       => 'Pi Theme',
    // Author information: name, email, website
    'author'      => 'Architecture:'
        . ' Taiwen Jiang <taiwenjiang@tsinghua.org.cn>,'
        . ' Hossein Azizabadi Farahani <hossein@azizabadi.com>;'
        . ' Front-end: @sexnothing; Resources: @loidco',
    // Screenshot image
    'screenshot'  => 'image/screenshot.png',
    // License or theme images and scripts
    'license'     => 'Creative Common License'
        . ' http://creativecommons.org/licenses/by/3.0/',
    // Optional description
    'description' => 'Demo for theme inheritance and customization.',
    // Parent theme from which templates can be inherited, default as 'default'
    'parent'      => 'default',
    // Supported browsers
    'browser'     => 'Internet Explorer: 8+; Chrome: latest;'
        . ' Firefox: latest; Safari: latest; Opera: latest;',

    // List of custom front layouts, optional
    'layout'      => [
        'layout-front' => __('Custom layout'),
    ],
];
