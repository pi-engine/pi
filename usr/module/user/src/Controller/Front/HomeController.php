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
        $nav = $this->getNav('homepage');

        // Get quick link
        $quicklink = $this->getQuicklink();


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
            'uid'          => $uid,
            'user'         => $user,
            'timeline'     => $timeline,
            'paginator'    => $paginator,
            'quicklink'    => $quicklink,
            'is_owner'     => true,
            'nav'          => $nav,
        ));
    }

    /**
     * Other view home page
     */
    public function viewAction()
    {
        $page   = $this->params('p', 1);
        $limit  = 10;
        $offset = (int) ($page -1) * $limit;

        $uid = $this->params('uid', '');
        if (!$uid) {
            return $this->jumpTo404(__('Invalid user ID!'));
        }
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
        $nav = $this->getNav('homepage', $uid);

        // Get quick link
        $quicklink = $this->getQuicklink();


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
            'uid'          => $uid,
            'user'         => $user,
            'timeline'     => $timeline,
            'paginator'    => $paginator,
            'quicklink'    => $quicklink,
            'is_owner'     => false,
            'nav'          => $nav,
        ));
    }


    /**
     * Set nav form home page profile and activity
     *
     *
     * @param string $cur
     * @param string $uid
     *
     * @return array
     */
    protected function getNav($cur, $uid = '')
    {
        // Get activity list
        $items = array();
        $nav = array(
            'cur'   => $cur,
            'items' => $items,
        );

        if (!$uid) {
            // Owner nav

            // Set homepage
            $homepageUrl = $this->url(
                'user',
                array(
                    'controller' => 'home',
                    'action'     => 'index',
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
        } else {
            // Other view
            // Set homepage
            $homepageUrl = $this->url(
                'user',
                array(
                    'controller' => 'home',
                    'action'     => 'index',
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
        }

        return $nav;

    }

    /**
     * Get quicklink
     *
     * @param null $limit
     * @param null $offset
     * @return array
     */
    protected function getQuicklink($limit = null, $offset = null)
    {
        $result = array();
        $model = $this->getModel('quicklink');
        $where = array(
            'active'  => 1,
            'display' => 1,
        );
        $columns = array(
            'id',
            'name',
            'title',
            'module',
            'link',
            'icon',
        );

        $select = $model->select()->where($where);
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }

        $select->columns($columns);
        $rowset = $model->selectWith($select);

        foreach ($rowset as $row) {
            $result[] = $row->toArray();
        }

        return $result;

    }

    /**
     * Get user information for profile page head display
     *
     * @param $uid
     * @return array user information
     */
    protected function getUser($uid)
    {
        $result = Pi::api('user', 'user')->get(
            $uid,
            array('name', 'gender', 'birthdate'),
            true
        );

        return $result;
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
