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
        'identifier'    => 'pic99e',

        // Salt for hashing
        'salt'          => '28d1baf6ad247fdea1b6d0eb8592bbd2',

        // Run mode. Potential values:
        // production - for production;
        // debug - for users debugging;
        // development - for developers;
        // close - for maintenance
        // '' - To set in system preference
        'environment'   => '',
    ),

    // System persist storage configs
    'persist'   => array(
        'storage'   => 'filesystem',
        'namespace' => 'c99e',
        'options'   => array(
        ),
    ),
);
