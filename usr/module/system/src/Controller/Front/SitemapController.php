<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Sitemap feed controller
 *
 * @deprecated
 */
class SitemapController extends ActionController
{
    /**
     * Default action
     */
    public function indexAction()
    {
        $this->redirect()->toRoute('home');
        return;

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
