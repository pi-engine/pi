<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Media\Dispatch;

use Pi;

class Local extends AbstractDispatch
{
    /**
     * Directly copy file from source to target
     * 
     * @param string $source
     * @param string $target
     * @return boolean
     * @throws \Exception 
     */
    public function copy($source, $target)
    {
        if (!file_exists($source)) {
            throw new \Exception('Source file is not exists');
        }
        $targetPath = dirname($target);
        if (!is_dir($targetPath)) {
            if (!mkdir($targetPath, 0777, true)) {
                throw new \Exception('Cannot create target path');
            }
        }
        
        $result = copy($source, $target);
        
        return $result;
    }
}
