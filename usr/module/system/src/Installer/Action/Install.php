<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Install as BasicInstall;
use Pi\Application\Installer\SqlSchema;
use Pi\Application\Installer\Theme as ThemeInstaller;
use Pi\Application\Installer\Module as ModuleInstaller;
use Zend\EventManager\Event;

/**
 * Install handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Install extends BasicInstall
{
    /**
     * Modules to be installed upon system installation
     *
     * @var string[]
     */
    protected $preInstalledModules = array('page', 'widget');

    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach(
            'install.pre',
            array($this, 'createSystemSchema'),
            1000
        );
        $events->attach('install.post', array($this, 'installTheme'), 1);
        $events->attach('install.post', array($this, 'createSystemData'), -10);
        $events->attach(
            'install.post',
            array($this, 'installApplication'),
            -100
        );
        $events->attach('install.post', array($this, 'dressupBlock'), -200);
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Generate system data
     *
     * @param Event $e
     */
    public function createSystemData(Event $e)
    {
        $module = $e->getParam('module');
        $message = array();

        // Add system roles
        $roleFile = Pi::service('module')->path($module) . '/config/role.php';
        $roles = include $roleFile;
        $roleModel = Pi::model('role');
        foreach ($roles as $section => $roleList) {
            foreach ($roleList as $role => $roleTitle) {
                $row = $roleModel->createRow(array(
                    'section'   => $section,
                    'module'    => $module,
                    'name'      => $role,
                    'title'     => $roleTitle,
                    'active'    => 1,
                ));
                $row->save();
            }
        }

        // Add default taxonomy domain
        Pi::service('taxonomy')->addDomain(array(
            'name'          => 'taxon',
            'title'         => _a('Default taxonomy'),
            'description'   =>
                _a('Default global taxonomy domain. Not allowed to change.'),
        ), false);

        // Add system messages
        $name       = 'admin-welcome';
        $message    = _a('Welcome to Pi powered system.');
        Pi::user()->data->set(0, $name, $message, $module);

        // Add quick links
        $user   = 1;
        $name   = 'admin-link';
        $links  = array(
            array(
                'title' => 'Pi Engine Development',
                'url'   => 'http://www.pialog.org',
            ),
            array(
                'title' => 'Pi Engine Code',
                'url'   => 'http://code.pialog.org',
            ),
            array(
                'title' => 'Pi Engine Doc',
                'url'   => 'http://doc.pialog.org',
            ),
            array(
                'title' => 'Pi Engine Twitter',
                'url'   => 'https://twitter.com/PiEnable',
            ),
        );
        Pi::user()->data->set($user, $name, $links, $module);

        // Add update list
        /*
        $model = Pi::model('update', $module);
        $data = array(
            'title'     => _a('System installed'),
            'content'   => _a('The system is installed successfully.'),
            'uri'       => Pi::url('www', true),
            'time'      => time(),
        );
        $model->insert($data);
        */
        Pi::service('event')->trigger('system-module_install', 'system');
    }

    /**
     * Install default theme
     *
     * @param Event $e
     * @return bool
     */
    public function installTheme(Event $e)
    {
        $themeInstaller = new ThemeInstaller;
        $result = $themeInstaller->install('default');
        if (is_array($result)) {
            $status = $result['status'];
            if (!$status) {
                $ret = $e->getParam('result');
                $ret['theme'] = $result;
                $e->setParam('result', $ret);
            }
        } else {
            $status = (bool) $result;
        }

        return $status;
    }

    /**
     * Create system module data
     *
     * @param Event $e
     * @return bool
     */
    public function createSystemSchema(Event $e)
    {
        $sqlFile = Pi::path('module') . '/system/sql/mysql.system.sql';
        $status = SqlSchema::query($sqlFile);

        return $status;
    }

    /**
     * Install modules automatically
     *
     * @param Event $e
     * @return bool
     */
    public function installApplication(Event $e)
    {
        $apps = $this->preInstalledModules;
        //$installer = new ModuleInstaller;
        foreach ($apps as $app) {
            $installer = new ModuleInstaller;
            $ret = $installer->install($app);
        }

        $categories = array(
            array(
                'title' => _a('Application'),
                'order' => 1,
                'modules'   => array(
                    'user',
                    'uclient',
                    'article',
                    'document',
                    'solution',
                    'video',
                    'forum',
                    'page',
                    'demo'
                ),
            ),
            array(
                'title' => _a('Service'),
                'order' => 2,
                'modules'   => array(
                    'uclient',
                    'message',
                    'tag',
                    'comment',
                    'search',
                    'widget',
                    'media',
                    'saml'
                ),
            )
        );
        $model = Pi::model('category', 'system');
        foreach ($categories as $category) {
            $row = $model->createRow($category);
            $row->save();
        }

        return true;
    }

    /**
     * Install and dress up pages with blocks
     *
     * @param Event $e
     */
    public function dressupBlock(Event $e)
    {
        // Find homepage
        $modelPage = Pi::model('page');
        $homePage = $modelPage->select(array(
            'section'       => 'front',
            'block'         => 1,
            'module'        => 'system',
            'controller'    => 'index',
            'action'        => 'index',
        ))->current()->toArray();

        // Add user login block to homepage sidebar
        $modelBlock = Pi::model('block');
        $blockList = $modelBlock->select(array(
            'module'    => 'system',
            'name'      => array('system-user', 'system-login')
        ));
        //$blocks = array();
        $i = 0;
        $modelLink = Pi::model('page_block');
        foreach ($blockList as $block) {
            //$blocks[$block['name']] = $block['id'];
            //foreach ($pages as $page) {
                $data = array(
                    'page'      => $homePage['id'],
                    'block'     => $block['id'],
                    'zone'      => 8,
                    'order'     => ++$i
                );
                $modelLink->insert($data);
            //}
        }

        // Add spotlight as top block to homepage
        $blockList = array();

        if (in_array('widget', $this->preInstalledModules)) {
            // Add spotlight and feature blocks to homepage
            $blockList[] = $modelBlock->select(array(
                'module'    => 'widget',
                'name'      => 'widget-highlights',
            ))->current()->toArray();
        }

        $i = 0;
        foreach ($blockList as $block) {
            $data = array(
                'page'      => $homePage['id'],
                'block'     => $block['id'],
                'zone'      => 0,
                'order'     => ++$i
            );
            $modelLink->insert($data);
        }


        // Add feature as center block to homepage
        $blockList = array();
        $blockList[] = $modelBlock->select(array(
            'module'    => 'system',
            'name'      => 'system-pi'
        ))->current()->toArray();

        $i = 0;
        foreach ($blockList as $block) {
            $data = array(
                'page'      => $homePage['id'],
                'block'     => $block['id'],
                'zone'      => 2,
                'order'     => ++$i
            );
            $modelLink->insert($data);
        }
    }
}
