<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Install as BasicAction;
use Pi\Application\Installer\Module as ModuleInstaller;
use Pi\Application\Installer\Resource\User as UserResource;
use Zend\EventManager\Event;

/**
 * Install handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Install extends BasicAction
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.post', array($this, 'checkModules'), 10);
        $events->attach('install.post', array($this, 'checkUsers'), 5);
        $events->attach('install.post', array($this, 'updateConfig'), 1);
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Check other modules and install profiles if available
     *
     * @param Event $e
     * @return void
     */
    public function checkModules(Event $e)
    {
        $module = $e->getParam('module');

        $modules = Pi::registry('module')->read();
        if (isset($modules['user'])) {
            unset($modules['user']);
        }
        $moduleList = array_keys($modules);
        foreach ($moduleList as $mod) {
            $options = Pi::service('module')->loadMeta($mod, 'user');
            if (empty($options)) {
                continue;
            }
            if (is_string($options)) {
                $optionsFile = sprintf(
                    '%s/%s/config/%s',
                    Pi::path('module'),
                    Pi::service('module')->directory($mod),
                    $options
                );
                $options = include $optionsFile;
                if (empty($options) || !is_array($options)) {
                    continue;
                }
            }

            $resourceHandler = new UserResource($options);
            $e->setParam('module', $mod);
            $resourceHandler->setEvent($e);
            $resourceHandler->installAction();
        }

        $e->setParam('module', $module);
    }

    /**
     * Check existent users and create profile
     *
     * @param Event $e
     *
     * @return bool
     */
    public function checkUsers(Event $e)
    {
        $modelAccount = Pi::model('user_account');
        $modelProfile = Pi::model('profile', 'user');

        $sql = 'INSERT INTO ' . $modelProfile->getTable() . ' (uid)'
             . ' SELECT id FROM ' . $modelAccount->getTable();
        try {
            $result = Pi::db()->query($sql);
        } catch (\Exception $exception) {
            $e->setResult('user', array(
                'status'    => false,
                'message'   => 'User profile generation failed: '
                . $exception->getMessage(),
            ));

            $result = false;
        }

        return $result;
    }

    /**
     * Update user service config
     *
     * @param Event $e
     *
     * @return bool
     */
    public function updateConfig(Event $e)
    {
        $config = Pi::config()->load('service.user.php');
        $config['adapter'] = 'Pi\User\Adapter\Local';
        Pi::config()->write('service.user.php', $config, true);

        return true;
    }
}
