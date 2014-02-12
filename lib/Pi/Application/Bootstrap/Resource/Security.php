<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;

/**
 * Security check
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Security extends AbstractResource
{
    /**
     * Check security settings
     *
     * Strategy:
     *
     * - quit the process and approve current request if TRUE is returned;
     * - quit and deny the request if FALSE is return;
     * - continue with next check if NULL is returned
     *
     * @return void|false
     */
    public function boot()
    {
        $options = $this->options;
        foreach ($options as $type => $opt) {
            if (false === $opt) {
                continue;
            }
            $status = Pi::service('security')->{$type}($opt);
            if ($status) return true;
            if (false === $status) {
                return false;
                //Pi::service('security')->deny($type);
            }
        }
    }
}
