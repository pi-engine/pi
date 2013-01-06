<?php
/**
 * System admin module controller
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

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Application\Installer\Module as ModuleInstaller;
use Module\System\Form\ModuleForm;
use Module\System\Form\ModuleFilter;
use Zend\Rest\Client\RestClient;
use Zend\Form\Form;
use Zend\Db\Sql\Expression;

/**
 * Feature list:
 *  1. List of active modules
 *  2. List of modules available for installation
 *  3. List of modules in global Pi Engine repository
 *  4. Module installation
 *  5. Module update
 *  6. Module activation
 *  7. Module deactivation
 *  8. Module uninstallation
 *  9. Module asset publish
 */
class ModuleController extends ActionController
{
    protected $repoClient;
    protected $repoUrl = 'http://repo.xoopsengine.org/module';
    protected $repoApi = 'http://api.xoopsengine.org/module';

    /**
     * List of active modules and inactive modules
     */
    public function indexAction()
    {
        $active = Pi::service('registry')->modulelist->read('active');
        $inactive = Pi::service('registry')->modulelist->read('inactive');

        $modules = array_merge($active, $inactive);
        foreach ($modules as $name => &$data) {
            $meta = Pi::service('module')->loadMeta($data['directory'], 'meta');
            $author = Pi::service('module')->loadMeta($data['directory'], 'author');
            $data['description'] = $meta['description'];
            $data['author'] = $author;
            if (empty($meta['logo'])) {
                $data['logo'] = Pi::url('static/image/module.png');
            } elseif (empty($data['active'])) {
                $data['logo'] = Pi::url('script/browse.php') . '?' . sprintf('module/%s/asset/%s', $data['directory'], $meta['logo']);
            } else {
                $data['logo'] = Pi::service('asset')->getModuleAsset($meta['logo'], $data['name'], false);
            }
            if (empty($data['update'])) {
                $data['update'] = __('Never updated.');
            } else {
                $data['update'] = date('Y-m-d H:i:s', $data['update']);
            }
            $data['active'] = isset($active[$name]) ? true : false;
        }
        $this->view()->assign('modules', $modules);
        //$this->view()->setTemplate('module-list');
        $this->view()->assign('title', __('Installed modules'));
    }

    /**
     * List of modules available for installation
     */
    public function availableAction()
    {
        $modules = array();
        $modulesInstalled = $this->installedModules();
        $iterator = new \DirectoryIterator(Pi::path('module'));
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isDir() || $fileinfo->isDot()) {
                continue;
            }
            $directory = $fileinfo->getFilename();
            if (/*isset($modulesInstalled[$directory]) || 'system' == $directory || */preg_match('/[^a-z0-9_]/i', $directory)) {
                continue;
            }
            $meta = Pi::service('module')->loadMeta($directory, 'meta');
            if (empty($meta)) {
                continue;
            }
            $author = Pi::service('module')->loadMeta($directory, 'author');
            $clonable = isset($meta['clonable']) ? $meta['clonable'] : false;
            if (!$clonable && in_array($directory, $modulesInstalled)) {
                continue;
            }
            $meta['logo'] = !empty($meta['logo']) ? Pi::url('script/browse.php') . '?' . sprintf('module/%s/asset/%s', $directory, $meta['logo']) : Pi::url('static/image/module.png');
            $modules[$directory] = array(
                'meta'      => $meta,
                'author'    => $author,
            );
        }

        $this->view()->assign('modules', $modules);
        $this->view()->assign('title', __('Modules ready for installation'));
    }

    /**
     * AJAX to update a module
     */
    public function updateAction()
    {
        $status = 1;
        $id = $this->params('id');
        $row = Pi::model('module')->find($id);
        $moduleName = $row->name;
        $installer = new ModuleInstaller;
        $ret = $installer->update($row);
        $message = '';
        $data = array();
        if (!$ret) {
            $status = 0;
            $message = $installer->renderMessage() ?: sprintf(__('The module "%s" is not updated.'), $moduleName);
        } else {
            $meta = Pi::service('module')->loadMeta($row->directory, 'meta');
            $author = Pi::service('module')->loadMeta($row->directory, 'author');
            /*
            $detail = array(
                'module'    => $row->toArray(),
                'meta'      => $meta,
                'author'    => $author,
            );
            */
            $row = Pi::model('module')->find($id);
            $data = $row->toArray();
            $data['update'] = date('Y-m-d H:i:s', $data['update']);
            $data['description'] = $meta['description'];
            $data['author'] = $author;
            if (empty($meta['logo'])) {
                $data['logo'] = Pi::url('static/image/module.png');
            } elseif (empty($data['active'])) {
                $data['logo'] = Pi::url('script/browse.php') . '?' . sprintf('module/%s/asset/%s', $data['directory'], $meta['logo']);
            } else {
                $data['logo'] = Pi::service('asset')->getModuleAsset($meta['logo'], $data['name'], false);
            }
        }
        $message = $message ?: sprintf(__('The module "%s" is updated.'), $moduleName);
        $result = array(
            'status'    => $status,
            'message'   => $message,
            'data'      => $data,
        );

        return $result;
    }

    /**
     * Activate a module
     */
    public function enableAction()
    {
        $status = 1;
        $id = $this->params()->fromPut('id');
        $active = $this->params()->fromPut('active');
        $row = Pi::model('module')->find($id);
        $moduleName = $row->name;
        $installer = new ModuleInstaller;
        if ($active) {
            $ret = $installer->activate($row);
        } else {
            $ret = $installer->deactivate($row);
        }
        if (!$ret) {
            $status = 0;
            $message = $installer->renderMessage() ?: sprintf(__('The module "%s" status is not updated.'), $moduleName);
        } else {
            $message = sprintf(__('The module "%s" status is updated.'), $moduleName);
        }
        $result = array(
            'status'    => $status,
            'message'   => $message,
            'data'      => array(
                'active'    => $active,
            ),
        );

        return $result;
    }

    /**
     * Install a module and publish its asset
     */
    public function installAction()
    {
        $form = new ModuleForm('install');
        $this->view()->assign('form', $form);
        if ($this->request->isPost()) {
            $post = $this->request->getPost();

            $directory = $post['directory'];
            $meta = Pi::service('module')->loadMeta($directory, 'meta');
            $installedModules = $this->installedModules();
            if (in_array($directory, $installedModules)) {
                if (empty($meta['clonable'])) {
                    return array(
                        'status'    => 0,
                        'message'   => __('The module is not allowed to install mutiples.'),
                        'module'    => array(),
                    );
                }
            }

            $form->setData($post);
            $form->setInputFilter(new ModuleFilter);

            $status = 1;
            $message = '';
            $module = array();
            if ($form->isValid()) {
                $values = $form->getData();
                $installer = new ModuleInstaller;
                $ret = $installer->install($values['name'], array('directory' => $values['directory'], 'title' => $values['title']));
                if (!$ret) {
                    $message = $installer->renderMessage() ?: sprintf(__('The module "%s" is not installed.'), $values['name']);
                    $status = 0;
                } else {
                    $row = Pi::model('module')->select(array('name' => $values['name']))->current();
                    /*
                    if (!empty($values['title'])) {
                        $row->title = $values['title'];
                        $row->save();
                    }
                    */
                    $message = $message ?: sprintf(__('The module "%s" is installed.'), $values['name']);
                    $module = array(
                        'id'    => $row->id,
                        'name'  => $row->name,
                        'title' => $row->title,
                    );
                    /*
                    $this->view()->assign('id', $row->id);
                    $this->view()->assign('message', $message);
                    $this->view()->setTemplate('module-operation');

                    return;
                    */
                }
            } else {
                $messages = $form->getMessages();
                $message = array();
                foreach ($messages as $key => $msg) {
                    $message[$key] = array_values($msg);
                }
                $status = -1;
            }
            return array(
                'status'    => $status,
                'message'   => $message,
                'module'    => $module,
                'clonable'  => empty($meta['clonable']) ? 0 : 1,
            );
        } else {
            $directory = $this->Params('directory');
            $meta = Pi::service('module')->loadMeta($directory, 'meta');
            $form->setData(array(
                'directory' => $directory,
                'name'      => $directory,
                'title'     => $meta['title'],
            ));
        }
        $form->setAttribute('action', $this->url('', array('action' => 'install')));
        $this->view()->assign('title', __('Module installation'));
        $this->view()->setTemplate('system:component/form-popup');
    }

    /**
     * Uninstall a module and remove its asset
     */
    public function uninstallAction()
    {
        $status = 1;
        $id = $this->params('id');
        $row = Pi::model('module')->find($id);
        $moduleName = $row->name;
        $message = '';
        if ('system' == $moduleName) {
            $status = 0;
            $message = __('System module is protected from uninstallation.');
        } else {
            $installer = new ModuleInstaller;
            $ret = $installer->uninstall($row);
            if (!$ret) {
                $status = 0;
                $message = $installer->renderMessage() ?: sprintf(__('The module "%s" is not uninstalled.'), $moduleName);
            }
        }
        $message = $message ?: sprintf(__('The module "%s" is uninstalled.'), $moduleName);
        /*
        $this->view()->assign('message', $message);
        $this->view()->assign('title', __('Module uninstallaton'));
        */
        $result = array(
            'status'    => $status,
            'message'   => $message,
            'module'    => $moduleName,
        );

        return $result;
    }

    /**
     * AJAX method to rename a module
     */
    public function renameAction()
    {
        $post = $this->params()->fromPut();
        if (empty($post['title'])) {
            return array(
                'status'    => 0,
                'message'   => __('Title is required.')
            );
        }
        $id = intval($post['id']);
        $row = Pi::model('module')->find($id);
        $row->title = $post['title'];
        $row->save();
        Pi::service('registry')->module->clear();
        Pi::service('registry')->modulelist->clear();

        return array(
            'status'    => 1,
            'data'      => array(
                'title' => $row->title,
            ),
        );
    }

    /**
     * Get installed modules indexed by directory
     *
     * @return array
     */
    protected function installedModules()
    {
        $model = Pi::model('module');
        $select = $model->select()->columns(array('dir' => new Expression('DISTINCT directory')));
        $rowset = $model->selectWith($select);
        $modules = array();
        foreach ($rowset as $row) {
            $modules[] = $row->dir;
        }

        return $modules;
    }
}
