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
 * Helper for template rendering context
 *
 * Usage inside a phtml template
 *
 * ```
 *  // Set context
 *  $this->context(<context-name>);
 *  // Get context
 *  $context = $this->context();
 * ```
 *
 * @see Pi\View\Resolver\ThemeTemplate
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Context extends AbstractHelper
{
    /** @var string Context name */
    protected $context = '';

    /**
     * Set/get context
     *
     * @param   string|null  $context
     * @return  string|this
     */
    public function __invoke($context = null)
    {
        if (null === $context) {
            return $this->context;
        }
        $this->context = $context;

        return $this;
    }
}
