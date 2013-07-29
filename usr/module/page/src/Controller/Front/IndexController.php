<?php
/**
 * Page index controller
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
 * @package         Module\Page
 * @version         $Id$
 */

namespace Module\Page\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Db\RowGateway\RowGateway;
use Zend\Mvc\MvcEvent;
use Zend\Db\Sql\Expression;

class IndexController extends ActionController
{
    protected function render($row)
    {
        
        if (!$row instanceof RowGateway || !$row->active) {
            $title = __('Page request');
            $content = __('The page requested does not exist.');
        } else {
            $content = $row->content;
            if ($content) {
                //$content = Pi::service('markup')->render($content, $row->markup ?: 'text');
                $content = Pi::service('markup')->render($content, 'html', $row->markup ?: 'text');
            }
            $title = $row->title;
            // Specify page head title 
            $this->view()->headTitle($row->title);
            $this->view()->headDescription($row->seo_description, 'set');
            $this->view()->headKeywords($row->seo_keywords, 'set');
            // Update clicks
            $model = $this->getModel('page');
            $model->update(array('clicks' => new Expression('`clicks` + 1')), array('id' => $row->id));
            // Add css and javascript  
            $style = (!empty($row->style)) ? $row->style : '';
            $script = (!empty($row->script)) ? $row->script : '';
            // Module config 
            $config = Pi::service('registry')->config->read($this->getModule());
        }

        // Set view
        $this->view()->setTemplate('page-view');
        $this->view()->assign(array(
            'title'     => $title,
            'content'   => $content,
            'style'     => $style,
            'script'    => $script,
            'config'    => $config,
            'page-id'   => sprintf('page-%s', $row->id), // Use page-id on <body> tag for custom each page
        ));
    }

    /**
     * Access a page via
     *  1. /url/page/123
     *  2. /url/page/my-slug
     *  3. /url/page/action/123
     *  4. /url/page/action/my-slug
     */
    public function indexAction()
    {
        $id = $this->params()->get('id', 'int');
        $slug = $this->params('slug', 'StripTags');

        $row = null;
        if ($id) {
            $row = $this->getModel('page')->find($id);
        } elseif ($slug) {
            $row = $this->getModel('page')->find($slug, 'slug');
        }

        $this->render($row);
    }

    /**
     * Transform an "action" token into a method name
     *
     * @param  string $action
     * @return string
     */
    public static function getMethodFromAction($action)
    {
        return 'indexAction';
    }
}
