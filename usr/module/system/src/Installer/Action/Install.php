<?php
/**
 * Pi module installer action
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
 * @subpackage      Installer
 * @version         $Id$
 */

namespace Module\System\Installer\Action;
use Pi;
use Pi\Application\Installer\Action\Install as BasicInstall;
use Pi\Application\Installer\SqlSchema;
use Pi\Application\Installer\Theme as ThemeInstaller;
use Pi\Application\Installer\Module as ModuleInstaller;
use Zend\EventManager\Event;

class Install extends BasicInstall
{
    protected $preInstalledModules = array('page', 'widget');

    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.pre', array($this, 'createSystemSchema'), 1000);
        $events->attach('install.post', array($this, 'installTheme'), 1);
        $events->attach('install.post', array($this, 'createSystemData'), -10);
        $events->attach('install.post', array($this, 'installApplication'), -100);
        parent::attachDefaultListeners();
        return $this;
    }

    public function createSystemSchema(Event $e)
    {
        $sqlFile = Pi::path('module') . '/system/sql/mysql.system.sql';
        $status = SqlSchema::query($sqlFile);

        return $status;
    }

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

    public function installApplication(Event $e)
    {
        $apps = $this->preInstalledModules;
        //$installer = new ModuleInstaller;
        foreach ($apps as $app) {
            $installer = new ModuleInstaller;
            $ret = $installer->install($app);
        }

        return true;
    }

    public function createSystemData(Event $e)
    {
        $module = $e->getParam('module');
        $message = array();

        // Add default taxonomy domain
        Pi::service('taxonomy')->addDomain(array(
            'name'          => 'taxon',
            'title'         => __('Default taxonomy'),
            'description'   => __('Default global taxonomy domain. Not allowed to change.'),
        ), false);


        // Find homepage
        $model = Pi::model('page');
        $pages = $model->select(array(
            'section'       => 'front',
            'block'         => 1,
            'module'        => 'system',
            'controller'    => 'index',
            'action'        => 'index',
        ))->toArray();
        // Add user login block to homepage
        $model = Pi::model('block');
        $blockList = $model->select(array(
            'module'    => $module,
            'name'      => array('system-user', 'system-login')
        ));
        //$blocks = array();
        $i = 0;
        $model = Pi::model('page_block');
        foreach ($blockList as $block) {
            //$blocks[$block['name']] = $block['id'];
            foreach ($pages as $page) {
                $data = array(
                    'page'      => $page['id'],
                    'block'     => $block['id'],
                    'zone'      => 0,
                    'order'     => ++$i
                );
                $model->insert($data);
            }
        }

        // Add system messages
        $type       = 'admin-welcome';
        $message    = array(
            'content'   => __('Welcome to Pi powered system.'),
            'time'      => time(),
        );
        $row = Pi::model('user_repo')->createRow(array(
            'module'    => $module,
            'type'      => $type,
            'content'   => $message,
        ));
        $row->save();

        // Add quick links
        $user   = 1;
        $type   = 'admin-link';
        $links  = array(
            array(
                'title' => 'Pi Engine Development',
                'url'   => 'http://www.xoopsengine.org',
            ),
            array(
                'title' => 'Pi Engine Code',
                'url'   => 'http://github.com/xoops',
            ),
            array(
                'title' => 'Pi Engine Doc',
                'url'   => 'http://api.xoopsengine.org',
            ),
            array(
                'title' => 'Pi Engine Twitter',
                'url'   => 'https://twitter.com/XoopsProject',
            ),
        );

        $row = Pi::model('user_repo')->createRow(array(
            'user'      => $user,
            'module'    => $module,
            'type'      => $type,
            'content'   => $links,
        ));
        $row->save();

        // Add update list
        $model = Pi::model('update', $module);
        $data = array(
            'title'     => __('System installed'),
            'content'   => __('The system is installed successfully.'),
            'uri'       => Pi::url('www', true),
            'time'      => time(),
        );
        $model->insert($data);
    }
}
