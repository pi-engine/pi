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
 * Helper for load Google analytics code
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->ga('UA-XXXXX-X');
 *  $this->ga();
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
        var ga = document.createElement('script');
        ga.type = 'text/javascript';
        ga.async = true;
        ga.src = ('https:' == document.location.protocol
            ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(ga, s);
    })();
EOT;
        $account = $account ?: Pi::config('ga_account');
        $scripts = sprintf($gaScripts, $account);

        return $scripts;
    }
}
