<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * Pi Engine specifications
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        // development - for developers
        'environment'   => '',

        // Site close for maintenance
        'site_close'    => 0,

        // Disable admin login
        'admin_disable' => 0,
    ),

    // System persist storage configs
    'persist'   => array(
        'storage'   => 'filesystem',
        'namespace' => 'apcns',
        'options'   => array(
        ),
    ),
);
