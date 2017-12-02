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
use Zend\View\Helper\AbstractHtmlElement;

/**
 * Helper for loading `push.js` html5 notification
 *
 * Usage inside a phtml template
 *
 * $this->notification();
 *
 * @see https://pushjs.org/
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class Notification extends AbstractHtmlElement
{
    /**
     * Render html5 notification
     *
     * @param array $option
     *
     * @return  html
     */
    public function __invoke($option = [])
    {
        // Check notification module install
        if (!Pi::service('module')->isActive('notification')) {
            return false;
        }

        // Set section
        $section = isset($option['section']) ? $option['section'] : 'front';

        // Set time
        $time = isset($option['time']) ? $option['time'] : 15000;

        // Make notification url
        $url = Pi::url(Pi::service('url')->assemble('default', [
            'module'     => 'notification',
            'controller' => 'check',
            'action'     => 'index',
            'section'    => $section,
        ]));

        // Set js file
        $js = 'vendor/pushjs/push.min.js';
        $js = Pi::service('asset')->getStaticUrl($js);

        // Set js script
        $scripts = <<<'EOT'
$(function(){
    setInterval(oneSecondFunction, %s);
});

function oneSecondFunction() {
    $.getJSON('%s').done(function (result) {
        if (result.status == 1) {
            Push.create(result.title, {
                body: result.body,
                icon: result.logo,
                timeout: 3000,
                onClick: function () {
                    window.focus();
                    this.close();
                }
            });
        }
    });
}
EOT;
        $scripts = sprintf($scripts, $time, $url);

        // Load js
        $this->view->footScript()->appendFile($js);
        $this->view->footScript()->appendScript($scripts);

        return $this;
    }
}