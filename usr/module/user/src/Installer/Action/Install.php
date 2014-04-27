<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Install as BasicAction;
//use Pi\Application\Installer\Module as ModuleInstaller;
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
        $events->attach('install.pre', array($this, 'checkConflicts'), 10);
        $events->attach('install.post', array($this, 'checkModules'), 20);
        $events->attach('install.post', array($this, 'checkUsers'), 10);
        $events->attach('install.post', array($this, 'setupProfile'), 5);
        $events->attach('install.post', array($this, 'updateConfig'), 1);
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Check other modules in conflict
     *
     * @param Event $e
     * @return bool
     */
    public function checkConflicts(Event $e)
    {
        $modules = Pi::registry('module')->read();
        if (isset($modules['uclient'])) {
            $this->setResult('user', array(
                'status'    => false,
                'message'   => 'The module can not co-exist with uclient module',
            ));

            return false;
        }

        return true;
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
            $options = Pi::service('module')->loadMeta($mod, 'user', true);
            if (empty($options)) {
                continue;
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
            $this->setResult('user', array(
                'status'    => false,
                'message'   => 'User profile generation failed: '
                . $exception->getMessage(),
            ));

            $result = false;
        }

        return $result;
    }

    /**
     * Set up user profile display settings
     *
     * @param Event $e
     *
     * @return bool
     */
    public function setupProfile(Event $e)
    {
        $result = null;
        $modelGroup = Pi::model('display_group', 'user');
        $modelField = Pi::model('display_field', 'user');

        // Get fields and groups
        $order = 1;
        $groups = array(
            '__BASIC__' => array(
                'title'     => _a('Basic profile'),
                'order'     => $order++,
                'compound'  => null,
            ),
        );
        $fieldList  = array();
        $fields     = Pi::registry('field', 'user')->read('', 'display');
        foreach ($fields as $field) {
            if ($field['type'] == 'compound') {
                $groups[$field['name']] = array(
                    'title'     => $field['title'],
                    'compound'  => $field['name'],
                    'order'     => $order++,
                );

                // Get compound fields
                $compoundMeta = Pi::registry('compound_field', 'user')->read($field['name']);
                foreach ($compoundMeta as $meta) {
                    $fieldList[$field['name']][$meta['name']] = 1;
                }

            } else {
                $fieldList['__BASIC__'][$field['name']] = 1;
            }
        }

        foreach ($groups as $groupName => $data) {
            $row = $modelGroup->createRow($data);
            $row->save();
            if (empty($fieldList[$groupName])) {
                continue;
            }
            $groupId = $row['id'];
            $fieldOrder = 1;
            foreach (array_keys($fieldList[$groupName]) as $fName) {
                $fData = array(
                    'field' => $fName,
                    'group' => $groupId,
                    'order' => $fieldOrder++,
                );
                $row = $modelField->createRow($fData);
                $row->save();
            }
        }

        Pi::registry('display_group', 'user')->flush();
        Pi::registry('display_field', 'user')->flush();

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
        $config = Pi::config()->load('service.user.php', false);
        $config['adapter'] = 'local';
        Pi::config()->write('service.user.php', $config, true);
        Pi::service('user')->reload($config);

        return true;
    }
}
