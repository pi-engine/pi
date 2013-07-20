<?php
/**
 * Bootstrap resource
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application\Bootstrap\Resource;

use Pi;

class Security extends AbstractResource
{
    /**
     * Check security settings
     *
     * Policy: quit the process and approve current request if TRUE is returned by a check; quit and deny the request if FALSE is return; continue with next check if NULL is returned
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
