<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article;

use Pi;

/**
 * File service API
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class File
{
    protected static $module = 'article';

    /**
     * Add content into file, if the file is not exists, create one
     * 
     * @param string  $filename  Absolute filename
     * @param string  $content   Content want to insert
     * @param bool    $truncate  Whether to truncate file
     * @return boolean 
     */
    public static function addContent(
        $filename, 
        $content = null, 
        $truncate = true
    ) {
        $path     = dirname($filename);
        $result   = self::mkdir($path);
        if (!$result) {
            return false;
        }
        
        if (!file_exists($filename)) {
            chmod($path, 0777);
        }
        $mode   = $truncate ? 'w' : 'a';
        $handle = fopen($filename, $mode);
        if (!$handle) {
            return false;
        }
        $result = fwrite($handle, $content);
        
        return $result;
    }
    
    /**
     * Create directory if it is not exists
     * 
     * @param string  $dir  Absolute directory
     * @return bool 
     */
    public static function mkdir($dir)
    {
        $result = true;

        if (!file_exists($dir)) {
            $oldumask = umask(0);

            $result   = mkdir($dir, 0777, TRUE);

            umask($oldumask);
        }

        return $result;
    }
}
