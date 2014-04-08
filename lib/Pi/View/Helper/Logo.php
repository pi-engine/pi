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
use Zend\View\Helper\AbstractHtmlElement;

/**
 * Helper for building logo URL
 *
 * Look up logo in following locations:
 *  - asset/custom/image/<logo-name>
 *  - asset/theme-<theme-name>/image/<logo-name>
 *  - static/image/<logo-name>
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Logo extends AbstractHtmlElement
{
    /**
     * Output logo URL
     *
     * @param  string $name    Logo filename
     *
     * @return string
     */
    public function __invoke($name = '')
    {
        $src = '';
        $name = $name ?: 'logo.png';

        $customFile = 'asset/custom/image/' . $name;
        if (file_exists(Pi::path($customFile))) {
            $src = Pi::url($customFile);
        } else {
            $theme = Pi::service('theme')->current();
            $component = 'theme/' . $theme;
            $asset = 'image/' . $name;
            $file = Pi::service('asset')->getAssetPath($component, $asset);
            if (file_exists($file)) {
                $src = Pi::service('asset')->getAssetUrl($component, $asset);
            } else {
                $file = 'static/image/' . $name;
                if (file_exists(Pi::path($file))) {
                    $src = Pi::url($file);
                }
            }
        }

        return $src;
    }
}
