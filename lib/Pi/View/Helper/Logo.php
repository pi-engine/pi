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
 * Helper for building logo URL
 *
 * Look up logo in following locations:
 *  - asset/custom/image/<logo-name>
 *  - asset/theme-<theme-name>/image/<logo-name>
 *  - static/image/<logo-name>
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Logo extends AbstractHelper
{
    /**
     * Output logo URL
     *
     * @param  string $name Logo filename
     *
     * @return string
     */
    public function __invoke($name = '')
    {
        return Pi::service('asset')->logo($name);
    }
}
