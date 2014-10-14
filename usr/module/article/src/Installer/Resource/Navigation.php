<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Installer\Resource;

use Pi;
use Pi\Application\Installer\Resource\Navigation as ParentNavigation;

/**
 * Custom navigation handler
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Navigation extends ParentNavigation
{
    /**
     * Load custom navigation config
     */
    protected function loadConfig()
    {
        $module = $this->getModule();
        $config = Api::getCustomConfig('navigation', $module);
        
        if (!empty($config)) {
            $this->config = $config;
        }
    }
    
   /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        $this->loadConfig();
        return parent::installAction();
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        $this->loadConfig();
        return parent::updateAction();
    }
}
