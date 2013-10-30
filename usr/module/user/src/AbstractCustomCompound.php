<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User;

/**
 * Abstract class for custom field handling
 *
 *
 * Skeleton
 *
 * - Specs: usr/custom/user/config/user.php
 * - Handler: usr/custom/user/src/Field/FieldName.php
 * - Form/Filter: usr/custom/user/src/Form/FieldNameForm.php
 * - schema: usr/custom/user/sql/fields.sql
 * - locale: usr/custom/user/locale/en/main.csv
 *
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractCustomCompound
{
    /**
     * Install a field
     *
     * - Create schema
     *
     * @return bool
     */
    abstract function install();

    /**
     * Remove a field
     *
     * - Drop schema
     *
     * @return bool
     */
    abstract function uninstall();

    abstract function add();
    abstract function update();
    abstract function delete();

    abstract function get();
    abstract function mget();
    abstract function display();
    abstract function edit();
    abstract function submit();
}
