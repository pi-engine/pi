<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
 * Module manipulation
 *
 * Feature list:
 *
 *  1. List of active modules
 *  2. List of modules available for installation
 *  3. List of modules in global Pi Engine repository
 *  4. Module installation
 *  5. Module update
 *  6. Module activation
 *  7. Module deactivation
 *  8. Module uninstallation
 *  9. Module asset publish
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ModuleController extends ActionController
{
    /**
     * Client to access module repos
     * @var object
     */
    protected $repoClient;

    /** @var string URL to module repos */
    protected $repoUrl = 'http://repo.pialog.org/module';

    /** @var string URL to module repo API */
    protected $repoApi = 'http://api.pialog.org/module';

    /**
     * List of active modules and inactive modules
     */
    public function indexAction()
    {
        $active = Pi::registry('modulelist')->read('active');
        $inactive = Pi::registry('modulelist')->read('inactive');

        $modules = array_merge($active, $inactive);
        foreach ($modules as $name => &$data) {
            $meta = Pi::service('module')->loadMeta(
                $data['directory'],
                'meta'
            );
            $author = Pi::service('module')->loadMeta(
                $data['directory'],
                'author'
            );
            $data['description'] = $meta['description'];
            $data['author'] = $author;
            $data['active'] = isset($active[$name]) ? true : false;
            if (empty($meta['logo'])) {
                $data['logo'] = Pi::url('static/image/module.png');
            } elseif (empty($data['active'])) {
                $data['logo'] = Pi::url('script/browse.php') . '?' . sprintf(
                    'module/%s/asset/%s',
                    $data['directory'],
                    $meta['logo']
                );
            } else {
                $data['logo'] = Pi::service('asset')->getModuleAsset(
                    $meta['logo'],
                    $data['name'],
                    false
                );
            }
            if (empty($data['update'])) {
                $data['update'] = __('Never updated.');
            } else {
                $data['update'] = _date($data['update']);
            }
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
            if (preg_match('/[^a-z0-9_]/i', $directory)) {
                continue;
            }
            $meta = Pi::service('module')->loadMeta($directory, 'meta');
            if (empty($meta)) {
                continue;
            }
            $author = Pi::service('module')->loadMeta($directory, 'author');
            //$clonable = isset($meta['clonable']) ? $meta['clonable'] : false;
            //$meta['installed'] = in_array($directory, $modulesInstalled);
            $meta['installed'] = Pi::registry('module')
                    ->read($directory) ? true : false;
            if (empty($meta['clonable']) && $meta['installed']) {
                continue;
            }
            $meta['logo'] = !empty($meta['logo'])
                ? Pi::url('script/browse.php') . '?'
                    . sprintf('module/%s/asset/%s', $directory, $meta['logo'])
                : Pi::url('static/image/module.png');
            $modules[$directory] = array(
                'meta'      => $meta,
                'author'    => $author,
            );
        }

        $this->view()->assign('modules', $modules);
        $this->view()->assign('title', __('Modules ready for installation'));
    }

    /**
     * Install a module and publish its asset
     */
    public function installAction()
    {
        $directory = _get('directory', 'regexp', array(
            'regexp' => '/^[a-z0-9_]+$/i')
        );
        $name = _get('name', 'regexp', array('regexp' => '/^[a-z0-9_]+$/i'))
            ?: $directory;
        $title = _get('title');

        $result     = false;
        $error      = '';
        $message    = '';
        $details    = array();

        if (empty($directory) && $name) {
            $directory = $name;
        }

        if (!$directory) {
            $error = __('Directory is not specified.');
        }
        if (!$error) {
            $meta = Pi::service('module')->loadMeta($directory, 'meta');
            if (!$meta) {
                $error = sprintf(
                    __('Meta data are not loaded for "%s".'),
                    $directory
                );
            }
        }
        if (!$error) {
            $installed = Pi::registry('module')->read($name)
                ? true : false;
            if (!$installed) {
                $installedModules = $this->installedModules();
                if (in_array($directory, $installedModules)
                    && empty($meta['clonable'])
                ) {
                    $installed = false;
                }
            }
            if ($installed) {
                $error = __('The module has already been installed.');
            }
        }
        if (!$error) {
            $args = array(
                'directory' => $directory,
                'title'     => $title ?: $meta['title'],
            );

            $installer = new ModuleInstaller;
            $result = $installer->install($name, $args);
            $details = $installer->getResult();
        }
        if ($result) {
            $message = sprintf(
                __('Module "%s" is installed successfully.'),
                $name ?: $directory
            );
        } elseif ($directory) {
            $message = sprintf(
                __('Module "%s" is not installed.'),
                $name ?: $directory
            );
        } else {
            $message = __('Module is not installed.');
        }

        $data = array(
            'title'     => __('Module installation'),
            'result'    => $result,
            'error'     => $error,
            'message'   => $message,
            'details'   => $details,
            'url'       => $this->url('', array('action' => 'available')),
        );
        $this->view()->assign($data);
        $this->view()->setTemplate('module-operation');
    }

    /**
     * Clone a module and publish its asset
     *
     * @return void|array
     */
    public function cloneAction()
    {
        $form = new ModuleForm('install');
        $this->view()->assign('form', $form);
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new ModuleFilter);
            if ($form->isValid()) {
                $values = $form->getData();
                return array(
                    'status'    => 1,
                    'data'      => $values,
                );
            }

            $messages = $form->getMessages();
            $message = array();
            foreach ($messages as $key => $msg) {
                $message[$key] = array_values($msg);
            }
            return array(
                'status'    => 0,
                'message'   => $message,
            );
        } else {
            $directory = _get('directory');
            $meta = Pi::service('module')->loadMeta($directory, 'meta');
            $form->setData(array(
                'directory' => $directory,
                'name'      => $directory,
                'title'     => $meta['title'],
            ));
        }
        $form->setAttribute(
            'action',
            $this->url('', array('action' => 'clone'))
        );
        $this->view()->assign('title', __('Module installation'));
        $this->view()->setTemplate('system:component/form-popup');
    }

    /**
     * Uninstall a module and remove its asset
     */
    public function uninstallAction()
    {
        $id         = _get('id', 'int');
        $name       = _get('name', 'regexp',
                           array('regexp' => '/^[a-z0-9_]+$/i'));

        $result     = false;
        $error      = '';
        $message    = '';
        $details    = array();
        $row        = null;

        if (!$id && !$name) {
            $error = __('Module is not specified.');
        }
        if (!$error) {
            if ($id) {
                $row = Pi::model('module')->find($id);
            } else {
                $row = Pi::model('module')->find($name, 'name');
            }
            if (!$row) {
                $error = __('Module is not found.');
            } elseif ('system' == $row->name) {
                $error = __('System module is protected.');
            } else {
                $installer = new ModuleInstaller;
                $result = $installer->uninstall($row);
                $details = $installer->getResult();
            }
        }
        if ($result) {
            $message = sprintf(__('Module "%s" is uninstalled successfully.'),
                               $row->title);
        } elseif ($row) {
            $message = sprintf(__('Module "%s" is not uninstalled.'),
                               $row->title);
        } elseif ($id || $name) {
            $message = sprintf(__('Module "%s" is not uninstalled.'),
                               $name ?: $id);
        } else {
            $message = __('Module is not uninstalled.');
        }

        $data = array(
            'title'     => __('Module uninstallation'),
            'result'    => $result,
            'error'     => $error,
            'message'   => $message,
            'details'   => $details,
            'url'       => $this->url('', array('action' => 'index')),
        );
        $this->view()->assign($data);
        $this->view()->setTemplate('module-operation');
    }

    /**
     * Update a module
     */
    public function updateAction()
    {
        $id         = _get('id', 'int');
        $name       = _get('name', 'regexp',
                           array('regexp' => '/^[a-z0-9_]+$/i'));

        $result     = false;
        $error      = '';
        $message    = '';
        $details    = array();
        $row        = null;

        if (!$id && !$name) {
            $error = __('Module is not specified.');
        }
        if (!$error) {
            if ($id) {
                $row = Pi::model('module')->find($id);
            } else {
                $row = Pi::model('module')->find($name, 'name');
            }
            if (!$row) {
                $error = __('Module is not found.');
            } else {
                $installer = new ModuleInstaller;
                $result = $installer->update($row);
                $details = $installer->getResult();
            }
        }
        if ($result) {
            $message = sprintf(__('Module "%s" is updated successfully.'),
                               $row->title);
        } elseif ($row) {
            $message = sprintf(__('Module "%s" is not updated.'), $row->title);
        } elseif ($id || $name) {
            $message = sprintf(__('Module "%s" is not updated.'),
                               $name ?: $id);
        } else {
            $message = __('Module is not updated.');
        }

        $data = array(
            'title'     => __('Module update'),
            'result'    => $result,
            'error'     => $error,
            'message'   => $message,
            'details'   => $details,
            'url'       => $this->url('', array('action' => 'index')),
        );
        $this->view()->assign($data);
        $this->view()->setTemplate('module-operation');
    }

    /**
     * Activate/deactivate a module
     */
    public function enableAction()
    {
        $id         = _get('id', 'int');
        $name       = _get('name', 'regexp',
                           array('regexp' => '/^[a-z0-9_]+$/i'));
        $active     = _get('active', 'int');

        $result     = false;
        $error      = '';
        $message    = '';
        $details    = array();
        $row        = null;

        if (!$id && !$name) {
            $error = __('Module is not specified.');
        }
        if (!$error) {
            if ($id) {
                $row = Pi::model('module')->find($id);
            } else {
                $row = Pi::model('module')->find($name, 'name');
            }
            if (!$row) {
                $error = __('Module is not found.');
            } else {
                $installer = new ModuleInstaller;
                if ($active) {
                    $result = $installer->activate($row);
                } else {
                    $result = $installer->deactivate($row);
                }
                $details = $installer->getResult();
            }
        }
        if ($active) {
            if ($result) {
                $message = sprintf(__('Module "%s" is enabled successfully.'),
                                   $row->title);
            } elseif ($row) {
                $message = sprintf(__('Module "%s" is not enabled.'),
                                   $row->title);
            } elseif ($id || $name) {
                $message = sprintf(__('Module "%s" is not enabled.'),
                                   $name ?: $id);
            } else {
                $message = __('Module is not enabled.');
            }
        } else {
            if ($result) {
                $message = sprintf(__('Module "%s" is disabled successfully.'),
                                  $row->title);
            } elseif ($row) {
                $message = sprintf(__('Module "%s" is not disabled.'),
                                   $row->title);
            } elseif ($id || $name) {
                $message = sprintf(__('Module "%s" is not disabled.'),
                                   $name ?: $id);
            } else {
                $message = __('Module is not disabled.');
            }
        }

        $data = array(
            'title'     => __('Module activation'),
            'result'    => $result,
            'error'     => $error,
            'message'   => $message,
            'details'   => $details,
            'url'       => $this->url('', array('action' => 'index')),
        );
        $this->view()->assign($data);
        $this->view()->setTemplate('module-operation');
    }

    /**
     * AJAX method to rename a module
     *
     * @return array Result pair of status and message
     */
    public function renameAction()
    {
        //$post = $this->params()->fromPost();
        $title = _post('title');
        if (empty($title)) {
            return array(
                'status'    => 0,
                'message'   => __('Title is required.')
            );
        }
        $id     = _post('id', 'int');
        $name   = _post('name', 'regexp',
                        array('regexp' => '/^[a-z0-9_]+$/i'));
        if ($id) {
            $row = Pi::model('module')->find($id);
        } else {
            $row = Pi::model('module')->find($name, 'name');
        }
        if (!$row) {
            return array(
                'status'    => 0,
                'message'   => __('Module is not found.')
            );
        }

        $row->title = $title;
        $row->save();
        Pi::registry('module')->clear();
        Pi::registry('modulelist')->clear();

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
     * @return string[]
     */
    protected function installedModules()
    {
        $model = Pi::model('module');
        $select = $model->select()
            ->columns(array('dir' => new Expression('DISTINCT directory')));
        $rowset = $model->selectWith($select);
        $modules = array();
        foreach ($rowset as $row) {
            $modules[] = $row->dir;
        }

        return $modules;
    }
}
