<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Uclient\Controller\Front;

use Pi;
use Module\System\Controller\Front\PasswordController as SystemController;

/**
 * Password controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PasswordController extends SystemController
{
    /**
     * Load system translations
     * {@inheritDoc}
     */
    protected function preAction($e)
    {
        Pi::service('i18n')->loadModule('default', 'system');
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function checkAccess()
    {
        return true;
    }
}
