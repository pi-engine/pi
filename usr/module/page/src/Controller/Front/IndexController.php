<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
            $title = __('Page request');
            $content = __('The page requested does not exist.');
        } else {
            $content = $row->content;
            if ($content) {
                $content = Pi::service('markup')->render(
                    $content, 
                    'html',
                    $row->markup ?: 'text'
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
        $name   = $this->params('name');

        $row = null;
        if ($id) {
            $row = $this->getModel('page')->find($id);
        } elseif ($name) {
            $row = $this->getModel('page')->find($name, 'slug');
            if (!$row) {
                $row = $this->getModel('page')->find($name, 'name');
            }
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
