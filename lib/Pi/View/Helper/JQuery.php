<?php
/**
 * jQuery file helper
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\View
 * @subpackage      Helper
 */

namespace Pi\View\Helper;

use Pi;

/**
 * Helper for loading jQuery files
 *
 *
 * Usage inside a phtml template:
 * <code>
 *  // Load basic jQuery file
 *  $this->jQuery();
 *
 *  // Load specific file
 *  $this->jQuery('some.js');
 *
 *  // Load specific file with attributes
 *  $this->jQuery('some.js', array('conditional' => '...', 'position' => 'prepend'));
 *
 *  // Load a list of files
 *  $this->bootstrap(array(
 *      'some.css',
 *      'some.js',
 *  ));
 *
 *  // Load a list of files with corresponding attributes
 *  $this->bootstrap(array(
 *      'some.css' => array('media' => '...', 'conditional' => '...'),
 *      'some.js' => array(),
 *  ));
 * </code>
 */
class JQuery extends AssetCanonize
{
    const DIR_ROOT = 'vendor/jquery';
    protected static $rootLoaded;

    /**
     * Load jQuery files
     *
     * @param   null|string|array $files
     * @param   array $attributes
     * @return  void
     */
    public function __invoke($files = null, $attributes = array())
    {
        $files = $this->canonize($files, $attributes);
        if (empty(static::$rootLoaded)) {
            if (!isset($files['jquery.min.js'])) {
                $files = array('jquery.min.js' => $this->canonizeFile('jquery.min.js')) + $files;
            }
            static::$rootLoaded = true;
        }
        
        foreach ($files as $file => $attrs) {
            $file = static::DIR_ROOT . '/' . $file;
            $url = Pi::service('asset')->getStaticUrl($file, $file);
            $position = isset($file['position']) ? $file['position'] : 'append';
            if ('css' == $attrs['ext']) {
                $attrs['href'] = $url;
                if ('prepend' == $position) {
                    $this->view->headLink()->prependStylesheet($attrs);
                } else {
                    $this->view->headLink()->appendStylesheet($attrs);
                }
            } else {
                if ('prepend' == $position) {
                    $this->view->headScript()->prependFile($url, 'text/javascript', $attrs);
                } else {
                    $this->view->headScript()->appendFile($url, 'text/javascript', $attrs);
                }
            }
        }
        return $this;
    }
}
