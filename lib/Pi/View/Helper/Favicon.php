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
 * Helper for building favicon URL
 *
 * Look up logo in following locations:
 *  - asset/custom/image/<favicon-name>
 *  - www/<favicon-name>
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Favicon extends AbstractHelper
{
    /**
     * Output favicon URL
     *
     * @param  string $name Favicon filename
     *
     * @return string
     */
    public function __invoke($name = '')
    {
        $name = $name ?: 'favicon.ico';
        $customFile = 'asset/custom/image/' . $name;
        if (file_exists(Pi::path($customFile))) {
            $src = Pi::url($customFile);
        } else {
            $src = Pi::url('www/' . $name);
        }

        return $src;
    }
}
