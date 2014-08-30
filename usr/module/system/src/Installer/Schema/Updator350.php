<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace   Module\System\Installer\Schema;

use Pi;
use Pi\Application\Installer\Schema\AbstractUpdator;

/**
 * System schema update handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Updator350 extends AbstractUpdator
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
        if (version_compare($version, '3.4.0', '<')) {
            $updator = new Updator340($this->handler);
            $result = $updator->upgrade($version);
            if (false === $result) {
                return $result;
            }
        }
        $result = $this->from340($version);

        return $result;
    }

    /**
     * Upgrade from previous version
     *
     * @param string $version
     *
     * @return bool
     */
    protected function from340($version)
    {
        $status = true;

        if (version_compare($version, '3.5.0', '<')) {
            $moduleList = Pi::registry('module')->read();
            $modules = array();
            foreach ($moduleList as $module => $data) {
                if ($module == $data['directory']) {
                    $modules[] = $module;
                }
            }
            // trim module name from non-cloned module route names
            if ($modules) {
                $update = array(
                    'name' => Pi::db()->expression("SUBSTR(name, LOCATE('-', name) + 1)"),
                );
                $where = array(
                    'module'    => $modules,
                );
                Pi::model('route')->update($update, $where);
            }
        }

        return $status;
    }
}
