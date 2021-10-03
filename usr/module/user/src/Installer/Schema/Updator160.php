<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Installer\Schema;

use Pi;
use Pi\Application\Installer\Schema\AbstractUpdator;

/**
 * Module schema update handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Updator160 extends AbstractUpdator
{
    /**
     * Update system table schema
     *
     * @param string $version
     *
     * @return bool
     */
    public function upgrade($version)
    {
        if (version_compare($version, '1.3.0', '<')) {
            $updator = new Updator130($this->handler);
            $result  = $updator->upgrade($version);
            if (false === $result) {
                return $result;
            }
        }

        return $this->from160($version);
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from160($version)
    {
        // Set status
        $status =  true;

        if (version_compare($version, '1.6.0', '<')) {
            $model    = Pi::model('profile', 'user');
            $table    = Pi::db()->prefix('profile', 'user');
            $adapter  = $model->getAdapter();
            $metadata = new \Laminas\Db\Metadata\Metadata($adapter);
            $adapter->getDriver()->getConnection()->disconnect();
            $columns = $metadata->getColumns($table);

            $col = null;
            foreach ($columns as $column) {
                if ($column->getName() == 'location_city') {
                    $col = $column;
                    break;
                }
            }
            if ($col != null) {
                $sql    = sprintf('UPDATE %s SET city=location_city where city IS NULL OR city = ""', $table);
                $status = $this->queryTable($sql);

                if (false === $status) {
                    return $status;
                }
            }

            $col = null;
            foreach ($columns as $column) {
                if ($column->getName() == 'location_country') {
                    $col = $column;
                    break;
                }
            }
            if ($col != null) {
                $sql    = sprintf('UPDATE %s SET country=location_country where country IS NULL OR country = ""', $table);
                $status = $this->queryTable($sql);

                if (false === $status) {
                    return $status;
                }
            }

            $table  = Pi::db()->prefix('display_field', 'user');
            $sql    = sprintf('DELETE FROM %s WHERE field="location_city";', $table);
            $status = $this->queryTable($sql);

            if (false === $status) {
                return $status;
            }
            $sql    = sprintf('DELETE FROM %s WHERE field="location_country";', $table);
            $status = $this->queryTable($sql);

            if (false === $status) {
                return $status;
            }

            $table  = Pi::db()->prefix('field', 'user');
            $sql    = sprintf('DELETE FROM %s WHERE name="location_city";', $table);
            $status = $this->queryTable($sql);

            if (false === $status) {
                return $status;
            }
            $sql    = sprintf('DELETE FROM %s WHERE name="location_country";', $table);
            $status = $this->queryTable($sql);

            if (false === $status) {
                return $status;
            }
        }

        return $status;
    }
}
