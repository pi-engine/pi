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
use Pi\Application\Installer\Resource\Permission as ParentPerm;

/**
 * Custom permission handler
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Permission extends ParentPerm
{
    /**
     * Load custom navigation config
     */
    protected function loadConfig()
    {
        $module = $this->getModule();
        $config = Api::getCustomConfig('permission', $module);
        
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
