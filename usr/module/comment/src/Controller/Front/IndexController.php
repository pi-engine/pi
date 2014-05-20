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

class IndexController extends ActionController
{
    /**
     * Demo for article with comments
     */
    public function indexAction()
    {
        //$this->redirect('', array('controller' => 'demo'));
        $title = sprintf(__('Comment portal for %s'), Pi::config('sitename'));
        $links = array(
                /*
            'build'   => array(
                'title' => __('Build comment data for demo articles'),
                'url'   => $this->url('', array(
                    'controller'    => 'demo',
                    'action'        => 'build'
                )),
            ),
            */
            'demo'   => array(
                'title' => __('Demo article with comments'),
                'url'   => $this->url('', array(
                    'controller'    => 'demo'
                )),
            ),
            /*
            'all'   => array(
                'title' => __('All comment posts'),
                'url'   => Pi::api('api', 'comment')->getUrl('list', array(
                    'active'  => null,
                )),
            ),
            */
            'all-active'   => array(
                'title' => __('All active comment posts'),
                'url'   => Pi::api('api', 'comment')->getUrl('list', array(
                    'active'  => 1,
                )),
            ),
            /*
            'all-inactive'   => array(
                'title' => __('All inactive comment posts'),
                'url'   => Pi::api('api', 'comment')->getUrl('list', array(
                    'active'  => 0,
                )),
            ),
            */
            'article'   => array(
                'title' => __('Commented articles'),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'article',
                )),
            ),
            'module'   => array(
                'title' => __('Comment posts for module "Comment"'),
                'url'   => Pi::api('api', 'comment')->getUrl('module', array(
                    'name'  => 'comment',
                )),
            ),
            'type'   => array(
                'title' => __('Comment posts for module "Comment" with type "Article"'),
                'url'   => Pi::api('api', 'comment')->getUrl('module', array(
                    'name'      => 'comment',
                    'type'  => 'article',
                )),
            ),
            'user'   => array(
                'title' => sprintf(
                    __('Comment posts by %s'),
                    Pi::service('user')->get(1, 'name')
                ),
                'url'   => Pi::api('api', 'comment')->getUrl('user', array(
                    'uid'   => 1,
                )),
            ),
        );
        if ($uid = Pi::service('user')->getId()) {
            $links['my-post'] = array(
                'title' => __('Comment posts by me'),
                'url'   => Pi::api('api', 'comment')->getUrl('user', array(
                    'uid'   => $uid,
                )),
            );
            $links['my-article'] = array(
                'title' => __('Commented articles by me'),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'article',
                    'uid'           => $uid,
                )),
            );
        }
        $this->view()->assign(array(
            'title' => $title,
            'links' => $links,
        ));
        $this->view()->setTemplate('comment-index');
    }

    /**
     * Action for comment JavaScript loading
     */
    public function loadAction()
    {
        $uri = $this->params('uri');
        $content = Pi::service('comment')->loadContent($uri);
        $result = array(
            'status'    => 1,
            'content'   => $content,
        );

        return $result;
    }
}
