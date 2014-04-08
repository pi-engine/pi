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
use Module\System\Controller\ComponentController as BasicController;

/**
 * Component controller placeholder
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ComponentController extends BasicController
{
    /**
     * Dashboard
     *
     * @return void
     */
    public function indexAction()
    {
        //$module = $this->params('module');
        $module = $this->params('name', $this->moduleName('system'));

        // Jump to a specific component if available
        $navConfig = Pi::registry('navigation')->read('system-component');
        if ($navConfig) {
            foreach ($navConfig as $key => $config) {
                $page = $config;
                break;
            }
            $this->redirect($page['route'], array(
                'module' => $page['module'],
                'controller'    => $page['controller'],
                'name'          => $module,
            ));
        }

        // Display module dashboard
        $directory = Pi::service('module')->directory($module);
        $callback = sprintf(
            'Module\\%s\Dashboard::summary',
            ucfirst($directory)
        );
        $summary = '';
        if (is_callable($callback)) {
            $summary = call_user_func($callback, $module);
        }

        $modules = Pi::registry('modulelist')->read();
        $data = $modules[$module];
        $meta = Pi::service('module')->loadMeta($directory, 'meta');
        $author = Pi::service('module')->loadMeta($directory, 'author');
        $data['description'] = $meta['description'];
        $data['author'] = $author;
        /*
        if (empty($meta['logo'])) {
            $data['logo'] = Pi::url('static/image/module.png');
        } else {
            $data['logo'] = Pi::service('asset')->getModuleAsset(
                $meta['logo'],
                $module
            );
        }
        */
        if (empty($data['update'])) {
            $data['update'] = _a('Never updated.');
        } else {
            $data['update'] = _date($data['update']);
        }

        $this->view()->assign('summary', $summary);
        $this->view()->assign('data', $data);
        $this->view()->assign('title', _a('Dashboard'));
        $this->view()->setTemplate('dashboard-module', 'system');
    }
}
