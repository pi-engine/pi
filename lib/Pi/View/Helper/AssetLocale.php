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
     * Get URI of a module asset
     *
     * @param   string      $file
     * @param   string|null $locale
     * @param   bool        $versioning Flag to append version
     * @return  string
     */
    public function __invoke($file, $locale = null, $versioning = true)
    {
        $locale = $locale ?: Pi::service('i18n')->locale;
        $file = sprintf('locale/%s/%s', $locale, $file);

        return Pi::service('asset')->getThemeAsset($file, null, $versioning);
    }
}
