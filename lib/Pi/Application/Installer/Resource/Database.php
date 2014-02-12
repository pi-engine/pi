<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Installer\Resource;

use Pi;
use Pi\Application\Installer\SqlSchema;

/**
 * Database setup
 *
 * SQL file format
 *
 * <pre>
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
 * </pre>
 *
 * Translated format:
 *
 *  - global prefix 'pi_'
 *  - module demo prefix 'demo_'
 *  - system prefix 'core_'
 *
 * <pre>
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
 * </pre>
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Database extends AbstractResource
{
    /**
     * Canonize config
     *
     * @param array|string $config
     *
     * @return array
     */
    protected function canonizeConfig($config)
    {
        if (is_string($config)) {
            $config = array('sqlfile' => $config);
        }

        return $config;
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        if (empty($this->config)) {
            return;
        }

        $config = $this->canonizeConfig($this->config);
        if (empty($config['sqlfile'])) {
            return;
        }
        $module = $this->event->getParam('module');
        $sqlFile = sprintf(
            '%s/%s/%s',
            Pi::path('module'),
            $this->event->getParam('directory'),
            $config['sqlfile']
        );
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

        if (isset($config['schema'])) {
            $schemaList = $config['schema'];
        } else {
            $schemaList = SqlSchema::fetchSchema($sqlFile);
        }
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
     * {@inheritDoc}
     *
     * Module database table list is supposed to be updated
     * during module upgrade, however we don't have a feasible solution yet.
     * Thus module developers are encouraged to use $config['schema']
     */
    public function updateAction()
    {
        if ($this->skipUpgrade()) {
            return;
        }
        $module = $this->event->getParam('module');

        $config = $this->canonizeConfig($this->config);
        if (isset($config['schema'])) {
            $schemaList = $config['schema'];
        } elseif (empty($config['sqlfile'])) {
            $schemaList = array();
        } else {
            $sqlFile = sprintf(
                '%s/%s/%s',
                Pi::path('module'),
                $this->event->getParam('directory'),
                $config['sqlfile']
            );
            if (!file_exists($sqlFile)) {
                $schemaList = array();
            } else {
                $schemaList = SqlSchema::fetchSchema($sqlFile);
            }
        }

        $modelSchema = Pi::model('module_schema');
        $rowset = $modelSchema->select(array('module' => $module));
        foreach ($rowset as $row) {
            $name = $row->name;
            if (!isset($schemaList[$name])) {
                $row->delete();
                $status = true;
                if (!$status) {
                    $msg = 'Deprecated schema "%s" is not removed.';
                    return array(
                        'status'    => false,
                        'message'   => sprintf($msg, $name),
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

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');
        $modelSchema = Pi::model('module_schema');
        $rowset = $modelSchema->select(array('module' => $module));
        foreach ($rowset as $table) {
            $sql = sprintf(
                'DROP %s IF EXISTS %s',
                $table->type,
                Pi::db()->prefix($table->name, $module)
            );
            try {
                Pi::db()->adapter()->query($sql, 'execute');
            } catch (\Exception $e) {
            }
        }
        $modelSchema->delete(array('module' => $module));

        return true;
    }
}
