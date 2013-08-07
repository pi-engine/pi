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
 * Helper for loading jQuery files
 *
 * Usage inside a phtml template
 *
 * ```
 *  // Load basic jQuery file
 *  $this->jQuery();
 *
 *  // Load specific file
 *  $this->jQuery('some.js');
 *
 *  // Load specific file with attributes
 *  $this->jQuery('some.js',
 *      array('conditional' => '...', 'position' => 'prepend'));
 *
 *  // Load a list of files
 *  $this->jQuery(array(
 *      'some.css',
 *      'some.js',
 *  ));
 *
 *  // Load a list of files with corresponding attributes
 *  $this->jQuery(array(
 *      'some.css' => array('media' => '...', 'conditional' => '...'),
 *      'some.js',
 *  ));
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class JQuery extends AssetCanonize
{
    /** @var string Root dir of jQuery */
    const DIR_ROOT = 'vendor/jquery';

    /** @var bool jQuery basic file is loaded */
    protected static $rootLoaded;

    /**
     * Load jQuery files
     *
     * @param   null|string|array $files
     * @param   array $attributes
     * @return  self
     */
    public function __invoke($files = null, $attributes = array())
    {
        $files = $this->canonize($files, $attributes);
        if (empty(static::$rootLoaded)) {
            if (!isset($files['jquery.min.js'])) {
                $files = array('jquery.min.js' =>
                        $this->canonizeFile('jquery.min.js'))
                    + $files;
            }
            static::$rootLoaded = true;
        }

        foreach ($files as $file => $attrs) {
            $file = static::DIR_ROOT . '/' . $file;
            $url = Pi::service('asset')->getStaticUrl($file, $file);
            $position = isset($file['position'])
                ? $file['position'] : 'append';
            if ('css' == $attrs['ext']) {
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
