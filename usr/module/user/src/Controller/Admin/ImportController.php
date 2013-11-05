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
        $customPath = Pi::path('custom_module');
        Pi::service('i18n')->load(sprintf('%s/user:main', $customPath));
        $metaFile = sprintf(
            '%s/user/config/user.php',
            $customPath
        );
        $meta = include $metaFile;

        $resourceHandler = new UserInstaller($meta);
        $resourceHandler->updateAction(true);

        Pi::registry('field', 'user')->clear();
        Pi::registry('compound_field', 'user')->clear();

        return $meta;

    }
}
