<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Placeholder for not defined controllers
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class IndexController extends ActionController
{
    /**
     * Default action if none provided
     *
     * @return void
     */
    public function indexAction()
    {
        // Security check for setup folder
        if (is_dir(Pi::path('setup'))) {
            $this->flashMessenger(_a('Security: `setup` folder is not removed!'), 'error');
        }

        // Security check for boot file
        $fileList = array('boot.php', '.htaccess');
		foreach ($fileList as $file) {
            $path = Pi::path($file);
            if (file_exists($path) && is_writable($path)) {
                $this->flashMessenger(sprintf(_a('Security: `%s` is writable!'), $file), 'error');
            }
        }

        // Write permission check
        $folderList = array('var', 'upload', 'asset', 'config', 'cache');
        foreach ($folderList as $folder) {
            $path = Pi::path($folder);
            if (!is_dir($path) || !is_writable($path)) {
                $this->flashMessenger(sprintf(_a('Permission: `%s` is not available for write!'), $folder), 'error');
            }
        }

        return $this->redirect()->toRoute('', array(
            'controller' => 'dashboard'
        ));
    }
}
