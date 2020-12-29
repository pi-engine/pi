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
use Laminas\View\Helper\AbstractHtmlElement;

/**
 * Helper for loading `push.js` html5 notification
 *
 * Usage inside a phtml template
 *
 * $this->notification();
 * $this->notification($option);
 *
 * $option = [
 *     'section' => 'front',
 *     'url'     => 'PUT_URL_HERE', // if put url section not needed
 *     'time'    => 15000,
 * ];
 *
 *
 * @see    https://pushjs.org/
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
        // Set section
        $section = isset($option['section']) ? $option['section'] : 'front';

        // Set url
        if (isset($option['url']) && !empty($option['url'])) {
            $url = $option['url'];
        } elseif (Pi::service('module')->isActive('notification')) {
            $url = Pi::url(
                Pi::service('url')->assemble(
                    'default',
                    [
                    'module'     => 'notification',
                    'controller' => 'check',
                    'action'     => 'index',
                    'section'    => $section,
                ]
                )
            );
        } else {
            return false;
        }

        // Set time
        $time = isset($option['time']) ? $option['time'] : 15000;

        // Set js file
        $js = 'vendor/pushjs/push.min.js';
        $js = Pi::service('asset')->getStaticUrl($js);

        // Set js script
        $scripts
                 = <<<'EOT'
$(function(){
    setInterval(oneSecondFunction, %s);
});

function oneSecondFunction() {
    $.getJSON('%s').done(function (result) {
        if (result.status == 1) {
            Push.create(result.title, {
                body: result.body,
                icon: result.logo,
                link: result.link,
                timeout: 3000,
                onClick: function () {
                    if (result.link) {
                        window.location.href = result.link;
                    }
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
    }
}
