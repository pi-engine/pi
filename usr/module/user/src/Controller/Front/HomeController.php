<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;

class HomeController extends ActionController
{
    /**
     * Owner home page
     *
     * @return array|void
     */
    public function indexAction()
    {
        $page   = $this->params('p', 1);
        $limit  = 10;
        $offset = (int) ($page -1) * $limit;

        $isLogin = Pi::user()->hasIdentity();

        if (!$isLogin) {
            $this->jump(
                array('', array('controller' => 'login', 'action' => 'index')),
                __('Please login'),
                5
            );
            return;
        }

        $uid = Pi::user()->getIdentity();
        // Get user information
        $user = $this->getUser($uid);

        // Get timeline
        $count    = Pi::api('user', 'timeline')->getCount($uid);
        $timeline = Pi::api('user', 'timeline')->get($uid, $limit, $offset);

        // Get timeline meta list
        $timelineMetaList = Pi::api('user', 'timeline')->getList();

        // Set timeline meta
        foreach ($timeline as &$item) {
            $item['icon']  = $timelineMetaList[$item['timeline']]['icon'];
            $item['title'] = $timelineMetaList[$item['timeline']]['title'];
        }

        // Get activity meta for nav display
        $nav = $this->getNav($uid, 'homepage');

        // Get quick link
        $quicklink = $this->getQuicklink();


        // Set paginator
        $paginatorOption = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
            'controller' => 'profile',
            'action'     => 'home',
            'uid'        => $uid,
        );
        $paginator = $this->setPaginator($paginatorOption);

        $this->view()->assign(array(
            'uid'          => $uid,
            'user'         => $user,
            'timeline'     => $timeline,
            'paginator'    => $paginator,
            'quicklink'    => $quicklink,
            'nav'          => $nav,
        ));
    }

    /**
     * Other view home page
     */
    public function viewAction()
    {

    }


    /**
     * Set nav form home page profile and activity
     *
     * @param $uid
     * @return array
     */
    protected function getNav($uid, $cur)
    {
        // Get activity list
        $items = array();
        $nav = array(
            'cur'   => $cur,
            'items' => $items,
        );

        if (!$uid) {
            return $nav;
        }

        // Set homepage
        $homepageUrl = $this->url(
            'user',
            array(
                'controller' => 'profile',
                'action'     => 'home',
                'uid'        => $uid
            )
        );
        $items[] = array(
            'title' => __('Homepage'),
            'name'  => 'homepage',
            'url'   => $homepageUrl,
            'icon'  => '',
        );

        // Set profile
        $profileUrl = $this->url(
            'user',
            array(
                'controller' => 'profile',
                'action'     => 'index',
                'uid'        => $uid,
            )
        );
        $items[] = array(
            'title' => __('Profile'),
            'name'  => 'profile',
            'url'   => $profileUrl,
            'icon'  => '',
        );

        // Set activity
        $activityList = Pi::api('user', 'activity')->getList();
        foreach ($activityList as $key => $value) {
            $url = $this->url(
                'user',
                array(
                    'controller' => 'activity',
                    'action'     => 'index',
                    'uid'        => $uid,
                    'name'       => $key,
                )
            );
            $items[] = array(
                'title' => $value['title'],
                'name'  => $key,
                'icon'  => $value['icon'],
                'url'   => $url,
            );
        }

        $nav['items'] = $items;

        return $nav;

    }

}