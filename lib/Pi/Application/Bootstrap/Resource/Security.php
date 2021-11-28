<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
        // Set security header
        Pi::service('security')->setHeaders();

        // Check methods
        $options = $this->options;
        foreach ($options as $type => $opt) {
            if (false === $opt) {
                continue;
            }

            // Check
            $status = Pi::service('security')->{$type}($opt);
            if (false === $status) {
                Pi::service('security')->deny($type);
                return false;
            }
        }

        return true;
    }
}