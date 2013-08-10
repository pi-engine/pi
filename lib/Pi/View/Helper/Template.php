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
 * Helper for template inclusion
 *
 * Usage inside a phtml template
 *
 * ```
 *  include $this->template('module/demo:admin/public_index.phtml');
 *  include $this->template('lib/Pi/Captcha/Image:form.phtml');
 *  include $this->template('header.phtml');
 * ```
 *
 * @see Pi\View\Resolver\ModuleTemplate
 * @see Pi\View\Resolver\ThemeTemplate
 * @see Pi\View\Resolver\ComponentTemplate
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Template extends AbstractHelper
{
    /**
     * Get full path to a module template
     *
     * @param   string  $template
     * @return  string
     */
    public function __invoke($template)
    {
        return $this->getView()->resolver($template);
    }
}
