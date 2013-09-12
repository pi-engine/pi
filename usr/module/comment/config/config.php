<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

$config = array(
    'comment_limit' => array(
        'title'         => _t('Number of comments to show'),
        'value'         => 5,
        'filter'        => 'int',
    ),

    'auto_approval'  => array(
        'title'         => _t('Auto approved after post'),
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'int',
    ),
);

return $config;