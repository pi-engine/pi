<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;

/**
 * Home controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class HomeController extends ActionController
{
    /**
     * Owner home page
     *
     * @return array|void
     */
    public function indexAction()
    {
        $page   = $this->params('page', 1);
        $limit  = Pi::config('list_limit', 'user');
        $offset = (int) ($page -1) * $limit;

        Pi::service('authentication')->requireLogin();
        Pi::api('profile', 'user')->requireComplete();
        $uid = Pi::user()->getId();
        /*
        // Check profile complete
        if ($this->config('profile_complete_form')) {
            $completeProfile = Pi::api('user', 'user')->get($uid, 'level');
            if (!$completeProfile) {
                $this->redirect()->toRoute(
                    'user',
                    array(
                        'controller' => 'register',
                        'action' => 'profile.complete',
                    )
                );
                return;
            }
        }
        */
        // Get user information
        //$user = $this->getUser($uid);

        // Get timeline
        $count    = Pi::api('timeline', 'user')->getCount($uid);
        $timeline = Pi::api('timeline', 'user')->get($uid, $limit, $offset);

        // Get timeline meta list
        $timelineMetaList = Pi::api('timeline', 'user')->getList();

        // Set timeline meta
        foreach ($timeline as &$item) {
            $item['icon']  = $timelineMetaList[$item['timeline']]['icon'];
            $item['title'] = $timelineMetaList[$item['timeline']]['title'];
        }

        // Get activity meta for nav display
        //$nav = Pi::api('nav', 'user')->getList('homepage');

        // Get quick link
        //$quicklink = Pi::api('quicklink', 'user')->getList();


        // Set paginator
        $paginatorOption = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
            'controller' => 'home',
            'action'     => 'index',
        );
        $paginator = $this->setPaginator($paginatorOption);

        $this->view()->assign(array(
            'uid'           => $uid,
            //'user'          => $user,
            'timeline'      => $timeline,
            'paginator'     => $paginator,
            'name'          => 'homepage',
            //'quicklink'    => $quicklink,
            //'owner'     => true,
            //'nav'          => $nav,
        ));
    }

    /**
     * Other view home page
     */
    public function viewAction()
    {
        $page   = $this->params('page', 1);
        $limit  = Pi::config('list_limit', 'user');
        $offset = (int) ($page -1) * $limit;

        $uid = $this->params('uid', '');

        /*
        // Get user information
        $user = $this->getUser($uid);
        if (!$user) {
            $this->jump(
                array(
                    'controller' => 'profile',
                    'action'     => 'index'
                ),
                __('User was not found.'),
                'error'
            );
        }

        // Get viewer role: public member follower following owner
        $role = Pi::user()->hasIdentity() ? 'member' : 'public';
        $user = Pi::api('privacy', 'user')->filterProfile(
            $uid,
            $role,
            $user,
            'user'
        );
        */

        // Get timeline
        $count    = Pi::api('timeline', 'user')->getCount($uid);
        $timeline = Pi::api('timeline', 'user')->get($uid, $limit, $offset);

        // Get timeline meta list
        $timelineMetaList = Pi::api('timeline', 'user')->getList();

        // Set timeline meta
        foreach ($timeline as &$item) {
            $item['icon']  = $timelineMetaList[$item['timeline']]['icon'];
            $item['title'] = $timelineMetaList[$item['timeline']]['title'];
        }

        // Get activity meta for nav display
        //$nav = Pi::api('nav', 'user')->getList('homepage', $uid);

        // Get quick link
        //$quicklink = Pi::api('quicklink', 'user')->getList();

        // Set paginator
        $paginatorOption = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
            'controller' => 'home',
            'action'     => 'view',
            'uid'        => $uid,
        );
        $paginator = $this->setPaginator($paginatorOption);

        $this->view()->assign(array(
            'uid'           => $uid,
            //'user'          => $user,
            'name'          => 'homepage',
            'timeline'      => $timeline,
            'paginator'     => $paginator,
            //'quicklink'    => $quicklink,
            //'owner'         => false,
            //'nav'          => $nav,
        ));

        $this->view()->setTemplate('home-index');
    }

    /**
     * Set paginator
     *
     * @param $option
     * @return \Pi\Paginator\Paginator
     */
    protected function setPaginator($option)
    {
        $params = array(
            'module'        => $this->getModule(),
            'controller'    => $option['controller'],
            'action'        => $option['action'],
        );

        if (isset($option['uid'])) {
            $params['uid'] = $option['uid'];
        }

        $paginator = Paginator::factory(intval($option['count']), array(
            'limit' => $option['limit'],
            'page'  => $option['page'],
            'url_options'   => array(
                'params'    => $params
            ),
        ));

        return $paginator;

    }
}
