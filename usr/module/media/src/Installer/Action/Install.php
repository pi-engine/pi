<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Install as BasicInstall;
use Zend\EventManager\Event;

/**
 * Custom install class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Install extends BasicInstall
{
    /**
     * Config file name which will copy to var folder
     */
    const CONFIG_FILE   = 'module.media.php';
    
    /**
     * Attach method to listener
     * 
     * @return \Module\Media\Installer\Action\Install 
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.post', array($this, 'initModuleConfigs'), 1);
        parent::attachDefaultListeners();
        return $this;
    }
    
    /**
     * Add a module.media.php configuration file into the var/config/custom
     * folder
     * 
     * @param Event $e 
     */
    public function initModuleConfigs(Event $e)
    {
        $source = sprintf(
            '%s/media/data/%s',
            Pi::path('module'),
            self::CONFIG_FILE
        );
        $target = sprintf('%s/custom/module.media.php', Pi::path('config'));
        $result = true;
        if (!file_exists($target)) {
            $result = copy($source, $target);
        }
        
        return $result;
    }
}
