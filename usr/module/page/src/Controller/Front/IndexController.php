<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        $this->view()->setTemplate('page-view');

        if (!$row instanceof RowGateway || !$row->active) {
            $title      = __('Page request');
            $content    = __('The page requested does not exist.');
            $markup     = '';
        } else {
            $content    = $row->content;
            $markup     = $row->markup ?: 'text';
            if ($content && 'pthml' != $markup) {
                $content = Pi::service('markup')->render(
                    $content, 
                    'html',
                    $markup
                );
            }
            $title = $row->title;
            // update clicks
            $model = $this->getModel('page');
            $model->increment('clicks', array('id' => $row->id));

            // Module config
            $config = Pi::config('', $this->getModule());
            // Set view
            $this->view()->headTitle($row->seo_title);
            $this->view()->headdescription($row->seo_description, 'set');
            $this->view()->headkeywords($row->seo_keywords, 'set');
            $this->view()->assign('config', $config);
        }

        $this->view()->assign(array(
            'title'     => $title,
            'content'   => $content,
            'markup'    => $markup,
        ));
        //return $content;
    }

    /**
     * Page render
     *
     * @see Module\Page\Route\Page
     */
    public function indexAction()
    {
        $id     = $this->params('id');
        //$name   = $this->params('name');
        $name   = $this->params('action');

        $row = null;
        if ($id) {
            $row = $this->getModel('page')->find($id);
        } elseif ($name) {
            $row = $this->getModel('page')->find($name, 'name');
            if (!$row) {
                $row = $this->getModel('page')->find($name, 'slug');
            }
        }
        if ($row->active) {
            $nav = Pi::registry('nav', $this->getModule())->read();
            if (isset($nav[$row->id])) {
                $nav[$row->id]['active'] = 1;
            } else {
                $nav = array();
            }
        } else {
            $nav = array();
        }
        $this->view()->assign('nav', $nav);

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
