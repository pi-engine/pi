<?php
/**
 * CSS/JavaScript file canonization helper
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
use Zend\View\Helper\AbstractHelper;
use Zend\Stdlib\ArrayUtils;

/**
 * Helper for canonizing CSS/JavaScript files, called by helpers: Backbone
 *
 *
 * Usage inside a phtml template:
 * <code>
 *  // Canonize specific file
 *  $this->canonize('some.css');
 *
 *  // Canonize specific file with position
 *  $this->canonize('some.css', 'prepend');
 *
 *  // Canonize specific file with attributes
 *  $this->canonize('some.css', array('conditional' => '...', 'postion' => 'prepend'));
 *
 *  // Canonize a list of files
 *  $this->canonize(array(
 *      'a.css',
 *      'b.css',
 *  ));
 *
 *  // Canonize a list of files with corresponding attributes
 *  $this->canonize(array(
 *      'a.css' => array('media' => '...', 'conditional' => '...'),
 *      'b.css' => array(),
 *  ));
 * </code>
 */
class AssetCanonize extends AbstractHelper
{
    /**
     * Canonize attributes of a file
     *
     * @param string $file
     * @param array $attrs
     * @return array
     */
    protected function canonizeFile($file, $attrs = array())
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $attrs['ext'] = strtolower($ext);
        switch ($ext) {
            case 'css':
                if (!isset($attrs['href'])) {
                    $attrs['href'] = $file;
                }
                if (!isset($attrs['rel'])) {
                    $attrs['rel'] = 'stylesheet';
                }
                if (!isset($attrs['type'])) {
                    $attrs['type'] = 'text/css';
                }
                if (!isset($attrs['media'])) {
                    $attrs['media'] = 'screen';
                }
                break;
            default:
                break;
        }

        return $attrs;
    }

    /**
     * Canonize files and corresponding attributes
     *
     * @param   null|string|array $files
     * @param   array $attributes
     * @return array
     */
    protected function canonize($files = null, $attributes = array())
    {
        if ($files && is_string($files)) {
            $files = array(
                $files => $attributes,
            );
        } elseif (!is_array($files)) {
            $files = (array) $files;
        }
        if (!ArrayUtils::hasStringKeys($files)) {
            $files = array_fill_keys($files, array());
        }

        foreach ($files as $file => &$attrs) {
            $attrs = $this->canonizeFile($file, $attrs);
        }
        return $files;
    }
}
