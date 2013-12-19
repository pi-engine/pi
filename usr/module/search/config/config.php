<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

$config = array(
    'leading_limit' => array(
        'title'         => _t('Leading search result limit'),
        'description'   => _t('Number of found items on leading page.'),
        'value'         => 5,
        'filter'        => 'int',
    ),

    'list_limit' => array(
        'title'         => _t('List search result limit'),
        'description'   => _t('Number of found items on list page.'),
        'value'         => 20,
        'filter'        => 'int',
    ),

    'min_length' => array(
        'title'         => _t('Minimum query'),
        'description'   => _t('Minimum length for query term.'),
        'value'         => 3,
        'filter'        => 'int',
    ),

    'logging'   => array(
        'title'         => _t('Log search terms'),
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'int',
    ),

    'cache'     => array(
        'title'         => _t('Search result cache'),
        'edit'          => 'cache_ttl',
        'value'         => 0,
    ),

    'hot'       => array(
        'title'         => _t('Hot search'),
        'description'   => _t('Hot words for global auto-complete, separated by comma `,`.'),
        'edit'          => 'textarea',
        'value'         => 'pi engine, zend framework, php, search',
    ),

    'google'       => array(
        'title'         => _t('Google Search Code'),
        'description'   => _t('GSC custom search provided by Google at https://www.google.com/cse/'),
    ),
);

return $config;