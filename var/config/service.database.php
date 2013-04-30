<?php
/**
 * Pi Engine database connection configuration
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

return array(
    // Connection configs, to be passed to driver
    'connection'    => array(
        'driver'    => 'pdo',
        'dsn'       => 'mysql:dbname=pi;host=localhost',
        'username'  => 'root',
        'password'  => '',

        // driver_options. All attributes must be valid.
        // @see http://www.php.net/manual/en/pdo.setattribute.php
        'driver_options'   => array(
            PDO::MYSQL_ATTR_INIT_COMMAND    => 'SET NAMES utf8 COLLATE utf8_general_ci',
            PDO::ATTR_PERSISTENT            => false,

            // Custom PDOstatement class. Optional, default as Pi\Db\Adapter\Driver\Statement
            // PDO::ATTR_STATEMENT_CLASS       => array('PDOstatement'),
        ),

        // Add custom options in this section
        'options'   => array(
        ),
    ),

    // Database schema
    'schema'        => 'pi',
    // Prefix for all tables
    'table_prefix'  => 'x2d1_',
    // Prefix for system tables; module identifiers will be used for its tables, respectively
    'core_prefix'   => 'core_'
);
