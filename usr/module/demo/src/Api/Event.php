<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/**
 * Demo for event/listener hook
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Event extends AbstractApi
{
    public function message($data)
    {
        d("Called by {$this->module} through " . __METHOD__);
    }

    public function selfcall($data)
    {
        d("Called by {$this->module} through " . __METHOD__);
    }

    public function moduleupdate($data)
    {
        Pi::service('log')->log(
            "Called by {$this->module} through " . __METHOD__
        );
    }

    public function moduleinstall($data)
    {
        Pi::service('log')->log(
            "Called by {$this->module} through " . __METHOD__
        );
    }

    public function runtime($data)
    {
        Pi::service('log')->log(
            "Called by {$this->module} through " . __METHOD__
        );
    }

    public function register($data)
    {
        _e("Called by {$this->module} through " . __METHOD__);
    }
}
