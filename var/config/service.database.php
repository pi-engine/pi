<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * Pi Engine database connection specifications
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

return [
    // Connection configs, to be passed to driver
    'connection'   => [
        'driver'         => 'pdo',
        'dsn'            => 'mysql:host=localhost;dbname=pi',
        'username'       => 'root',
        'password'       => '',

        // driver_options. All attributes must be valid.
        // @see http://www.php.net/manual/en/pdo.setattribute.php
        'driver_options' => [
            PDO::MYSQL_ATTR_INIT_COMMAND =>
                'SET NAMES utf8 COLLATE utf8_general_ci',
            PDO::ATTR_PERSISTENT         => false,

            // Custom PDOstatement class.
            // Optional, default as Pi\Db\Adapter\Driver\Statement
            // PDO::ATTR_STATEMENT_CLASS       => array('PDOstatement'),
        ],

        // Add custom options in this section
        'options'        => [
        ],
    ],

    // Database schema
    'schema'       => 'pi',
    // Prefix for all tables
    'table_prefix' => 'pcc5_',
    // Prefix for system tables
    // module identifiers will be used for its tables, respectively
    'core_prefix'  => 'core_',
];
