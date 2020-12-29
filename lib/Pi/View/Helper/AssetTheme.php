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
 * Helper for building theme asset URI
 *
 * Usage inside a phtml template
 *
 * ```
 *  $cssUri = $this->assetTheme('css/style.css');
 *  $cssUri = $this->assetTheme('css/style.css', 'default');
 * ```
 *
 * @see    Pi\Application\Service\Asset
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AssetTheme extends AbstractHelper
{
    /**
     * Get URI of a theme asset
     *
     * @param string    $file
     * @param string    $theme
     * @param bool|null $appendVersion
     *
     * @return  string
     */
    public function __invoke(
        $file,
        $theme = '',
        $appendVersion = null
    ) {
        //$type = $isPublic ? 'public' : 'asset';

        $result = Pi::service('asset')->getThemeAsset(
            $file,
            $theme,
            $appendVersion
        );

        return $result;
    }
}
