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
use Pi\Mvc\Controller\ActionController;
use Pi;

/**
 * Dashboard action controller
 */
class DashboardController extends ActionController
{
    /**
     * Default action if none provided
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $module = $this->params('module');
        $user   = Pi::registry('user')->id;

        // Fetch all active modules
        $modules = Pi::service('registry')->modulelist->read('active');

        // Get personal sorted module list
        $moduleList = array();
        $row = Pi::model('user_repo')->select((array('user' => $user, 'module' => 'system', 'type' => 'module-admin')))->current();
        if ($row) {
            $list = (array) $row->content;
            foreach ($list as $name) {
                if (!isset($modules[$name])) {
                    continue;
                }
                $moduleList[$name] = array(
                    'title'     => $modules[$name]['title'],
                    'name'      => $name,
                    'logo'      => $modules[$name]['logo'],
                );
            }
        }

        // Append not sorted modules
        foreach (array_keys($modules) as $name) {
            if (isset($moduleList[$name])) {
                continue;
            }
            $moduleList[$name] = array(
                'title'     => $modules[$name]['title'],
                'name'      => $name,
                'logo'      => $modules[$name]['logo'],
            );
        }

        // Get module summary callbacks
        // Get hidden modules
        $summaryList = array(
            'active'    => array(),
            'inactive'  => array(),
        );
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
                $summaryList['active'][] = array(
                    'name'      => $name,
                    'content'   => call_user_func($callback, $name),
                    'title'     => $modules[$name]['title'],
                    'logo'      => $modules[$name]['logo'],
                );
            }
        }
        foreach ($summaryHidden as $name) {
            $callback = sprintf('Module\\%s\\Dashboard::summary', ucfirst($modules[$name]['directory']));
            if (is_callable($callback)) {
                $summaryList['inactive'][] = array(
                    'name'      => $name,
                    'title'     => $modules[$name]['title'],
                );
            }
        }

        // Get user quick links
        $links = array();
        $row = Pi::model('user_repo')->select((array('user' => $user, 'module' => 'system', 'type' => 'admin-link')))->current();
        if ($row) {
            $links = (array) $row->content;
        }

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

        // Get system message, only admins have access
        $messages = array();
        $row = Pi::model('user_repo')->select((array('module' => $module, 'type' => 'admin-message')))->current();
        if ($row) {
            $messages = (array) $row->content;
        }
        // Just for test
        if (!$messages) {
            $messages = array(
                array(
                    'content'   => __('The first system message for test.'),
                    'time'      => time() - 1000,
                ),
                array(
                    'content'   => __('The second system message for test.'),
                    'time'      => time(),
                ),
            );
        }
        foreach ($messages as &$message) {
            $message['time'] = date('Y/m/d H:i:s', $message['time']);
            $message['content'] = Pi::service('markup')->render($message['content'], 'text');
        }

        $this->view()->assign('module', $module);
        $this->view()->assign('modules', $moduleList);
        $this->view()->assign('summaryList', $summaryList);
        //$this->view()->assign('monitors', $monitorsEnabled);
        $this->view()->assign('links', $links);
        $this->view()->assign('messages', $messages);
        $this->view()->assign('memo', $memo);

        if ($module == 'system') {
            $subject = Pi::config('sitename');
        } else {
            $subject = $modules[$module]['title'];
        }
        $title = sprintf(__('Dashboard for %s'), $subject);
        $this->view()->assign('title', $title);
        $this->view()->setTemplate('dashboard', 'system');
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
            'content'   => Pi\Security::escape($data['content']),
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
