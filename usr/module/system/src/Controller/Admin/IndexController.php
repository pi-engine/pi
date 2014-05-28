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
            $pattern = _a('Security: `setup` folder is not removed!');
            $this->flashMessenger($pattern, 'warning');
        }

        // Security check for boot file
        $fileList = array('boot.php', '.htaccess');
        $pattern = _a('Security: `%s` is writable!');
        foreach ($fileList as $file) {
            $path = Pi::path($file);
            if (file_exists($path) && is_writable($path)) {
                $this->flashMessenger(sprintf($pattern, $file), 'error');
            }
        }

        // Write permission check
        $folderList = array('var', 'upload', 'asset', 'config', 'cache');
        $pattern = _a('Permission: `%s` is not available for write!');
        foreach ($folderList as $fodler) {
            $path = Pi::path($fodler);
            if (!is_dir($path) || !is_writable($path)) {
                $this->flashMessenger(sprintf($pattern, $fodler), 'error');
            }
        }

        return $this->redirect()->toRoute('', array(
            'controller' => 'dashboard'
        ));
    }
}
