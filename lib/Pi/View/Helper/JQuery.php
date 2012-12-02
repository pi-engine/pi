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
 * @since           3.0
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for loading jQuery files
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->jQuery();
 *  $this->jQuery('extension.js');
 *  $this->jQuery(array('ext1.js', 'ext2.js'));
 * </code>
 */
class JQuery extends AbstractHelper
{
    const DIR_ROOT = 'vendor/jquery';
    protected static $rootLoaded;

    /**
     * Load jQuery files
     *
     * @param   null|string|array $file
     * @return  void
     */
    public function __invoke($options = null)
    {
        //$root = Pi::url('static') . '/' . static::DIR_ROOT;
        //$rootPath = Pi::path('static') . '/' . static::DIR_ROOT;
        $options = (array) $options;
        if (!static::$rootLoaded) {
            if (!in_array('jquery.min.js', $options)) {
                array_unshift($options, 'jquery.min.js');
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
