<?php
/**
 * Pi module installer resource
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
 * @since           3.0
 * @package         Pi\Application
 * @subpackage      Installer
 * @version         $Id$
 */

namespace Pi\Application\Installer\Resource;
use Pi;
use Pi\Application\Installer\SqlSchema;

/**
 * SQL file format
 * <code>
 *  CREATE TABLE `{test}` (
 *      `id`      int(10) unsigned        NOT NULL auto_increment,
 *      `message` varchar(255)            NOT NULL default '',
 *      PRIMARY KEY  (`id`)
 *  ) ENGINE=InnoDB;
 *
 *  CREATE TABLE `{core.systable}` (
 *      `id`      int(10) unsigned        NOT NULL auto_increment,
 *      `message` varchar(255)            NOT NULL default '',
 *      PRIMARY KEY  (`id`)
 *  ) ENGINE=InnoDB;
 * </code>
 * Translated format: global prefix 'pi_', module demo prefix 'demo_', system prefix 'core_'
 * <code>
 *  CREATE TABLE `pi_demo_test` (
 *      `id`      int(10) unsigned        NOT NULL auto_increment,
 *      `message` varchar(255)            NOT NULL default '',
 *      PRIMARY KEY  (`id`)
 *  ) ENGINE=InnoDB;
 *
 *  CREATE TABLE `pi_core_systable` (
 *      `id`      int(10) unsigned        NOT NULL auto_increment,
 *      `message` varchar(255)            NOT NULL default '',
 *      PRIMARY KEY  (`id`)
 *  ) ENGINE=InnoDB;
 * </code>
 *
 */
class Database extends AbstractResource
{
    public function installAction()
    {
        if (empty($this->config)) {
            return;
        }

        if (empty($this->config['sqlfile'])) {
            return;
        }
        $module = $this->event->getParam('module');
        $sqlFile = sprintf('%s/%s/%s', Pi::path('module'), $this->event->getParam('directory'), $this->config['sqlfile']);
        if (!file_exists($sqlFile)) {
            return array(
                'status'    => false,
                'message'   => sprintf('SQL file "%s" is not found.', $sqlFile)
            );
        }
        try {
            $status = SqlSchema::query($sqlFile, $module);
        } catch (\Exception $e) {
            return array(
                'status'    => false,
                'message'   => 'SQL schema query failed: ' . $e->getMessage()
            );
        }

        $schemaList = isset($this->config['schema']) ? $this->config['schema'] : array();
        $modelSchema = Pi::model('module_schema');
        foreach($schemaList as $name => $type) {
            $status = $modelSchema->insert(array(
                'name'      => $name,
                'type'      => $type,
                'module'    => $module
            ));
            if (!$status) {
                return array(
                    'status'    => false,
                    'message'   => 'Module schema is not saved.'
                );
            }
        }

        return true;
    }

    /**
     * Module database table list is supposed to be updated during module upgrade,
     * however we don't have a feasible solution yet. Thus module developers are encouraged to use $config['schema']
     */
    public function updateAction()
    {
        if ($this->skipUpgrade()) {
            return;
        }
        $module = $this->event->getParam('module');
        $schemaList = isset($this->config['schema']) ? $this->config['schema'] : array();
        $modelSchema = Pi::model('module_schema');
        $rowset = $modelSchema->select(array('module' => $module));
        foreach ($rowset as $row) {
            $name = $row->name;
            if (!isset($schemaList[$name])) {
                $row->delete();
                $status = true;
                if (!$status) {
                    return array(
                        'status'    => false,
                        'message'   => sprintf('Deprecated schema "%s" is not removed.', $name)
                    );
                }
            } else {
                unset($schemaList[$row->name]);
            }
        }
        foreach($schemaList as $name => $type) {
            $status = $modelSchema->insert(array(
                'name'      => $name,
                'type'      => $type,
                'module'    => $module
            ));
            if (!$status) {
                return array(
                    'status'    => false,
                    'message'   => 'Module schema is not saved.'
                );
            }
        }

        return true;
    }

    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        $modelSchema = Pi::model('module_schema');
        $rowset = $modelSchema->select(array('module' => $module));
        foreach ($rowset as $table) {
            $sql = sprintf('DROP %s IF EXISTS %s', $table->type, Pi::db()->prefix($table->name, $module));
            Pi::db()->adapter()->query($sql, 'execute');
        }
        $modelSchema->delete(array('module' => $module));
        return true;
    }
}
