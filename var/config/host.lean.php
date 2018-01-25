<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * Pi Engine host specifications
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

/**
 * Host definition file with lean specification
 *
 * Paths/URLs to system folders
 *
 * - URIs without a leading slash are considered relative
 *      to the current Pi Engine host location
 * - URIs with a leading slash are considered semi-relative
 *      requires proper rewriting rules in server conf
 */
return [
    // URIs to resources
    // If URI is a relative one then www root URI will be prepended
    'uri'  => [
        // WWW root URI
        'www' => 'http://pi.tld',
    ],

    // Paths to resources
    // If path is a relative one then www root path will be prepended
    'path' => [
        // Sharable paths
        // WWW root path, dependent sub folders: `script`, `public`
        'www' => '/path/to/pi-framework/www',
        // Library directory
        'lib' => '/path/to/pi-framework/lib',
        // User extension directory
        'usr' => '/path/to/pi-framework/usr',
    ],
];
