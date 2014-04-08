<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
 *
 *  // Load i18n
 *  $this->angular(array(
 *      <...>,
 *      'i18n',
 *  ));
 *
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

    /** @var bool i18n is loaded */
    protected static $i18nLoaded;

    /**
     * Load angular files
     *
     * @param   null|string|array $files
     * @param   array $attributes
     * @param   bool|null $appendVersion
     *
     * @return  $this
     */
    public function __invoke(
        $files = null,
        $attributes = array(),
        $appendVersion = null
    ) {
        $files = $this->canonize($files, $attributes);
        $dev = 'development' == Pi::environment();
        if (isset($files['i18n'])) {
            unset($files['i18n']);
            if (!static::$i18nLoaded) {
                $locale = Pi::config('locale');
                if ('en' != $locale) {
                    $file = 'i18n/angular-locale_' . $locale . '.js';
                    $files[$file] = $this->canonizeFile($file);
                }
                static::$i18nLoaded = true;
            }
        }
        if (!static::$rootLoaded) {
            $autoLoad = array();
            // Required primary js
            $primaryFile = 'angular.js';
            if (!isset($files[$primaryFile])) {
                $autoLoad += array(
                    $primaryFile => $this->canonizeFile($primaryFile)
                );
            }
            $files = $autoLoad + $files;
            static::$rootLoaded = true;
        }
        foreach ($files as $file => $attrs) {
            if (!$dev) {
                $file = preg_replace('/\.js$/', '.min.js', $file); 
            }

            $file = static::DIR_ROOT . '/' . $file;
            $url = Pi::service('asset')->getStaticUrl($file, $appendVersion);
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
