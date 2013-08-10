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
 * Helper for loading theme Intl resource
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->i18nTheme('main');
 *  $this->i18nTheme('main', 'default');
 *  $this->i18nTheme('main', null, 'en');
 * ```
 *
 * @see Pi\Application\Service\I18n
 * @see Pi\Application\Service\Asset
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class I18nTheme extends AbstractHelper
{
    /**
     * Load a theme i18n resource
     *
     * @param   string  $domain
     * @param   string|null  $theme
     * @param   string|null  $locale
     * @return  self
     */
    public function __invoke($domain, $theme = null, $locale = null)
    {
        Pi::service('i18n')->loadTheme($domain, $theme, $locale);

        return $this;
    }
}
