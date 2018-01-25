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

use Zend\View\Helper\AbstractHelper;

/**
 * Helper for theme template inclusion
 *
 * Usage inside a phtml template
 *
 * ```
 *  include $this->templateTheme('header.phtml');
 * ```
 *
 * @see Pi\View\Resolver\ThemeTemplate
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class TemplateTheme extends AbstractHelper
{
    /**
     * Get full path to a theme template
     *
     * @param   string $template
     * @param   string|null $theme , not implemented yet
     * @return  string
     */
    public function __invoke($template, $theme = null)
    {
        return $this->getView()->resolver($template, $this->view);
    }
}
