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

/**
 * Activity controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class ActivityController extends ActionController
{
    /**
     * Display activity
     *
     * @return array|void
     */
    public function indexAction()
    {
        $name  = $this->params('name', '');
        $uid   = Pi::user()->getIdentity();
        $limit = 10;

        // Redirect login page if not logged in
        if (!$uid) {
            $this->jump(
                'user',
                array('controller' => 'login', 'action' => 'index'),
                __('Need login'),
                2
            );
        }

        if (!$name) {
            $this->jumpTo404('An error occur');
        }

        // Get user base info
        $user = Pi::api('user', 'user')->get(
            $uid,
            array('name', 'gender', 'birthdate'),
            true
        );


        // Get activity list for nav display
        $activityList = Pi::api('user', 'activity')->getList();

        // Get activity contents
        $activityContents = Pi::api('user', 'activity')->get($uid, $name, $limit);

        $this->view()->assign(array(
            'activity_list'     => $activityList,
            'activity_contents' => $activityContents,
            'cur_activity'      => $name,
            'user'              => $user,
            'nav'               => $this->getNav('activity', $uid),
            'is_owner'          => true,
        ));
    }

    /**
     * Set nav form home page profile and activity
     *
     * @param $uid
     * @return array
     */
    protected function getNav($cur, $uid = '')
    {
        // Get activity list
        $items = array();
        $nav   = array(
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
}
