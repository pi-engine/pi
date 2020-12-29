<?php
/**
 * @author Frédéric TISSOT  <contact@espritdev.fr>
 */
namespace Module\Page\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Page\Form\SitemapForm;

class ToolsController extends ActionController
{
    public function sitemapAction()
    {
        $form = new SitemapForm('sitemap');
       
        $message = __('Rebuild the module links in sitemap module tables');
        if ($this->request->isPost()) {
            Pi::api('sitemap', 'page')->sitemap();
            $message = __('Sitemap rebuild finished');
        }
        
        $this->view()->assign('title', __('Rebuild sitemap links'));
        $this->view()->assign('form', $form);
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('tools-sitemap');
    }
}
