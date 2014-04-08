<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media;

use Pi;
use Module\Media\Installer\Action\Install;

trigger_error(sprintf('The class is discouraged, move corresponding methods to relevant APIs - %s', __FILE__), E_USER_WARNING);

/**
 * Common service API
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class Service
{
    /**
     * Module name
     * @var string 
     */
    protected static $module = 'media';
    
    /**
     * Get media filename and path
     * 
     * @param array $meta
     * @return array 
     */
    public static function getMediaFile($meta)
    {
        $config = Pi::service('config')->load(Install::CONFIG_FILE);
        
        // Get filename
        $namePattern = $config['upload']['source_hash'];
        $mimetype = explode('/', $meta['mimetype']);
        $filename = call_user_func($namePattern, array(
            'extension' => $mimetype[1],
            'id'        => $meta['id'],
        ));
        
        // Get file path
        $meta['type'] = $mimetype[0];
        $meta['extension'] = $mimetype[1];
        $pathPattern = $config['upload']['path'];
        $path = call_user_func($pathPattern, $meta);
        $target = sprintf(
            '%s/%s',
            $path,
            $filename
        );
        
        return array(
            'filename'  => $filename,
            'path'      => $target,
        );
    }
}
