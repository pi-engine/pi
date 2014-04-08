<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Sitemap feed controller
 */
class SitemapController extends ActionController
{
    /**
     * Default action
     */
    public function indexAction()
    {
        // Disable debugger message
        Pi::service('log')->mute();

        $this->view()->setTemplate(false)->setLayout('layout-content');
        $sitemapConfig = Pi::registry('navigation')->read('sitemap')
            ?: Pi::registry('navigation')->read('front');
        $sitemap = $this->view()->navigation($sitemapConfig)->sitemap();
        $content = $sitemap->setFormatOutput(true)->render();

        return $content;
    }

}
