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

use Pi;
use Laminas\View\Helper\AbstractHelper;

/**
 * Helper for building module asset PATH
 *
 * Usage inside a phtml template
 *
 * ```
 *  $svgPath = $this->assetModulePath('image/image.svg');
 *  $svgPath = $this->assetModulePath('image/image.svg', 'demo');
 * ```
 *
 * @see Pi\Application\Service\Asset
 * @author Mickaël STAMM <contact@sta2m.com>
 */
class AssetModulePath extends AbstractHelper
{
    /**
     * Get URI of a module asset
     *
     * @param   string $file
     * @param   string $module
     * @param   bool|null $appendVersion
     *
     * @return  string
     */
    public function __invoke(
        $file,
        $module = '',
        $appendVersion = null
    )
    {
        //$type = $isPublic ? 'public' : 'asset';
        $module = $module ?: Pi::service('module')->current();

        // Check if customized asset available in current theme
        $customAssets = Pi::registry('theme_module_asset')->read($module);
        if (!empty($customAssets[$file])) {
            $result = $customAssets[$file];
            // Load original module asset
        } else {
            $result = Pi::service('asset')->getModuleAssetPath(
                $file,
                $module,
                $appendVersion
            );
        }

        return $result;
    }
}
