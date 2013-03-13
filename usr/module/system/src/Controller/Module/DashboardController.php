<?php
/**
 * Action controller class
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\System\Controller\Module;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Dashboard action controller
 */
class DashboardController extends ActionController
{
    public function modeAction()
    {
        $mode = $this->params('mode', 'admin');
        // Set run mode
        if (!empty($mode)) {
            /*
            Pi::service('session')->backoffice->exchangeArray(array(
                'mode'      => $mode,
                'changed'   => 1,
            ));
            */
            $_SESSION['PI_BACKOFFICE'] = array(
                'mode'      => $mode,
                'changed'   => 1,
            );

        }

        $modules = Pi::service('registry')->modulelist->read();
        $moduleList = array_keys($modules);
        $allowed = Pi::service('registry')->moduleperm->read($mode);
        if (null === $allowed || !is_array($allowed)) {
            $allowed = $moduleList;
        } else {
            $allowed = array_intersect($moduleList, $allowed);
        }
        if (!$allowed) {
            $this->redirect('', array('action' => 'system'));
            return;
        }

        $name = array_shift($allowed);
        $link = '';
        switch ($mode) {
            case 'admin':
                $link = $this->url('admin', array(
                    'module'        => $name,
                    'controller'    => 'dashboard',
                    //'mode'          => $mode,
                ));
                break;
            case 'manage':
                $controller = '';
                $navConfig = Pi::service('registry')->navigation->read('system-component');
                foreach ($navConfig as $key => $item) {
                    if (!isset($item['visible']) || $item['visible']) {
                        $controller = $item['controller'];
                        break;
                    }
                }
                if ($controller) {
                    $link = $this->url('admin', array(
                        'module'        => 'system',
                        'controller'    => $controller,
                        'name'          => $name,
                    ));
                }
                break;
            case 'deployment':
            default:
                break;
        }
        if (!$link) {
            $this->jump(array('action' => 'system'), __('No permitted operation available.'));
            return;
        }
        //d($link);exit;
        $this->redirect()->toUrl($link);

        return;
    }

    /**
     * Default action for site admin entry
     *
     * @return ViewModel
     */
    public function systemAction()
    {
        $module = $this->params('module');
        $user   = Pi::registry('user')->id;
        //$mode = $this->params('mode', '');

        /*
        // Set run mode
        if (!empty($mode)) {
            Pi::service('session')->backoffice->exchangeArray(array(
                'mode'      => 'operation' == $mode ? 'operation' : 'manage',
                'component' => '',
                'module'    => '',
            ));
        }
        */
        /*
        Pi::service('session')->backoffice->exchangeArray(array(
            'mode'      => '',
            'changed'   => 1,
            'component' => '',
            'module'    => '',
        ));
        */
        $_SESSION['PI_BACKOFFICE'] = array(
            'mode'      => '',
            'changed'   => 1,
            'component' => '',
            'module'    => '',
        );

        // Fetch all permitted modules
        $modules = Pi::service('registry')->modulelist->read('active');
        $modulesPermitted = Pi::service('registry')->moduleperm->read('admin');
        foreach (array_keys($modules) as $name) {
            if (null !== $modulesPermitted && !in_array($name, $modulesPermitted)) {
                unset($modules[$name]);
            }
        }

        // Get module summary callbacks
        // Get hidden modules
        $summaryList = array();
        $list = array();
        $row = Pi::model('user_repo')->select((array('user' => $user, 'module' => 'system', 'type' => 'module-summary')))->current();
        if ($row) {
            $list = (array) $row->content;
        }

        $summaryEnabled = array();
        $summaryHidden = array();
        // Enabled explicitly
        if (isset($list['active'])) {
            $summaryEnabled = array_intersect((array) $list['active'], array_keys($modules));
            $summaryEnabled = array_unique($summaryEnabled);
        }
        // Disabled expicitly
        if (isset($list['inactive'])) {
            $summaryHidden = array_intersect((array) $list['inactive'], array_keys($modules));
            $summaryHidden = array_unique($summaryHidden);
        }
        $new = $list ? array_diff(array_keys($modules), $summaryEnabled, $summaryHidden) : array_keys($modules);
        $keys = array_unique($summaryEnabled + $new);

        foreach ($keys as $name) {
            $callback = sprintf('Module\\%s\\Dashboard::summary', ucfirst($modules[$name]['directory']));
            if (is_callable($callback)) {
                $summaryList[] = array(
                    'name'      => $name,
                    'content'   => call_user_func($callback, $name),
                    'title'     => $modules[$name]['title'],
                    'logo'      => $modules[$name]['logo'],
                    'active'    => 1
                );
            }
        }
        foreach ($summaryHidden as $name) {
            $callback = sprintf('Module\\%s\\Dashboard::summary', ucfirst($modules[$name]['directory']));
            if (is_callable($callback)) {
                $summaryList['inactive'][] = array(
                    'name'      => $name,
                    'title'     => $modules[$name]['title'],
                    'active'    => 0
                );
            }
        }

        // Get user quick links
        $links = array();
        $row = Pi::model('user_repo')->select((array('user' => $user, 'module' => 'system', 'type' => 'admin-link')))->current();
        if ($row) {
            $links = (array) $row->content;
        }

        /*
        // Get personal memo
        $memo = array();
        $row = Pi::model('user_repo')->select((array('user' => $user, 'module' => 'system', 'type' => 'admin-memo')))->current();
        if ($row) {
            $content = $row->content;
            $memo = array(
                'time'      => date('Y/m/d H:i:s', $content['time']),
                'content'   => Pi::service('markup')->render($content['content'], 'text'),
            );
        }
        */

        // Get system message, only admins have access
        $message = array();
        $row = Pi::model('user_repo')->select((array('module' => 'system', 'type' => 'admin-message')))->current();
        if (!$row || !$row->content) {
            $row = Pi::model('user_repo')->select((array('module' => 'system', 'type' => 'admin-welcome')))->current();
        }
        $content = $row->content;
        $message = array(
            'time'      => date('Y/m/d H:i:s', $content['time']),
            'content'   => Pi::service('markup')->render($content['content'], 'text'),
        );
        $messagePerm = false;
        if (Pi::registry('user')->isAdmin()) {
            $messagePerm = true;
        }

        //$this->view()->assign('module', $module);
        //$this->view()->assign('modules', $moduleList);
        $this->view()->assign('summaryList', $summaryList);
        //$this->view()->assign('monitors', $monitorsEnabled);
        $this->view()->assign('links', $links);
        $this->view()->assign('message', $message);
        $this->view()->assign('messagePerm', $messagePerm);
        //$this->view()->assign('memo', $memo);

        $this->view()->assign('title', __('Dashboard'));
        $this->view()->setTemplate('dashboard-system', 'system');
    }

    /**
     * Entry page for module admin
     */
    public function indexAction()
    {
        $module = $this->params('module');
        if (!$module) {
            $this->redirect('', array('action' => 'system'));
            return;
        }

        $directory = Pi::service('module')->directory($module);
        $callback = sprintf('Module\\%s\\Dashboard::summary', ucfirst($directory));
        $summary = '';
        if (is_callable($callback)) {
            $summary = call_user_func($callback, $module);
        }

        $modules = Pi::service('registry')->modulelist->read();
        $data = $modules[$module];
        $meta = Pi::service('module')->loadMeta($directory, 'meta');
        $author = Pi::service('module')->loadMeta($directory, 'author');
        $data['description'] = $meta['description'];
        $data['author'] = $author;
        if (empty($meta['logo'])) {
            $data['logo'] = Pi::url('static/image/module.png');
        } else {
            $data['logo'] = Pi::service('asset')->getModuleAsset($meta['logo'], $module, false);
        }
        if (empty($data['update'])) {
            $data['update'] = __('Never updated.');
        } else {
            $data['update'] = date('Y-m-d H:i:s', $data['update']);
        }

        $this->view()->assign('summary', $summary);
        $this->view()->assign('data', $data);
        $this->view()->assign('title', __('Dashboard'));
        $this->view()->setTemplate('dashboard-module', 'system');
    }

    /**
     * AJAX method for module admin entries
     *
     * @return int
     */
    public function entryAction()
    {
        $this->saveAjax('module-admin');

        return 1;
    }

    /**
     * AJAX method for module summary list
     *
     * @return int
     */
    public function summaryAction()
    {
        $this->saveAjax('module-summary');

        return 1;
    }

    /**
     * AJAX method for adding a module summary
     *
     * @return int
     */
    public function getSummaryAction()
    {
        $this->saveAjax('module-summary');
        $name = $this->params()->fromPost('name');

        $directory = Pi::service('module')->directory($name);
        $callback = sprintf('Module\\%s\\Dashboard::summary', ucfirst($directory));
        if (is_callable($callback)) {
            $content = call_user_func($callback, $name);
        } else {
            $content = '';
        }
        return $content;
    }

    /**
     * AJAX method for quick links
     *
     * @return int
     */
    public function linkAction()
    {
        $this->saveAjax('admin-link');

        return 1;
    }

    /**
     * AJAX method for system message
     *
     * @return int
     */
    public function messageAction()
    {
        $type = 'admin-message';

        $data = array();
        $content = $this->params()->fromPost('content');
        if ($content) {
            $data = array(
                'content'   => $content,
                'time'      => time(),
            );
        }
        $row = Pi::model('user_repo')->select((array('module' => 'system', 'type' => $type)))->current();

        if (Pi::registry('user')->isAdmin()) {
            if ($row) {
                $row->content = $data;
            } else {
                $row = Pi::model('user_repo')->createRow(array(
                    'module'    => 'system',
                    'type'      => $type,
                    'content'   => $data,
                ));
            }
            $row->save();
        }

        if (!$data) {
            $row = Pi::model('user_repo')->select((array('module' => 'system', 'type' => 'admin-welcome')))->current();
            $data = $row->content;
        }

        $message = array(
            'time'      => date('Y/m/d H:i:s', $data['time']),
            'content'   => Pi::service('markup')->render($data['content'], 'text'),
        );

        return $message;
    }

    /**
     * AJAX method for personal memo
     *
     * @return int
     */
    public function memoAction()
    {
        $type = 'admin-memo';
        $module = $this->params('module');
        $user   = Pi::registry('user')->id;

        $content = $this->params()->fromPost('content');
        $data = array(
            'content'   => $content,
            'time'      => time(),
        );
        $row = Pi::model('user_repo')->select((array('user' => $user, 'module' => $module, 'type' => $type)))->current();
        if ($row) {
            $row->content = $data;
        } else {
            $row = Pi::model('user_repo')->createRow(array(
                'user'      => $user,
                'module'    => $module,
                'type'      => $type,
                'content'   => $data,
            ));
        }
        $row->save();

        $memo = array(
            'time'      => date('Y/m/d H:i:s', $data['time']),
            'content'   => $data['content'],
        );

        return $memo;
    }

    /**
     * Save data submitted from AJAX
     *
     * @param string $type
     * @return bool
     */
    protected function saveAjax($type)
    {
        $module = $this->params('module');
        $user   = Pi::registry('user')->id;

        $content = $this->params()->fromPost('content');
        $row = Pi::model('user_repo')->select((array('user' => $user, 'module' => $module, 'type' => $type)))->current();
        if ($row) {
            $row->content = $content;
        } else {
            $row = Pi::model('user_repo')->createRow(array(
                'user'      => $user,
                'module'    => $module,
                'type'      => $type,
                'content'   => $content,
            ));
        }
        $row->save();

        return true;
    }
}
