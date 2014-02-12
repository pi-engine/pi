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
use Zend\View\Helper\HeadTitle as ZendHeadTitle;

/**
 * Helper for setting and retrieving title element for HTML head
 *
 * @see \Zend\View\Helper\HeadTitle for details.
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HeadTitle extends ZendHeadTitle
{
    /**
     * Retrieve placeholder for title element and optionally set state
     *
     * @param  string $title
     * @param  string $setType
     * @return $this
     */
    public function __invoke($title = null, $setType = null)
    {
        if (null !== $setType) {
            $setType = strtoupper($setType);
        }

        return parent::__invoke($title, $setType);
    }
}
