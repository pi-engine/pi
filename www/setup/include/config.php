<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

$configs = array();

// Server settings
$configs['system'] = array(
    'server'    => _s('Web server'),
    'php'       => _s('PHP'),
    'persist'   => _s('Persist options'),
    'pdo'       => _s('PDO drivers'),
);

// PHP extensions
$configs['extension'] = array(
    'apc'       => array(
        'title'     => _s('APC'),
        'message'   => _s('The Alternative PHP Cache (APC) is highly recommended for high-performance scenario. Refer to <a href="http://www.php.net/manual/en/intro.apc.php" target="_blank" title="APC introduction">APC introduction</a> for details.'),
    ),
    'redis'     => array(
        'title'     => _s('Redis'),
        'message'   => _s('The extension is highly recommended for performance scenario and advanced data structure. Refer to <a href="http://redis.io" target="_blank" title="Redis">Redis page</a> for details.'),
    ),
    'memcached' => array(
        'title'     => _s('Memcached'),
        'message'   => _s('Memcached is highly recommended for high-performance yet robust distributed scenario. However it is not suitable for shared-hosting usage since tag is not supported. Refer to <a href="http://www.php.net/manual/en/intro.memcached.php" target="_blank" title="Memcached introduction">Memcached introduction</a> for details.'),
    ),
    'memcache'  => array(
        'title'     => _s('Memcache'),
        'message'   => _s('Memcache a widely used cache engine. However it is not suitable for shared-hosting usage since tag is not supported. Refer to <a href="http://www.php.net/manual/en/intro.memcache.php" target="_blank" title="Memcache introduction">Memcache introduction</a> for details.'),
    ),
    'curl'      => array(
        'title'     => _s('cURL'),
        'message'   => _s('The extension allows to connect and communicate to different types of servers with different types of protocols, check <a href="http://www.php.net/manual/en/book.curl.php" title="cURL" target="_blank">cURL</a> for details.'),
    ),
    'intl'      => array(
        'title'     => _s('Intl'),
        'message'   => _s('Internationalization extension enables collation and date/time/number/currency formatting, check <a href="http://www.php.net/manual/en/book.intl.php" title="Internationalization extension" target="_blank">Internationalization extension</a> for details.'),
    ),
    'discount'  => array(
        'title'     => _s('Markdown'),
        'message'   => _s('The extension is suggested for text parser, check <a href="http://daringfireball.net/projects/markdown/" title="Markdown project" target="_blank">Markdown</a> for details and download from <a href="http://pecl.php.net/package/markdown" title="PECL project" target="_blank">PECL page</a>.'),
    ),
    'mbstring'  => array(
        'title'     => _s('Mbstring'),
        'message'   => _s('The extension is required for multibyte string processing, check <a href="http://www.php.net/manual/en/book.mbstring.php" title="Multibyte String" target="_blank">Multibyte String</a> for details.'),
    ),
);

// setup config site info
//$configs['db_types']  = array('mysql');

// Directories
$configs['paths'] = array(
    'lib'           => array(
        'path'  => array('../lib', 'lib'),
        'url'   => false,
    ),
    'var'           => array(
        'path'  => array('../var', 'var'),
        'url'   => false,
    ),
    'usr'       => array(
        'path'  => array('../usr', 'usr'),
        'url'   => false,
    ),
    // To remove?
    'static'        => array(
        'path'  => array('static', '../static'),
        'url'   => array(
            '%www/static',
            //'http://static.' . preg_replace('/^(www\.)/i', '', $_SERVER['HTTP_HOST']),
        ),
    ),
    'upload'        => array(
        'path'  => array('upload', '../upload'),
        'url'   => array(
            '%www/upload',
            //'http://upload.' . preg_replace('/^(www\.)/i', '', $_SERVER['HTTP_HOST']),
        ),
    ),
);

// Skip URL validation
// Set to true for performance
$configs['skip_url_validate'] = false;

// Writable files and directories prior to installation
$configs['writable']['www'] = array('asset', '.htaccess', 'boot.php');
$configs['writable']['var'] = '';
$configs['writable']['upload'] = '';

// Readonly files and directories after installation
$configs['readonly']['www'] = array('.htaccess', 'boot.php');

// DB config for DSN
$configs['database']['charset'] = 'utf8';
$configs['database']['collate'] = 'utf8_general_ci';

return $configs;
