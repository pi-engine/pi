<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Controller\Front;

use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;

class ArticleController extends ActionController
{
    /**
     * A test page with a couple of API demos
     */
    public function indexAction()
    {
        $id = (int) $this->params('id', 1);
        $page = $this->params('p', 5);
        $paginator = Paginator::factory(100, array(
            'limit' => 10,
            'page'  => $page,
            'url_options'           => array(
                // Use router to build URL for each page
                'page_param'    => 'p',
                'total_param'   => 't',
                'params'        => array(
                    'id'             => $id,
                ),
            ),
        ));
        $this->view()->assign('id', $id);
        $this->view()->assign('paginator', $paginator);
    }
}
