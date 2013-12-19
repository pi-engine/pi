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

class Curl extends AbstractDispatch
{
    /**
     * Upload file by curl
     * 
     * @param string $source
     * @param string $target
     * @return boolean
     * @throws \Exception 
     */
    public function copy($source, $target)
    {
        $configs = $this->configs;
        if (!isset($configs['upload_file']) 
            || empty($configs['upload_file'])
        ) {
            throw new \Exception('A server url is needed to process file');
        }
        
        $data = array(
            'target'    => $target,
            //'auth'      => $configs['authorization'],
            'file'      => '@' . $source,
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $configs['upload_file']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        return $result;
    }
}
