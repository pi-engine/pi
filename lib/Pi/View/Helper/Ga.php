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
use Laminas\View\Helper\AbstractHelper;

/**
 * Helper for register/render Google analytics code
 *
 * Usage inside a phtml template
 *
 * ```
 *  // Specific mode
 *  $this->ga('UA-XXXXX-X');
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
     * @param string $trackingId
     *
     * @return  $this
     */
    public function __invoke($trackingId = '')
    {
        if (!$trackingId) {
            $trackingId = Pi::config('ga_account');
        }
        if (false !== ($pos = strpos($trackingId, ';'))) {
            $trackingId = trim(substr($trackingId, 0, $pos));
        }
        if (!$trackingId) {
            return '';
        }

        $gaScripts
            = <<<'EOT'
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '%s');
EOT;

        $scripts = sprintf($gaScripts, $trackingId);
        $url     = sprintf('https://www.googletagmanager.com/gtag/js?id=%s', $trackingId);

        // Set script
        $this->view->headScript()->appendFile($url, 'text/javascript', ['async' => 'async']);
        $this->view->headScript()->appendScript($scripts);

        return $this;
    }
}