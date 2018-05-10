<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
        $offset = (int)($page - 1) * $limit;

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
            ['name', 'country', 'city', 'time_activated'],
            true,
            true
        );

        // Set paginator
        $paginatorOption = [
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
            'controller' => 'home',
            'action'     => 'index',
        ];
        $paginator       = $this->setPaginator($paginatorOption);

        $this->view()->assign([
            'uid'       => $uid,
            'timeline'  => $timeline,
            'paginator' => $paginator,
            'name'      => 'homepage',
            'user'      => $user,
        ]);

        $this->view()->assign('view', false);
        $this->view()->headTitle(sprintf(__('%s activities'), $user['name']));
        $this->view()->headdescription(sprintf(__('View %s activities'), $user['name']), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }

    /**
     * Other view home page
     */
    public function viewAction()
    {
        // Check front disable
        if ($this->config('disable_front')) {
            return $this->jumpToDenied(__('View information is disable'));
        }

        $page   = $this->params('page', 1);
        $limit  = Pi::config('list_limit', 'user');
        $offset = (int)($page - 1) * $limit;
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
        $user         = Pi::api('user', 'user')->get(
            $uid,
            ['name', 'country', 'city', 'time_activated'],
            true,
            true
        );
        $user['name'] = isset($user['name']) ? $user['name'] : null;

        // Set paginator
        $paginatorOption = [
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
            'controller' => 'home',
            'action'     => 'view',
            'uid'        => $uid,
        ];
        $paginator       = $this->setPaginator($paginatorOption);

        $this->view()->assign([
            'uid'       => $uid,
            'name'      => 'homepage',
            'timeline'  => $timeline,
            'paginator' => $paginator,
            'user'      => $user,
        ]);

        $this->view()->setTemplate('home-index');
        $this->view()->assign('view', true);
        $this->view()->headTitle(sprintf(__('%s activities'), $user['name']));
        $this->view()->headdescription(sprintf(__('View %s activities'), $user['name']), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }

    /**
     * Set paginator
     *
     * @param $option
     * @return \Pi\Paginator\Paginator
     */
    protected function setPaginator($option)
    {
        $params = [
            'module'     => $this->getModule(),
            'controller' => $option['controller'],
            'action'     => $option['action'],
        ];

        if (isset($option['uid'])) {
            $params['uid'] = $option['uid'];
        }

        $paginator = Paginator::factory(intval($option['count']), [
            'limit'       => $option['limit'],
            'page'        => $option['page'],
            'url_options' => [
                'params' => $params,
            ],
        ]);

        return $paginator;

    }
}
