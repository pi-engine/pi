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
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for building theme asset URI
 *
 * Usage inside a phtml template
 *
 * ```
 *  $cssPath = $this->assetThemePath('css/style.css');
 *  $cssPath = $this->assetThemePath('css/style.css', 'default');
 * ```
 *
 * @see Pi\Application\Service\Asset
 * @author Frédéric TISSOT
 */
class AssetThemePath extends AbstractHelper
{
    /**
     * Get URI of a theme asset
     *
     * @param   string      $file
     * @param   string      $theme
     * @param   bool|null   $appendVersion
     *
     * @return  string
     */
    public function __invoke(
        $file,
        $theme = '',
        $appendVersion = null,
        $searchParent = false
    ) {
        //$type = $isPublic ? 'public' : 'asset';

        $result = Pi::service('asset')->getThemeAssetPath(
            $file,
            $theme,
            $appendVersion,
            $searchParent
        );

        return $result;
    }
}
