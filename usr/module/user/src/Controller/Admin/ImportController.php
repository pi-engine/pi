<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Application\Installer\Resource\User as UserInstaller;

/**
 * Custom compound controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ImportController extends ActionController
{
    /**
     * Entrance template
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view()->setTemplate('import');
    }

    /**
     * Do import
     */
    public function doAction()
    {
        $metaFile = Pi::path('custom/module/user/config/user.php');
        $meta = include $metaFile;

        try {
            $resourceHandler = new UserInstaller($meta);
            $resourceHandler->updateAction(true);

            Pi::registry('field', 'user')->clear();
            Pi::registry('compound_field', 'user')->clear();

            $message = _a('User data imported successfully.');
        } catch (\Exception $e) {
            $message = _a('User data import failed: ' . $e->getMessage());
        }

        $this->jump(array('action' => 'index'), $message);
    }
}
