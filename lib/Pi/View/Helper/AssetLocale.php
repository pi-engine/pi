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
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for building locale asset URI inside current theme
 *
 *
 * Usage inside a phtml template
 *
 * ```
 *  $cssUri = $this->assetLocale('rtl.css');
 *  $jsUri = $this->assetLocale('rtl.js', 'en);
 * ```
 *
 * @see Pi\Application\Service\Asset
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AssetLocale extends AbstractHelper
{
    /**
     * Get URI of a theme locale asset
     *
     * @param   string      $file
     * @param   string      $locale
     * @param   bool|null $appendVersion
     *
     * @return  string
     */
    public function __invoke(
        $file,
        $locale = '',
        $appendVersion = null
    ) {
        //$type = $isPublic ? 'public' : 'asset';
        $locale = $locale ?: Pi::service('i18n')->locale;
        $file = sprintf('locale/%s/%s', $locale, $file);

        $result = Pi::service('asset')->getThemeAsset(
            $file,
            '',
            $appendVersion
        );

        return $result;
    }
}
