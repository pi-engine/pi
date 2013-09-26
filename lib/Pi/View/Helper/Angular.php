<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;

/**
 * Helper for loading AngularJS files
 *
 * Loads raw files for development mode and compressed files for other modes
 *
 * Usage inside a phtml template
 *
 * ```
 *  // Load basic angular.js
 *  $this->angular();
 *
 *  // Load specific file
 *  $this->angular('some.js');
 *
 *  // Load specific file with attributes
 *  $this->angular('some.js',
 *      array('conditional' => '...', 'position' => 'prepend'));
 *
 *  // Load a list of files
 *  $this->angular(array(
 *      'a.js',
 *      'b.js',
 *  ));
 *
 *  // Load a list of files with corresponding attributes
 *  $this->angular(array(
 *      'a.js' => array('media' => '...', 'conditional' => '...'),
 *      'b.js',
 *  ));
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Angular extends AssetCanonize
{
    /** @var string Angular root directory */
    const DIR_ROOT = 'vendor/angular';

    /** @var bool Angular basic file is loaded */
    protected static $rootLoaded;

    /**
     * Load angular files
     *
     * @param   null|string|array $files
     * @param   array $attributes
     * @return  self
     */
    public function __invoke($files = null, $attributes = array())
    {
        $files = $this->canonize($files, $attributes);
        if (!static::$rootLoaded) {
            $autoLoad = array();
            // Required primary js
            if ('development' == Pi::environment()) {
                $primaryFile = 'angular.js';
                $backupFile = 'angular.min.js';
            } else {
                $primaryFile = 'angular.min.js';
                $backupFile = 'angular.js';
            }
            if (!isset($files[$primaryFile])) {
                $autoLoad += array(
                    $primaryFile => $this->canonizeFile($primaryFile)
                );
            }
            if (!isset($files[$backupFile])) {
                unset($files[$backupFile]);
            }
            $files = $autoLoad + $files;
            static::$rootLoaded = true;
        }

        foreach ($files as $file => $attrs) {
            $file = static::DIR_ROOT . '/' . $file;
            $url = Pi::service('asset')->getStaticUrl($file, $file);
            $position = isset($attrs['position'])
                ? $attrs['position'] : 'append';
            if ($attrs['ext'] == 'css') {
                $attrs['href'] = $url;
                if ('prepend' == $position) {
                    $this->view->headLink()->prependStylesheet($attrs);
                } else {
                    $this->view->headLink()->appendStylesheet($attrs);
                }
            } else {
                if ('prepend' == $position) {
                    $this->view->headScript()
                        ->prependFile($url, 'text/javascript', $attrs);
                } else {
                    $this->view->headScript()
                        ->appendFile($url, 'text/javascript', $attrs);
                }
            }
        }

        return $this;
    }
}
