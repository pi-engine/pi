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
use Pi\Application\Installer\Module as ModuleInstaller;
use Module\System\Form\ModuleForm;
use Module\System\Form\ModuleFilter;
use Module\System\Form\ModuleCategoryForm;
use Module\System\Form\ModuleCategoryFilter;
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
 * 10. Module categorization
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
            /*
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
                    $data['name']
                );
            }
            */
            $data['icon'] = $data['icon'] ?: 'fa-th';
            if (empty($data['update'])) {
                $data['update'] = _a('Never updated.');
            } else {
                $data['update'] = _date($data['update']);
            }
        }
        $this->view()->assign('modules', $modules);
        //$this->view()->setTemplate('module-list');
        $this->view()->assign('title', _a('Installed modules'));
    }

    /**
     * List of modules available for installation
     */
    public function availableAction()
    {
        $modules = array();
        $filter = function ($fileinfo) use (&$modules) {
            if (!$fileinfo->isDir()) {
                return false;
            }
            $directory = $fileinfo->getFilename();
            if (preg_match('/[^a-z0-9_]/i', $directory)) {
                return false;
            }
            $meta = Pi::service('module')->loadMeta($directory, 'meta');
            if (empty($meta)) {
                return false;
            }
            $author = Pi::service('module')->loadMeta($directory, 'author');
            $meta['installed'] = Pi::registry('module')
                ->read($directory) ? true : false;
            if (empty($meta['clonable']) && $meta['installed']) {
                return false;
            }
            $meta['icon'] = !empty($meta['icon']) ? $meta['icon'] : 'fa-th';
            $modules[$directory] = array(
                'meta'      => $meta,
                'author'    => $author,
            );
        };
        Pi::service('file')->getList('module', $filter);

        $this->view()->assign('modules', $modules);
        $this->view()->assign('title', _a('Modules ready for installation'));
    }

    /**
     * Manage module categories for admin
     */
    public function categoryAction()
    {
        // Category, `_all` for all and `_none` for uncategorized
        $id = $this->params('id', '_all');
        // Module
        $m = $this->params('m');
        // Operation: move module, delete category, add category, edit category
        $op = $this->params('op');
        if ('_new' == $id) {
            $op = 'add';
        }
        // From: category id
        $from = $this->params('from');

        $message = '';
        $model = Pi::model('category', 'system');
        switch ($op) {
            case 'move':
                if ($m) {
                    if ($from) {
                        $row = $model->find($from);
                        $modules = $row->modules;
                        $modules = array_diff($modules, array($m));
                        $row->modules = $modules;
                        $row->save();
                    }
                    if ($id && is_numeric($id)) {
                        $row = $model->find($id);
                        $modules = (array) $row['modules'];
                        $modules[] = $m;
                        $row->modules = $modules;
                        $row->save();
                    } else {
                        $id = $from;
                    }
                    $message = _a('Module moved successfully.');
                }
                break;

            case 'delete':
                if ($id && is_numeric($id)) {
                    $model->delete(array('id' => $id));
                    $message = _a('Category deleted successfully.');
                }
                break;

            case 'add':
            case 'edit':
                if ($id && is_numeric($id)) {
                    $row = $model->find($id);
                    $data = $row->toArray();
                } else {
                    $data = array();
                }
                $form = new ModuleCategoryForm;
                $form->setData($data);
                $form->setAttributes(array('action' => $this->url('', array(
                        'action'    => 'category',
                    ))));
                $this->view()->assign('form', $form);
                break;

            case 'save':
                if ($this->request->isPost()) {
                    if ($id && is_numeric($id)) {
                        $row = $model->find($id);
                    } else {
                        $row = $model->createRow();
                    }
                    $form = new ModuleCategoryForm;
                    $post = $this->request->getPost();
                    $form->setData($post);
                    $form->setInputFilter(new ModuleCategoryFilter);
                    if ($form->isValid()) {
                        $values = $form->getData();
                        if (isset($values['id'])) {
                            unset($values['id']);
                        }
                        $row->assign($values);
                        $row->save();
                        $id = (int) $row->id;
                    }
                    $message = _a('Category updated successfully.');
                }
                break;

            case 'sort':
                if ($id && is_numeric($id) && $this->request->isPost()) {
                    $sort = $this->request->getPost('sort');
                    $modules = array_keys($sort);
                    array_multisort(array_values($sort), $modules);
                    $row = $model->find($id);
                    $row->modules = $modules;
                    $row->save();
                    $message = _a('Modules sorted successfully.');
                }
                break;

            default:
                break;
        }
        if ($op && $message) {
            $this->flashMessenger($message, 'success');
        }

        // Build categories
        Pi::registry('category', 'system')->flush();
        $categories = Pi::registry('category', 'system')->read();
        $moduleList = Pi::registry('modulelist')->read();
        unset($moduleList['system']);
        array_walk($categories, function (&$category) use (&$moduleList) {
            $modules = (array) $category['modules'];
            $category['modules'] = array();
            foreach ($modules as $name) {
                if (isset($moduleList[$name])) {
                    $category['modules'][] = array(
                        'name'  => $name,
                        'title' => $moduleList[$name]['title'],
                    );
                    unset($moduleList[$name]);
                }
            }
        });

        // Build navigation tabs
        $tabs = array(
            array(
                'label'     => _a('All categorized'),
                'href'      => $this->url('', array(
                        'action'    => 'category',
                        'id'        => '_all'
                    )),
                'active'    => ($id == '_all') ? 1 : 0,
            ),
            array(
                'label'     => _a('Uncategorized'),
                'href'      => $this->url('', array(
                        'action'    => 'category',
                        'id'        => '_none'
                    )),
                'active'    => ($id == '_none') ? 1 : 0,
            )
        );
        foreach ($categories as $key => $category) {
            $tabs[] = array(
                'label'     => $category['title'],
                'href'      => $this->url('', array(
                        'action'    => 'category',
                        'id'        => $category['id']
                    )),
                'active'    => ($id == $category['id']) ? 1 : 0,
            );
        }
        $tabs[] = array(
            'label'     => _a('+ New category'),
            'href'      => $this->url('', array(
                    'action'    => 'category',
                    'id'        => '_new'
                )),
            'active'    => ($id == '_new') ? 1 : 0,
        );
        $this->view()->assign(array(
            // Operation
            'op'            => $op,
            // Category id
            'id'            => $id,
            // Categorized modules
            'categories'    => $categories,
            // Uncategorized modules
            'modules'       => $moduleList,
            // Tabs
            'tabs'          => $tabs,
        ));
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
            $error = _a('Directory is not specified.');
        }
        if (!$error) {
            $meta = Pi::service('module')->loadMeta($directory, 'meta');
            if (!$meta) {
                $error = sprintf(
                    _a('Meta data are not loaded for "%s".'),
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
                $error = _a('The module has already been installed.');
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
                _a('Module "%s" is installed successfully.'),
                $name ?: $directory
            );

            Pi::service('event')->trigger(
                'module_install',
                $name ?: $directory
            );
        } elseif ($directory) {
            $message = sprintf(
                _a('Module "%s" is not installed.'),
                $name ?: $directory
            );
        } else {
            $message = _a('Module is not installed.');
        }

        $data = array(
            'title'     => _a('Module installation'),
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
        $this->view()->assign('title', _a('Module installation'));
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
        $details    = array();
        $row        = null;

        if (!$id && !$name) {
            $error = _a('Module is not specified.');
        }
        if (!$error) {
            if ($id) {
                $row = Pi::model('module')->find($id);
            } else {
                $row = Pi::model('module')->find($name, 'name');
            }
            if (!$row) {
                $error = _a('Module is not found.');
            } elseif ('system' == $row->name) {
                $error = _a('System module is protected.');
            } else {
                $installer = new ModuleInstaller;
                $result = $installer->uninstall($row);
                $details = $installer->getResult();
            }
        }
        if ($result) {
            $message = sprintf(
                _a('Module "%s" is uninstalled successfully.'),
                $row->title
            );

            Pi::service('event')->trigger('module_uninstall', $row->name);
        } elseif ($row) {
            $message = sprintf(
                _a('Module "%s" is not uninstalled.'),
                $row->title
            );
        } elseif ($id || $name) {
            $message = sprintf(
                _a('Module "%s" is not uninstalled.'),
                $name ?: $id
            );
        } else {
            $message = _a('Module is not uninstalled.');
        }

        $data = array(
            'title'     => _a('Module uninstallation'),
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
     * Update all modules
     */
    public function refreshAction()
    {
        @set_time_limit(0);

        $result     = array();
        $rowset     = Pi::model('module')->select(array('active' => 1));
        foreach ($rowset as $row) {
            $installer  = new ModuleInstaller;
            $status = $installer->update($row);
            $details = $installer->getResult();
            $result[$row['name']] = array(
                'title'     => $row['title'],
                'status'    => $status,
                'result'    => $details,
            );
            if ($status) {
                Pi::service('event')->trigger('module_update', $row['name']);
            }
        }

        $data = array(
            'title'     => _a('Module updates'),
            'result'    => $result,
            'url'       => $this->url('', array('action' => 'index')),
        );
        $this->view()->assign($data);
        $this->view()->setTemplate('module-refresh');
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
        $details    = array();
        $row        = null;

        if (!$id && !$name) {
            $error = _a('Module is not specified.');
        }
        if (!$error) {
            if ($id) {
                $row = Pi::model('module')->find($id);
            } else {
                $row = Pi::model('module')->find($name, 'name');
            }
            if (!$row) {
                $error = _a('Module is not found.');
            } else {
                $installer = new ModuleInstaller;
                $result = $installer->update($row);
                $details = $installer->getResult();
            }
        }
        if ($result) {
            $message = sprintf(
                _a('Module "%s" is updated successfully.'),
                $row->title
            );

            Pi::service('event')->trigger('module_update', $row->name);

        } elseif ($row) {
            $message = sprintf(_a('Module "%s" is not updated.'), $row->title);
        } elseif ($id || $name) {
            $message = sprintf(_a('Module "%s" is not updated.'),
                $name ?: $id);
        } else {
            $message = _a('Module is not updated.');
        }

        $data = array(
            'title'     => _a('Module update'),
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
        $details    = array();
        $row        = null;

        if (!$id && !$name) {
            $error = _a('Module is not specified.');
        }
        if (!$error) {
            if ($id) {
                $row = Pi::model('module')->find($id);
            } else {
                $row = Pi::model('module')->find($name, 'name');
            }
            if (!$row) {
                $error = _a('Module is not found.');
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
                $message = sprintf(
                    _a('Module "%s" is enabled successfully.'),
                    $row->title
                );

                Pi::service('event')->trigger('module_activate', $row['name']);
            } elseif ($row) {
                $message = sprintf(
                    _a('Module "%s" is not enabled.'),
                    $row->title
                );
            } elseif ($id || $name) {
                $message = sprintf(
                    _a('Module "%s" is not enabled.'),
                    $name ?: $id
                );
            } else {
                $message = _a('Module is not enabled.');
            }
        } else {
            if ($result) {
                $message = sprintf(
                    _a('Module "%s" is disabled successfully.'),
                    $row->title
                );

                Pi::service('event')->trigger('module_deactivate', $row['name']);
            } elseif ($row) {
                $message = sprintf(
                    _a('Module "%s" is not disabled.'),
                    $row->title
                );
            } elseif ($id || $name) {
                $message = sprintf(
                    _a('Module "%s" is not disabled.'),
                    $name ?: $id
                );
            } else {
                $message = _a('Module is not disabled.');
            }
        }

        $data = array(
            'title'     => _a('Module activation'),
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
                'message'   => _a('Title is required.')
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
                'message'   => _a('Module is not found.')
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
