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

/**
 * Common api class for providing APIs for resources
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Api
{
    /**
     * Load custom configuration file data.
     * 
     * In order to implement module clone, Load custom config data from
     * `custom/module/{module name}/{config file}` other than
     * `custom/module/article/{config file}`
     * 
     * @param string|null $name
     * @param string|null $module
     * @return type
     */
    public static function getCustomConfig($name, $module)
    {
        if (empty($module) || empty($name)) {
            return array();
        }
        
        Pi::service('i18n')->load(array('custom:module/' . $module, 'admin'));

        // Load module meta data
        $configFile = sprintf(
            '%s/module/article/config/module.php',
            Pi::path('custom')
        );
        if (!file_exists($configFile)) {
            return array();
        }
        $config     = include $configFile;
        $resources  = $config['resource'];
        
        $customConfigFile = isset($resources[$name]) ?$resources[$name] : '';
        if (empty($customConfigFile)) {
            return array();
        }
        
        $filename = sprintf(
            '%s/module/%s/config/%s',
            Pi::path('custom'),
            $module,
            $customConfigFile
        );
        if (!file_exists($filename)) {
            $filename = sprintf(
                '%s/article/config/%s',
                Pi::path('module'),
                $customConfigFile
            );
            if (!file_exists($filename)) {
                return array();
            }
        }
        
        return include $filename;
    }
}
