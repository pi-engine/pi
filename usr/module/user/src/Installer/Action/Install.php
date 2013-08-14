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
use Pi\Application\Installer\Action\Install as BasicInstall;
use Pi\Application\Installer\Module as ModuleInstaller;
use Pi\Application\Installer\Resource\User as UserResource;
use Zend\EventManager\Event;

class Install extends BasicInstall
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.post', array($this, 'checkModules'), 1);
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
            $meta = Pi::service('module')->loadMeta($mod, 'maintenance');
            if (empty($meta['resource']['user'])) {
                continue;
            }
            $options = $meta['resource']['user'];
            if (is_string($options)) {
                $optionsFile = sprintf(
                    '%s/%s/config/%s',
                    Pi::path('module'),
                    Pi::service('module')->directory($mode),
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
}
