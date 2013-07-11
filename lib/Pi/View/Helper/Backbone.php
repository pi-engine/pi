<?php
/**
 * Backbone file helper
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
 * Helper for loading Backbone files
 *
 * Usage inside a phtml template:
 * <code>
 *  // Load basic backbone and underscore
 *  $this->backbone();
 *
 *  // Load specific file
 *  $this->backbone('some.js');
 *
 *  // Load specific file with attributes
 *  $this->backbone('some.js', array('conditional' => '...', 'position' => 'prepend'));
 *
 *  // Load a list of files
 *  $this->backbone(array(
 *      'some.css',
 *      'some.js',
 *  ));
 *
 *  // Load a list of files with corresponding attributes
 *  $this->backbone(array(
 *      'some.css' => array('media' => '...', 'conditional' => '...'),
 *      'some.js' => array(),
 *  ));
 * </code>
 */
class Backbone extends AssetCanonize
{
    const DIR_ROOT = 'vendor/backbone';
    protected static $rootLoaded;

    /**
     * Load bootstrap files
     *
     * @param   null|string|array $files
     * @param   array $attributes
     * @return  void
     */
    public function __invoke($files = null, $attributes = array())
    {
        $files = $this->canonize($files, $attributes);
        if (!static::$rootLoaded) {
            $autoLoad = array();
            // Required underscore js
            if (!isset($files['underscore.min.js'])) {
                $autoLoad += array('underscore-min.js' => $this->canonizeFile('underscore-min.js'));
            }
            // Required primary js
            if (!isset($files['backbone.min.js'])) {
                $autoLoad += array('backbone-min.js' => $this->canonizeFile('backbone-min.js'));
            }
            $files = $autoLoad + $files;
            static::$rootLoaded = true;
        }

        foreach ($files as $file => $attrs) {
            $file = static::DIR_ROOT . '/' . $file;
            $url = Pi::service('asset')->getStaticUrl($file, $file);
            $position = isset($attrs['position']) ? $attrs['position'] : 'append';
            if ($attrs['ext'] == 'css') {
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
