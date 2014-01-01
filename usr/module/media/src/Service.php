<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Media;

use Pi;
use Pi\Mvc\Controller\ActionController;
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
     * Render form
     * 
     * @param Pi\Mvc\Controller\ActionController $obj  ActionController instance
     * @param Zend\Form\Form $form     Form instance
     * @param string         $message  Message assign to template
     * @param bool           $isError  Whether is error message
     */
    public static function renderForm(
        ActionController $obj,
        $form,
        $message = null,
        $isError = true
    ) {
        $params = array('form' => $form);
        if ($isError) {
            $params['error'] = $message;
        } else {
            $params['message'] = $message;
        }
        $obj->view()->assign($params);
    }
    
    /**
     * Output file
     * 
     * @param array $options 
     */
    public static function httpOutputFile(array $options)
    {
        if ((!isset($options['file']) && !isset($options['raw']))) {
            if (!$options['silent']) {
                header('HTTP/1.0 404 Not Found');
            }
            exit();
        }
        if (isset($options['file']) && !is_file($options['file'])) {
            if (!$options['silent']) {
                header('HTTP/1.0 403 Forbidden');
            }
            exit();
        }
        if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
            $options['fileName'] = urlencode($options['fileName']);
        }
        $options['fileSize'] = isset($options['file']) 
            ? filesize($options['file']) : strlen($options['raw']);

        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        header("Pragma: public");
        header('Content-Description: File Transfer');
        if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE') === false) {
            header('Content-Type: application/force-download; charset=UTF-8');
        } else {
            header('Content-Type: application/octet-stream; charset=UTF-8');
        }
        header('Content-Disposition: attachment; filename="' . $options['fileName'] . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Pragma: public');
        header('Content-Length: ' . $options['fileSize']);
        ob_clean();
        flush();

        if (!empty($options['file'])) {
            readfile($options['file']);
            if (!empty($options['deleteFile'])) {
                @unlink($options['file']);
            }
        } else {
            echo $options['raw'];
            ob_flush();
            flush();
        }
        if (empty($options['notExit'])) {
            exit();
        }
    }
    
    /**
     * Create directory if it is not exists
     * 
     * @param string  $dir  Absolute directory
     * @return bool 
     */
    public static function mkdir($dir)
    {
        trigger_error(sprintf(
            'Method %s is deprecated, use %s.',
            __METHOD__,
            'Pi\Service\File::mkdir'
        ));

        $result = true;

        if (!file_exists($dir)) {
            $oldumask = umask(0);

            $result   = mkdir($dir, 0777, TRUE);

            umask($oldumask);
        }

        return $result;
    }
    
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
