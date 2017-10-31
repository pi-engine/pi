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

        // Get user base info
        $user = Pi::api('user', 'user')->get(
            $uid,
            array('name','country', 'city', 'time_activated'),
            true,
            true
        );

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
            'timeline'      => $timeline,
            'paginator'     => $paginator,
            'name'          => 'homepage',
            'user'          => $user,
        ));

        $this->view()->assign('view', false);
        $this->view()->headTitle(sprintf(__('%s activities') , $user['name']));
        $this->view()->headdescription(sprintf(__('View %s activities') , $user['name']), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }

    /**
     * Other view home page
     */
    public function viewAction()
    {
        $page   = $this->params('page', 1);
        $limit  = Pi::config('list_limit', 'user');
        $offset = (int) ($page -1) * $limit;
        $uid    = _get('uid');

        Pi::service('authentication')->requireLogin();

        // Get timeline
        $count    = Pi::api('timeline', 'user')->getCount($uid);
        $timeline = Pi::api('timeline', 'user')->get($uid, $limit, $offset);

        // Get timeline meta list
        $timelineMetaList = Pi::api('timeline', 'user')->getList();

        // Set timeline meta
        foreach ($timeline as &$item) {
            if (!isset($timelineMetaList[$item['timeline']])) {
                continue;
            }
            $item['icon']  = $timelineMetaList[$item['timeline']]['icon'];
            $item['title'] = $timelineMetaList[$item['timeline']]['title'];
        }

        // Get user base info
        $user = Pi::api('user', 'user')->get(
            $uid,
            array('name','country', 'city', 'time_activated'),
            true,
            true
        );

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
            'name'          => 'homepage',
            'timeline'      => $timeline,
            'paginator'     => $paginator,
            'user'          => $user,
        ));

        $this->view()->setTemplate('home-index');
        $this->view()->assign('view', true);
        $this->view()->headTitle(sprintf(__('%s activities') , $user['name']));
        $this->view()->headdescription(sprintf(__('View %s activities') , $user['name']), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }
    
    public function commentAction()
    {
        $page   = _get('page', 'int') ?: 1;
        $id = $this->params('uid', Pi::user()->getId());
        $view = $this->params('uid') ? true : false;
        $result = Pi::api('api', 'comment')->getComments($page, $id);        
      
        // Get user base info
        $user = Pi::api('user', 'user')->get(
            $id,
            array('name','country', 'city', 'time_activated'),
            true,
            true
        );
        
        $this->view()->assign('name', 'comment');
        $this->view()->assign('comment', array(
            'title'     => $this->config('head_title'),
            'count'     => $result['count'],
            'posts'     => $result['posts'],
            'paginator' => $result['paginator'],
        ));
        
        $this->view()->setTemplate('home-comment');
        $this->view()->assign('user', $user);
        $this->view()->assign('view', $view);
        $this->view()->assign('uid', $id);
        
        
    }
    
    public function itemAction()
    {
        $id = $this->params('uid', Pi::user()->getId());
        $view = $this->params('uid') ? true : false;
        
        // Get user base info
        $user = Pi::api('user', 'user')->get(
            $id,
            array('name','country', 'city', 'time_activated'),
            true,
            true
        );
        
        $owner = Pi::api('owner', 'guide')->getOwner($id, 'uid');
        $items = Pi::api('item', 'guide')->getListFromOwner($owner['id']);
        foreach ($items as $item) {
            if ($item['item_type'] == 'commercial') {
                $itemList['commercial'][$item['id']] = $item;
            } elseif ($item['item_type'] == 'person') {
                $itemList['person'][$item['id']] = $item;
            } else {
                $itemList['free'][$item['id']] = $item;
            }
        }
        $this->view()->assign('user', $user);
        $this->view()->assign('uid', $id);
        $this->view()->assign('name', 'item');
        $this->view()->assign('itemList', $itemList);
        $this->view()->assign('view', $view);
        $this->view()->setTemplate('home-item');
    }
    
    public function favoriteAction()
    {
        $id = $this->params('uid', Pi::user()->getId());
        $view = $this->params('uid') ? true : false;
        
        $favourites = Pi::api('favourite', 'favourite')->listFavourite($id);
        $count = 0;
        foreach ($favourites as $favourite) {
            $count += $favourite['total_item'];
        }
        
        // Get user base info
        $user = Pi::api('user', 'user')->get(
            $id,
            array('name','country', 'city', 'time_activated'),
            true,
            true
        );

        $this->view()->assign('user', $user);
        $this->view()->assign('count', $count);
        $this->view()->assign('uid', $id);
        $this->view()->assign('name', 'favorite');
        $this->view()->assign('favourites', $favourites);
        $this->view()->assign('view', $view);
        $this->view()->setTemplate('home-favorite');
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
