<?php
/**
 * Bootstrap file helper
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
 * Helper for loading Bootstrap files
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->bootstrap();
 *  $this->bootstrap('css/bootstrap.responsive.min.css');
 *  $this->bootstrap(array(
 *      'css/bootstrap.responsive.css',
 *      'js/bootstrap.js',
 *  ));
 * </code>
 */
class Bootstrap extends AbstractHelper
{
    const DIR_ROOT = 'vendor/bootstrap';
    protected $rootLoaded;

    /**
     * Load bootstrap files
     *
     * @param   null|string|array $file
     * @param   bool $includeCss To include bootstrap.min.css automatically
     * @return  void
     */
    public function __invoke($options = null, $includeCss = true)
    {
        $options = (array) $options;
        if (empty(static::$rootLoaded) && $includeCss) {
            if (!in_array('css/bootstrap.min.css', $options)) {
                array_unshift($options, 'css/bootstrap.min.css');
            }
            $this->rootLoaded = true;
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
