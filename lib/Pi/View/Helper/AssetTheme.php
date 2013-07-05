<?php
/**
 * Theme asset helper
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
 * Helper for building theme asset URI
 * @see Pi\Application\Service\Asset
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->assetTheme('css/style.css');
 *  $this->assetTheme('css/style.css', 'default');
 * </code>
 */
class AssetTheme extends AbstractHelper
{
    /**
     * Get URI of a module asset
     *
     * @param   string      $file
     * @param   string|null $theme
     * @param   bool        $versioning Flag to append version
     * @return  string
     */
    public function __invoke($file, $theme = null, $versioning = true)
    {
        return Pi::service('asset')->getThemeAsset($file, $theme, $versioning);
    }
}
