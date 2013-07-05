<?php
/**
 * Google analytics code helper
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for load Google analytics code
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->ga('UA-XXXXX-X');
 *  $this->ga();
 * </code>
 */
class Ga extends AbstractHelper
{
    /**
     * Load GA scripts
     *
     * @param   string  $account
     * @return  string
     */
    public function __invoke($account = '')
    {
        $gaScripts = <<<'EOT'
    // GA account ID
    var userAccount = '%s';

    // Google Analytics for Pi Engine, don't change below
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', userAccount]);
    _gaq.push(['_trackPageview']);

    (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
EOT;
        $account = $account ?: Pi::config('ga_account');
        $scripts = sprintf($gaScripts, $account);
        return $scripts;
    }
}
