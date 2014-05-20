<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Comment\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;

class DemoController extends ActionController
{
    /**
     * Demo for article with comments
     */
    public function indexAction()
    {
        $id     = _get('id', 'int') ?: rand(1, 5);
        $page   = _get('page', 'int') ?: 1;
        $paginator = Paginator::factory(100, array(
            'limit' => 10,
            'page'  => $page,
            'url_options'           => array(
                'params'        => array(
                    'id'        => $id,
                    'enable'    => 'yes',
                ),
            ),
        ));
        $this->view()->assign(array(
            'title'     => sprintf(__('Demo article #%d'), $id),
            'paginator' => $paginator,
        ));

        $this->view()->setTemplate('demo');
    }
}
