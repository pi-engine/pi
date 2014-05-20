<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Comment\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Install as BasicInstall;
use Pi\Application\Installer\Module as ModuleInstaller;
use Pi\Application\Installer\Resource\Comment as CommentResource;
use Zend\EventManager\Event;

class Install extends BasicInstall
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.post', array($this, 'checkModules'), 10);
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Check other modules and register comments if available
     *
     * @param Event $e
     * @return void
     */
    public function checkModules(Event $e)
    {
        $module = $e->getParam('module');

        $modules = Pi::registry('module')->read();
        if (isset($modules['comment'])) {
            unset($modules['comment']);
        }
        $moduleList = array_keys($modules);
        foreach ($moduleList as $mod) {
            $options = Pi::service('module')->loadMeta($mod, 'comment', true);
            if (empty($options)) {
                continue;
            }
            /*
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
            */

            $resourceHandler = new CommentResource($options);
            $e->setParam('module', $mod);
            $resourceHandler->setEvent($e);
            $resourceHandler->installAction();
        }

        $e->setParam('module', $module);
    }
}
