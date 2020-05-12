<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

$configs = [];

// Server settings
$configs['system'] = [
    'server'  => _s('Web server'),
    'php'     => _s('PHP'),
    'persist' => _s('Persist options'),
    'pdo'     => _s('PDO drivers'),
    'vendor'  => _s('Composer update'),
];

// PHP extensions
$configs['extension'] = [
    'apc'       => [
        'title'   => _s('APC'),
        'message' => _s(
            'The Alternative PHP Cache (APC) is highly recommended for high-performance scenario. Refer to <a href="http://www.php.net/manual/en/intro.apc.php" target="_blank" title="APC introduction">APC introduction</a> for details.'
        ),
    ],
    'redis'     => [
        'title'   => _s('Redis'),
        'message' => _s(
            'The extension is highly recommended for performance scenario and advanced data structure. Refer to <a href="http://redis.io" target="_blank" title="Redis">Redis page</a> for details.'
        ),
    ],
    'memcached' => [
        'title'   => _s('Memcached'),
        'message' => _s(
            'Memcached is highly recommended for high-performance yet robust distributed scenario. However it is not suitable for shared-hosting usage since tag is not supported. Refer to <a href="http://www.php.net/manual/en/intro.memcached.php" target="_blank" title="Memcached introduction">Memcached introduction</a> for details.'
        ),
    ],
    'memcache'  => [
        'title'   => _s('Memcache'),
        'message' => _s(
            'Memcache a widely used cache engine. However it is not suitable for shared-hosting usage since tag is not supported. Refer to <a href="http://www.php.net/manual/en/intro.memcache.php" target="_blank" title="Memcache introduction">Memcache introduction</a> for details.'
        ),
    ],
    'curl'      => [
        'title'   => _s('cURL'),
        'message' => _s(
            'The extension allows to connect and communicate to different types of servers with different types of protocols, check <a href="http://www.php.net/manual/en/book.curl.php" title="cURL" target="_blank">cURL</a> for details.'
        ),
    ],
    'intl'      => [
        'title'   => _s('Intl'),
        'message' => _s(
            'Internationalization extension enables collation and date/time/number/currency formatting, check <a href="http://www.php.net/manual/en/book.intl.php" title="Internationalization extension" target="_blank">Internationalization extension</a> for details.'
        ),
    ],
    /* 'discount'  => [
        'title'   => _s('Markdown'),
        'message' => _s(
            'The extension is suggested for text parser, check <a href="http://daringfireball.net/projects/markdown/" title="Markdown project" target="_blank">Markdown</a> for details and download from <a href="http://pecl.php.net/package/markdown" title="PECL project" target="_blank">PECL page</a>.'
        ),
    ], */
    'mbstring'  => [
        'title'   => _s('Mbstring'),
        'message' => _s(
            'The extension is required for multibyte string processing, check <a href="http://www.php.net/manual/en/book.mbstring.php" title="Multibyte String" target="_blank">Multibyte String</a> for details.'
        ),
    ],
];

// setup config site info
//$configs['db_types']  = array('mysql');

// Directories
$configs['paths'] = [
    'lib'    => [
        'path' => ['../lib', 'lib'],
        'url'  => false,
    ],
    'var'    => [
        'path' => ['../var', 'var'],
        'url'  => false,
    ],
    'usr'    => [
        'path' => ['../usr', 'usr'],
        'url'  => false,
    ],
    // To remove?
    'static' => [
        'path' => ['static', '../static'],
        'url'  => [
            '%www/static',
            //'http://static.' . preg_replace('/^(www\.)/i', '', $_SERVER['HTTP_HOST']),
        ],
    ],
    'upload' => [
        'path' => ['upload', '../upload'],
        'url'  => [
            '%www/upload',
            //'http://upload.' . preg_replace('/^(www\.)/i', '', $_SERVER['HTTP_HOST']),
        ],
    ],
];

// Skip URL validation
// Set to true for performance
$configs['skip_url_validate'] = false;

// Writable files and directories prior to installation
$configs['writable']['www']    = ['asset', '.htaccess', 'boot.php'];
$configs['writable']['var']    = '';
$configs['writable']['upload'] = '';

// Readonly files and directories after installation
$configs['readonly']['www'] = ['.htaccess', 'boot.php'];

// DB config for DSN
$configs['database']['charset'] = 'utf8mb4';
$configs['database']['collate'] = 'utf8mb4_general_ci';

return $configs;
