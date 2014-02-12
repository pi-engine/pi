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

use Zend\View\Helper\AbstractHelper;

/**
 * Helper for canonizing CSS/JavaScript files
 *
 * Called by helpers
 *
 *  - Backbone
 *  - Bootstrap
 *  - Css
 *  - JQuery
 *  - Js
 *
 *
 * Usage inside a phtml template:
 *
 * ```
 *  // Canonize specific file
 *  $this->canonize('some.css');
 *
 *  // Canonize specific file with position
 *  $this->canonize('some.css', 'prepend');
 *
 *  // Canonize specific file with attributes
 *  $this->canonize('some.css',
 *      array('conditional' => '...', 'postion' => 'prepend'));
 *
 *  // Canonize a list of files with corresponding attributes
 *  $this->canonize(array(
 *      'a.css' => array('media' => '...', 'conditional' => '...'),
 *      'b.css',
 *  ));
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AssetCanonize extends AbstractHelper
{
    /**
     * Canonize attributes of a file
     *
     * Note: asset type is detected via file extension thus
     * attached versioning number should be removed for detection
     *
     * @param string $file
     * @param array $attrs
     * @return array
     * @see Pi\Application\Service\Asset::versionStamp()
     *      for versioning information
     */
    protected function canonizeFile($file, $attrs = array())
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $attrs['ext'] = $ext;
        if (false !== ($pos = strpos($ext, '?'))) {
            $ext = substr($ext, 0, $pos);
        }
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
            case 'js':
                if (!isset($attrs['src'])) {
                    $attrs['src'] = $file;
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
        $result = array();
        if (!$files) {
            return $result;
        }

        if ($files && is_string($files)) {
            if (is_string($attributes)) {
                $attributes = array(
                    'position' => $attributes,
                );
            }
            $files = array(
                $files => $attributes,
            );
        } elseif (!is_array($files)) {
            $files = (array) $files;
        }

        foreach ($files as $file => $attrs) {
            if (is_int($file)) {
                $file = $attrs;
                $attrs = array();
            }
            $attrs = $this->canonizeFile($file, $attrs);
            $result[$file] = $attrs;
        }

        return $result;
    }
}
