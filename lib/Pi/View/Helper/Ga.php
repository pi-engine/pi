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
 * Helper for register/render Google analytics code
 *
 * Usage inside a phtml template
 *
 * ```
 *  // Specific mode
 *  $this->ga('UA-XXXXX-X', 'pi-engine.tld');
 *
 *  // Or specific mode
 *  $this->ga('UA-XXXXX-X; pi-engine.tld');
 *
 *  // Default mode
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
     * @param   string  $trackingId
     * @param   string  $host
     *
     * @return  $this
     */
    public function __invoke($trackingId = '', $host = '')
    {
        if (!$trackingId) {
            $trackingId   = Pi::config('ga_account');
        }
        $hostConfig = '';
        if (false !== ($pos = strpos($trackingId, ';'))) {
            $hostConfig = trim(substr($trackingId, $pos + 1));
            $trackingId = trim(substr($trackingId, 0, $pos));
        }
        if (!$trackingId) {
            return '';
        }
        if (!$host) {
            $host = $hostConfig ?: $_SERVER['HTTP_HOST'];
        }

        $gaScripts =<<<'EOT'
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '%s', '%s');
  ga('send', 'pageview');
EOT;

        $scripts = sprintf($gaScripts, $trackingId, $host);
        $this->view->headScript()->appendScript($scripts);

        return $this;
    }
}
