<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace   Module\System\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Zend\EventManager\Event;
use Pi\Application\Installer\SqlSchema;

/**
 * Module update handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Update extends BasicUpdate
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('update.pre', array($this, 'updateSchema'));
        $events->attach('update.post', array($this, 'updateConfig'));
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Update config
     *
     * @param Event $e
     */
    public function updateConfig(Event $e)
    {
        $model = Pi::model('update', $this->module);
        $data = array(
            'title'     => __('System updated'),
            'content'   => __('The system is updated successfully.'),
            'uri'       => Pi::url('www', true),
            'time'      => time(),
        );
        $model->insert($data);
    }

    /**
     * Update module table schema
     *
     * @param Event $e
     * @return bool
     */
    public function updateSchema(Event $e)
    {
        $moduleVersion = $e->getParam('version');

        if (version_compare($moduleVersion, '3.1.1', '<')):

        // Add table of navigation data
        $sql =<<<'EOD'
CREATE TABLE `{core.module_dependency}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `dependent`       varchar(64)     NOT NULL,
  `independent`     varchar(64)     NOT NULL,

  PRIMARY KEY  (`id`)
);
EOD;
        $sqlHandler = new SqlSchema;
        try {
            $sqlHandler->queryContent($sql);
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'SQL schema query failed: '
                    . $exception->getMessage(),
            ));

            return false;
        }

        endif;

        if (version_compare($moduleVersion, '3.1.0', '<')):

        $sqlHandler = new SqlSchema;
        $adapter = Pi::db()->getAdapter();

        // Change fields from 'tinytext' to 'text'
        $table = Pi::model('config')->getTable();
        $sql = sprintf('ALTER TABLE %s MODIFY `edit` text', $table);
        try {
            $adapter->query($sql, 'execute');
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'Table alter query failed: '
                    . $exception->getMessage(),
            ));

            return false;
        }

        $table = Pi::model('user_meta')->getTable();
        foreach (array('edit', 'admin', 'search', 'options') as $field) {
            $sql = sprintf("ALTER TABLE %s MODIFY `{$field}` text", $table);
            try {
                $adapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status'    => false,
                    'message'   => 'Table alter query failed: '
                        . $exception->getMessage(),
                ));

                return false;
            }
        }

        endif;


        if (version_compare($moduleVersion, '3.0.0-beta.3', '<')):

        // Add table of navigation data
        $sql =<<<'EOD'
CREATE TABLE `{core.navigation_node}` (
  `id`              int(10)         unsigned    NOT NULL auto_increment,
  `navigation`      varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `data`            text,

  PRIMARY KEY  (`id`)
);
EOD;
        $sqlHandler = new SqlSchema;
        try {
            $sqlHandler->queryContent($sql);
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'SQL schema query failed: '
                    . $exception->getMessage(),
            ));

            return false;
        }

        // Drop not used table
        $tables = array(
            Pi::model('monitor')->getTable(),
            Pi::model('navigation_page')->getTable(),
        );
        $adapter = Pi::db()->getAdapter();
        foreach ($tables as $table) {
            try {
                $sql = sprintf('DROP TABLE IF EXISTS %s', $table);
                $adapter->query($sql, 'execute');
            } catch (\Exception $exception) {
                $this->setResult('db', array(
                    'status'    => false,
                    'message'   => 'Table drop failed: '
                        . $exception->getMessage(),
                ));

                return false;
            }
        }

        endif;


        if (version_compare($moduleVersion, '3.0.1', '<')):

        $sqlHandler = new SqlSchema;
        $adapter = Pi::db()->getAdapter();

        // Add table field `section` to table acl_role
        $table = Pi::model('acl_role')->getTable();
        $sql = sprintf('ALTER TABLE %s ADD `section` varchar(64) NOT NULL'
                . ' default \'front\' AFTER `module`',
            $table);
        try {
            $adapter->query($sql, 'execute');
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'Table alter query failed: '
                    . $exception->getMessage(),
            ));

            return false;
        }

        // Update table acl_resource
        $table = Pi::model('acl_resource')->getTable();
        $sql = sprintf('ALTER TABLE %s DROP `item`', $table);
        try {
            $adapter->query($sql, 'execute');
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'Table alter query failed: '
                    . $exception->getMessage(),
            ));

            return false;
        }
        $sql = sprintf('ALTER TABLE %s DROP KEY `pair`', $table);
        try {
            $adapter->query($sql, 'execute');
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'Table alter query failed: '
                    . $exception->getMessage(),
            ));

            return false;
        }
        $sql = sprintf(
            'ALTER TABLE %s ADD KEY `pair`'
                . ' UNIQUE KEY (`section`, `module`, `name`)',
            $table
        );
        try {
            $adapter->query($sql, 'execute');
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'Table alter query failed: '
                    . $exception->getMessage(),
            ));

            return false;
        }

        // Update table for audit
        $table = Pi::model('audit')->getTable();
        try {
            $sql = sprintf('DROP TABLE IF EXISTS %s', $table);
            $adapter->query($sql, 'execute');
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'Table drop failed: '
                    . $exception->getMessage(),
            ));

            return false;
        }

        $sql =<<<'EOD'
CREATE TABLE `{core.audit}` (
  `id`              int(10)         unsigned NOT NULL auto_increment,
  `user`            int(10)         unsigned NOT NULL    default '0',
  `ip`              varchar(15)     NOT NULL    default '',
  `section`         varchar(64)     NOT NULL    default '',
  `module`          varchar(64)     NOT NULL    default '',
  `controller`      varchar(64)     NOT NULL    default '',
  `action`          varchar(64)     NOT NULL    default '',
  `method`          varchar(64)     NOT NULL    default '',
  `message`         text,
  `extra`           text,
  `time`            int(10)         unsigned NOT NULL   default '0',

  PRIMARY KEY  (`id`)
);
EOD;
        try {
            $sqlHandler->queryContent($sql);
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'SQL schema query failed: '
                    . $exception->getMessage(),
            ));

            return false;
        }

        // Add table of user staff role
        $sql =<<<'EOD'
CREATE TABLE `{core.user_staff}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `user`            int(10)         unsigned    NOT NULL,
  `role`            varchar(64)     NOT NULL    default '',

  PRIMARY KEY  (`id`),
  UNIQUE KEY `user` (`user`)
);
EOD;
        try {
            $sqlHandler->queryContent($sql);
        } catch (\Exception $exception) {
            $this->setResult('db', array(
                'status'    => false,
                'message'   => 'SQL schema query failed: '
                    . $exception->getMessage(),
            ));

            return false;
        }

        $rowset = Pi::model('user_role')->select(
            array('role <> ?' => 'member')
        );
        $modelStaff = Pi::model('user_staff');
        foreach ($rowset as $row) {
            $modelStaff->insert(array(
                'user'  => $row->user,
                'role'  => $row->role,
            ));
            $row->delete();
        }

        endif;
    }
}
