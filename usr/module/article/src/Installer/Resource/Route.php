<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Installer\Resource;

use Pi\Application\Installer\Resource\Route as BasicRoute;
use Pi;

/**
 * Custom route resource install class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Route extends BasicRoute
{
    /**
     * Route resource file 
     */
    const RESOURCE_CONFIG_NAME = 'resource.route.php';
    
    /**
     * Installing route resource
     * 
     * @return bool 
     */
    public function installAction()
    {
        $module     = $this->event->getParam('module');
        $filename   = sprintf(
            'var/%s/config/%s', 
            $module, 
            self::RESOURCE_CONFIG_NAME
        );
        $configPath = Pi::path($filename);
        if (file_exists($configPath)) {
            $configs      = include $configPath;
            $class        = '';
            foreach ($configs as $config) {
                $class    = $config['type'];
                break;
            }
            if (class_exists($class)) {
                // Remove exists article custom route
                Pi::model('route')->delete(array('module' => $module));
                $this->config = $configs;
            }
        }
        
        return parent::installAction();
    }
}
