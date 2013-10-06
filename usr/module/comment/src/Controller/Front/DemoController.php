<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
            'page'  => $page,
            'url_options'           => array(
                'params'        => array(
                    'id'        => $id,
                    'enable'    => 'yes',
                ),
            ),
        ));
        $this->view()->assign('id', $id);
        $this->view()->assign('paginator', $paginator);

        $this->view()->setTemplate('demo');
    }

    public function buildAction()
    {
        $roots = Pi::model('root', 'comment')->delete(array(
            'module'    => 'comment',
        ));
        foreach ($roots as $root) {
            Pi::api('comment')->delete($root->id);
        }

        $rootIds = array();
        for ($i = 1; $i <= 10; $i++) {
            $root = array(
                'module'    => 'comment',
                'item'      => $i,
                'category'  => 'article',
                'active'    => rand(0, 1),
            );
            $rootIds[$i] = Pi::api('comment')->addRoot($root);
        }

        for ($i = 0; $i < 1000; $i++) {
            $post = array(
                'root'      => $rootIds[rand(1, 10)],
                'uid'       => rand(1, 5),
                'ip'        => Pi::service('user')->getIp(),
                'active'    => rand(0, 1),
                'content'   => sprintf(__('Demo comment %d.'), $i + 1),
                'time'      => time() - rand(100, 100000),
            );
            Pi::api('comment')->addPost($post);
        }

        //exit();
        $this->redirect('comment', array(
            'action'    => 'index',
            'id'        => rand(1, 5),
            'enable'    => 'yes',
        ));
    }
}
