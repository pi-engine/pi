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
 * @since           3.0
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for loading Backbone files
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->backbone();
 *  $this->backbone('some.js');
 *  $this->backbone(array(
 *      'some.css',
 *      'some.js',
 *  ));
 * </code>
 */
class Backbone extends AbstractHelper
{
    const DIR_ROOT = 'vendor/backbone';
    protected static $rootLoaded;

    /**
     * Load bootstrap files
     *
     * @param   null|string|array $file
     * @return  void
     */
    public function __invoke($options = null)
    {
        $options = (array) $options;
        if (!static::$rootLoaded) {
            // Required primary js
            if (!in_array('backbone.min.js', $options)) {
                array_unshift($options, 'backbone-min.js');
            }
            // Required underscore js
            if (!in_array('underscore.min.js', $options)) {
                array_unshift($options, 'underscore-min.js');
            }
            static::$rootLoaded = true;
        }
        foreach ($options as $file) {
            $fileExtension = substr($file, strrpos( $file, '.' ) + 1);
            $file = static::DIR_ROOT . '/' . $file;
            $url = Pi::service('asset')->getStaticUrl($file, $file);
            if ($fileExtension == 'css') {
                $this->view->headLink()->appendStylesheet($url);
            } else {
                $this->view->headScript()->appendFile($url);
            }
        }
        return $this;
    }
}
