<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * Pi Engine specifications
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

// Application configs
return array(
    // application configs
    'config'    => array(
        // Site specific identifier, should not change it after installation
        'identifier'    => 'siteidentifier',

        // Salt for hashing
        'salt'          => 'bf11488eed7286c61db279f2c02af5f0',

        // Run mode. Potential values:
        // production - for production;
        // debug - for users debugging;
        // development - for developers;
        // close - for maintenance
        'environment'   => 'development',
    ),

    // System persist storage configs
    'persist'   => array(
        'storage'   => 'apc',
        'namespace' => 'apcns',
        'options'   => array(
        ),
    ),
);
