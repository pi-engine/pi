<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Laminas\View\Helper\AbstractHelper;

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
     * @param array  $attrs
     *
     * @return array
     * @see Pi\Application\Service\Asset::versionStamp()
     *      for versioning information
     */
    protected function canonizeFile($file, $attrs = [])
    {
        $ext          = strtolower(pathinfo($file, PATHINFO_EXTENSION));
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
     * @param null|string|array $files
     * @param array             $attributes
     *
     * @return array
     */
    protected function canonize($files = null, $attributes = [])
    {
        $result = [];
        if (!$files) {
            return $result;
        }

        if ($files && is_string($files)) {
            if (is_string($attributes)) {
                $attributes = [
                    'position' => $attributes,
                ];
            }
            $files = [
                $files => $attributes,
            ];
        } elseif (!is_array($files)) {
            $files = (array)$files;
        }

        foreach ($files as $file => $attrs) {
            if (is_int($file)) {
                $file  = $attrs;
                $attrs = [];
            }
            $attrs         = $this->canonizeFile($file, $attrs);
            $result[$file] = $attrs;
        }

        return $result;
    }
}
