<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Admin;

use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Zend\Db\Sql\Expression;
use Pi;

/**
 * List controller
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ListController extends ActionController
{
    /**
     * Get application title by appkey
     * 
     * @param array $appkeys
     * @return array
     */
    protected function getAppTitle($appkeys)
    {
        $result = array();
        $modelApp = $this->getModel('application');
        $rowApp = $modelApp->select(array('appkey' => $appkeys));
        foreach ($rowApp as $row) {
            $result[$row->appkey] = $row->title ?: $row->name;
        }
        unset($rowApp);
        unset($modelApp);
        
        return $result;
    }
    
    /**
     * Get category title by category ids
     * 
     * @param array $category
     * @return array
     */
    protected function getCategoryTitle($category)
    {
        $result = array();
        $modelCategory = $this->getModel('category');
        $rowCategory = $modelCategory->select(array('id' => $category));
        foreach ($rowCategory as $row) {
            $result[$row->id] = $row->title ?: $row->name;
        }
        unset($rowCategory);
        unset($modelCategory);
        
        return $result;
    }


    /**
     * List all media
     * 
     * @return ViewModel
     */
    public function indexAction()
    {
        $active = $this->params('status', null);
        if ($active !== null) {
            $active = (int) $active;
        }
        $page   = (int) $this->params('p', 1);
        $limit  = (int) $this->config('page_limit') > 0
            ? $this->config('page_limit') : 20;
        $offset = ($page - 1) * $limit;
        
        $where = array();
        $params = array();
        if (1 === $active) {
            $where['active'] = 1;
            $params['status'] = 1;
        } elseif (0 === $active) {
            $where['active'] = 0;
            $params['status'] = 1;
        }
        $delete = $this->params('delete', 0);
        if ($delete) {
            $where['time_deleted > ?'] = 0;
        } else {
            $where['time_deleted'] = 0;
        }
        $params['delete'] = $delete;

        // Get media list
        $module = $this->getModule();
        $resultset = Pi::api('doc', $module)->getList(
            $where,
            $limit,
            $offset,
            'time_created DESC'
        );
        
        $uids = $appkeys = array();
        $apps = $users = $avatars = array();
        foreach ($resultset as $row) {
            $appkeys[] = $row['appkey'];
            $uids[]    = $row['uid'];
        }
        // Get application title
        if (!empty($appkeys)) {
            $apps = $this->getAppTitle($appkeys);
        }
        
        // Get users
        if (!empty($uids)) {
            $users = Pi::user()->get($uids);
            $avatars = Pi::avatar()->get($uids);
        }
        
        // Total count
        $totalCount = $this->getModel('doc')->count($where);

        // Paginator
        /*
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit)
            ->setCurrentPageNumber($page)
            ->setUrlOptions(array(
                'page_param' => 'p',
                'router'     => $this->getEvent()->getRouter(),
                'route'      => $this->getEvent()
                    ->getRouteMatch()
                    ->getMatchedRouteName(),
                'params'     => array_filter(array_merge(array(
                    'module'        => $this->getModule(),
                    'controller'    => 'list',
                    'action'        => 'index',
                ), $params)),
            ));
        */
        $paginator = Paginator::factory($totalCount, array(
            'page' => $page,
            'url_options'   => array(
                'page_param' => 'p',
                'params'     => array_filter(array_merge(array(
                    'module'        => $this->getModule(),
                    'controller'    => 'list',
                    'action'        => 'index',
                ), $params)),
            ),
        ));

        $navTabs = array(
            array(
                'active'    => null === $active && !$delete,
                'label'     => _a('All resources'),
                'href'      => $this->url('', array(
                    'action'    => 'index',
                )),
            ),
            array(
                'active'    => 1 === $active && !$delete,
                'label'     => _a('Active resources'),
                'href'      => $this->url('', array(
                    'action'    => 'index',
                    'status'    => 1,
                )),
            ),
            array(
                'active'    => 0 === $active && !$delete,
                'label'     => _a('Inactive resources'),
                'href'      => $this->url('', array(
                    'action'    => 'index',
                    'status'    => 0,
                )),
            ),
            array(
                'active'    => $delete,
                'label'     => _a('Deleted resources'),
                'href'      => $this->url('', array(
                    'action'    => 'index',
                    'delete'    => 1,
                )),
            ),
        );
        
        $this->view()->assign(array(
            'title'      => _a('Resource List'),
            'apps'       => $apps,
            'medias'     => $resultset,
            'paginator'  => $paginator,
            'tabs'       => $navTabs,
            'users'      => $users,
            'avatars'    => $avatars,
            'active'     => $active,
            'delete'     => $delete,
        ));
    }
    
    /**
     * List media by application
     * 
     * @return ViewModel
     */
    public function applicationAction()
    {
        $app = $this->params('appkey', null);
        
        if (empty($app)) {
            // Fetch data count
            $model = $this->getModel('doc');
            $select = $model->select()
                ->columns(array(
                    'appkey',
                    'module',
                    'token',
                    'count' => new Expression('count(id)')
                ))->group(array('appkey', 'module', 'token'));
            $rowset = $model->selectWith($select)->toArray();
            
            // Canonize data
            $appkeys = array(0);
            $result = array();
            foreach ($rowset as $row) {
                $appkeys[] = $row['appkey'];
                $appkey = $row['appkey'];
                $module = $row['module'];
                $token = $row['token'];
                if (isset($result[$appkey])) {
                    $result[$appkey]['count'] += $row['count'];
                } else {
                    $result[$appkey]['count'] = $row['count'];
                    $result[$appkey]['url'] = $this->url('', array(
                        'action' => 'application',
                        'appkey' => $appkey,
                    ));
                }
                if (isset($result[$appkey][$module])) {
                    $result[$appkey][$module]['count'] += $row['count'];
                } else {
                    $result[$appkey][$module]['count'] = $row['count'];
                    $result[$appkey][$module]['url'] = $this->url('', array(
                        'action' => 'application',
                        'appkey' => $appkey,
                        'name'   => $module,
                    ));
                }
                if (isset($result[$appkey][$module][$token]['count'])) {
                    $result[$appkey][$module][$token]['count'] 
                        += $row['count'];
                } else {
                    $result[$appkey][$module][$token]['count'] 
                        = $row['count'];
                    $result[$appkey][$module][$token]['url'] 
                        = $this->url('', array(
                            'action'   => 'application',
                            'appkey'   => $appkey,
                            'name'     => $module,
                            'token'    => $token,
                        ));
                }
            }
            
            // Get application and category title
            $apps = $this->getAppTitle($appkeys);
            
            $this->view()->assign(array(
                'title'      => _a('Resource List by Application'),
                'items'      => $result,
                'apps'       => $apps,
            ));
            $this->view()->setTemplate('list-application-select');
            return;
        }
        
        $module   = $this->params('name', null);
        $token    = $this->params('token', 0);
        $active   = $this->params('status', null);
        $delete   = $this->params('delete', 0);
        $page     = (int) $this->params('p', 1);
        $limit    = (int) $this->config('page_limit') > 0
            ? $this->config('page_limit') : 20;
        $offset   = ($page - 1) * $limit;
        $active = $active === null ? $active : (int) $active;
        
        $where = array(
            'appkey'   => $app,
            'module'   => $module,
            'token'    => $token,
        );
        $where = array_filter($where);
        $params = array(
            'appkey'   => $app,
            'name'     => $module,
            'token'    => $token,
        );
        $params = array_filter($params);
        $navParams = $params;
        
        if (1 === $active) {
            $where['active'] = 1;
            $params['status'] = 1;
        } elseif (0 === $active) {
            $where['active'] = 0;
            $params['status'] = 1;
        }
        
        $delete = $this->params('delete', 0);
        if ($delete) {
            $where['time_deleted > ?'] = 0;
        } else {
            $where['time_deleted'] = 0;
        }
        $params['delete'] = $delete;

        // Get media list
        $resultset = Pi::api('doc', $this->getModule())->getList(
            $where,
            $limit,
            $offset,
            'time_created'
        );
        
        $uids = $appkeys = array();
        $apps = $users = $avatars = array();
        foreach ($resultset as $row) {
            $appkeys[] = $row['appkey'];
            $uids[]    = $row['uid'];
        }
        // Get application title
        if (!empty($appkeys)) {
            $apps = $this->getAppTitle($appkeys);
        }
        
        // Get users
        if (!empty($uids)) {
            $users = Pi::user()->get($uids);
            $avatars = Pi::avatar()->get($uids);
        }
        
        // Total count
        $totalCount = $this->getModel('doc')->count($where);

        // Paginator
        /*
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit)
            ->setCurrentPageNumber($page)
            ->setUrlOptions(array(
                'page_param' => 'p',
                'router'     => $this->getEvent()->getRouter(),
                'route'      => $this->getEvent()
                    ->getRouteMatch()
                    ->getMatchedRouteName(),
                'params'     => array_filter(array_merge(array(
                    'controller'    => 'list',
                    'action'        => 'application',
                ), $params)),
            ));
        */
        $paginator = Paginator::factory($totalCount, array(
            'page'          => $page,
            'url_options'   => array(
                'page_param'    => 'p',
                'params'        => array_filter(array_merge(array(
                    'controller'    => 'list',
                    'action'        => 'application',
                ), $params)),
            ),
        ));

        $navTabs = array(
            array(
                'active'    => null === $active && !$delete,
                'label'     => _a('All resources'),
                'href'      => $this->url('', array_merge(array(
                    'action'    => 'application',
                ), $navParams)),
            ),
            array(
                'active'    => 1 === $active && !$delete,
                'label'     => _a('Active resources'),
                'href'      => $this->url('', array_merge(array(
                    'action'    => 'application',
                    'status'    => 1,
                ), $navParams)),
            ),
            array(
                'active'    => 0 === $active && !$delete,
                'label'     => _a('Inactive resources'),
                'href'      => $this->url('', array_merge(array(
                    'action'    => 'application',
                    'status'    => 0,
                ), $navParams)),
            ),
            array(
                'active'    => $delete,
                'label'     => _a('Deleted resources'),
                'href'      => $this->url('', array_merge(array(
                    'action'    => 'application',
                    'delete'    => 1,
                ), $navParams)),
            ),
        );

        $this->view()->assign(array(
            'title'      => _a('Resource List By module'),
            'apps'       => $apps,
            'medias'     => $resultset,
            'paginator'  => $paginator,
            'tabs'       => $navTabs,
            'users'      => $users,
            'avatars'    => $avatars,
            'active'     => $active,
            'delete'     => $delete,
            'app'        => isset($apps[$app]) ? $apps[$app] : $app,
            'name'       => $module,
            'token'      => $token,
        ));
    }
    
    /**
     * List media by type
     * 
     * @return ViewModel
     */
    public function typeAction()
    {
        $type = $this->params('type', null);
        
        if (empty($type)) {
            $model = $this->getModel('doc');
            // Get count group by mimetype
            $select = $model->select()
                ->columns(array(
                    'mimetype',
                    'count' => new Expression('count(id)')
                ))->group(array('mimetype'));
            $rowset = $model->selectWith($select);
            $result = array();
            foreach ($rowset as $row) {
                list($mType, $ext) = explode('/', $row->mimetype);
                if (isset($result[$mType])) {
                    $result[$mType]['count'] += $row->count;
                } else {
                    $result[$mType]['count'] = $row->count;
                }
                $result[$mType]['type'] = $mType;
                $result[$mType]['url'] = $this->url('', array(
                    'action'    => 'type',
                    'type'      => $mType,
                ));
            }
            
            $this->view()->assign(array(
                'title'     => _a('Media List by Type'),
                'items'     => $result,
            ));
            $this->view()->setTemplate('list-type-select');
            return;
        }
        
        $active = $this->params('status', null);
        if ($active !== null) {
            $active = (int) $active;
        }
        $page   = (int) $this->params('p', 1);
        $limit  = (int) $this->config('page_limit') > 0
            ? $this->config('page_limit') : 20;
        $offset = ($page - 1) * $limit;
        
        // Create mimetype condition
        $where = array();
        $where = array(
            'mimetype like ?'  => $type . '%',
        );
        $params = array(
            'type'  => $type,
        );
        if (1 === $active) {
            $where['active'] = 1;
            $params['status'] = 1;
        } elseif (0 === $active) {
            $where['active'] = 0;
            $params['status'] = 1;
        }
        
        $delete = $this->params('delete', 0);
        if ($delete) {
            $where['time_deleted > ?'] = 0;
        } else {
            $where['time_deleted'] = 0;
        }
        $params['delete'] = $delete;

        // Get media list
        $module = $this->getModule();
        $resultset = Pi::api('doc', $module)->getList(
            $where,
            $limit,
            $offset,
            'time_created'
        );
        
        $uids = $appkeys = array();
        $apps = $users = $avatars = array();
        foreach ($resultset as $row) {
            $appkeys[] = $row['appkey'];
            $uids[]    = $row['uid'];
        }
        // Get application title
        if (!empty($appkeys)) {
            $apps = $this->getAppTitle($appkeys);
        }
        
        // Get users
        if (!empty($uids)) {
            $users = Pi::user()->get($uids);
            $avatars = Pi::avatar()->get($uids);
        }
        
        // Total count
        $totalCount = $this->getModel('doc')->count($where);

        // Paginator
        /*
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit)
            ->setCurrentPageNumber($page)
            ->setUrlOptions(array(
                'page_param' => 'p',
                'router'     => $this->getEvent()->getRouter(),
                'route'      => $this->getEvent()
                    ->getRouteMatch()
                    ->getMatchedRouteName(),
                'params'     => array_filter(array_merge(array(
                    'controller'    => 'list',
                    'action'        => 'type',
                ), $params)),
            ));
        */
        $paginator = Paginator::factory($totalCount, array(
            'page'          => $page,
            'url_options'   => array(
                'page_param'    => 'p',
                'params'        => array_filter(array_merge(array(
                    'controller'    => 'list',
                    'action'        => 'type',
                ), $params)),
            ),
        ));

        $navTabs = array(
            array(
                'active'    => null === $active && !$delete,
                'label'     => _a('All resources'),
                'href'      => $this->url('', array(
                    'action'    => 'type',
                    'type'      => $type,
                )),
            ),
            array(
                'active'    => 1 === $active && !$delete,
                'label'     => _a('Active resources'),
                'href'      => $this->url('', array(
                    'action'    => 'type',
                    'type'      => $type,
                    'status'    => 1,
                )),
            ),
            array(
                'active'    => 0 === $active && !$delete,
                'label'     => _a('Inactive resources'),
                'href'      => $this->url('', array(
                    'action'    => 'type',
                    'type'      => $type,
                    'status'    => 0,
                )),
            ),
            array(
                'active'    => $delete,
                'label'     => _a('Deleted resources'),
                'href'      => $this->url('', array(
                    'action'    => 'type',
                    'type'      => $type,
                    'delete'    => 1,
                )),
            ),
        );
        
        if ('image' == $type) {
            $this->view()->setTemplate('list-type-image');
        }

        $this->view()->assign(array(
            'title'      => _a('Resource List By Type'),
            'apps'       => $apps,
            'medias'     => $resultset,
            'paginator'  => $paginator,
            'tabs'       => $navTabs,
            'users'      => $users,
            'avatars'    => $avatars,
            'active'     => $active,
            'delete'     => $delete,
        ));
    }
    
    /**
     * List media by user
     * 
     * @return ViewModel
     */
    public function userAction()
    {
        // Get user ID
        $user = $this->params('user', null);
        if (is_numeric($user)) {
            $userModel = Pi::service('user')->getUser($user);
        } elseif ($user) {
            $userModel = Pi::service('user')->getUser($user, 'identity');
        } else {
            $userModel = '';
        }
        $uid = $userModel ? $userModel->get('id') : 0;
        
        $active = $this->params('status', null);
        if ($active !== null) {
            $active = (int) $active;
        }
        $page   = (int) $this->params('p', 1);
        $limit  = (int) $this->config('page_limit') > 0
            ? $this->config('page_limit') : 20;
        $offset = ($page - 1) * $limit;
        
        $where = array(
            'uid'  => $uid,
        );
        $params = array(
            'user'  => $user,
        );
        if (1 === $active) {
            $where['active'] = 1;
            $params['status'] = 1;
        } elseif (0 === $active) {
            $where['active'] = 0;
            $params['status'] = 1;
        }
        
        $delete = $this->params('delete', 0);
        if ($delete) {
            $where['time_deleted > ?'] = 0;
        } else {
            $where['time_deleted'] = 0;
        }
        $params['delete'] = $delete;

        // Get media list
        $module = $this->getModule();
        $resultset = Pi::api('doc', $module)->getList(
            $where,
            $limit,
            $offset,
            'time_created'
        );
        
        $apps = $appkeys = array();
        foreach ($resultset as $row) {
            $appkeys[] = $row['appkey'];
        }
        // Get application title
        if (!empty($appkeys)) {
            $apps = $this->getAppTitle($appkeys);
        }
        
        // Get users
        $users = Pi::user()->get($uid);
        $avatars = Pi::avatar()->get($uid);
        
        // Total count
        $totalCount = $this->getModel('doc')->count($where);

        // Paginator
        /*
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit)
            ->setCurrentPageNumber($page)
            ->setUrlOptions(array(
                'page_param' => 'p',
                'router'     => $this->getEvent()->getRouter(),
                'route'      => $this->getEvent()
                    ->getRouteMatch()
                    ->getMatchedRouteName(),
                'params'     => array_filter(array_merge(array(
                    'controller'    => 'list',
                    'action'        => 'user',
                ), $params)),
            ));
        */
        $paginator = Paginator::factory($totalCount, array(
            'page'          => $page,
            'url_options'   => array(
                'page_param'    => 'p',
                'params'        => array_filter(array_merge(array(
                    'controller'    => 'list',
                    'action'        => 'user',
                ), $params)),
            ),
        ));

        $navTabs = array(
            array(
                'active'    => null === $active && !$delete,
                'label'     => _a('All resources'),
                'href'      => $this->url('', array(
                    'action'    => 'user',
                    'user'      => $user,
                )),
            ),
            array(
                'active'    => 1 === $active && !$delete,
                'label'     => _a('Active resources'),
                'href'      => $this->url('', array(
                    'action'    => 'user',
                    'user'      => $user,
                    'status'    => 1,
                )),
            ),
            array(
                'active'    => 0 === $active && !$delete,
                'label'     => _a('Inactive resources'),
                'href'      => $this->url('', array(
                    'action'    => 'user',
                    'user'      => $user,
                    'status'    => 0,
                )),
            ),
            array(
                'active'    => $delete,
                'label'     => _a('Deleted resources'),
                'href'      => $this->url('', array(
                    'action'    => 'user',
                    'user'      => $user,
                    'delete'    => 1,
                )),
            ),
        );
        $url = $this->url('', array('action' => 'user'));
        
        $this->view()->assign(array(
            'title'      => _a('Resource List By User'),
            'apps'       => $apps,
            'medias'     => $resultset,
            'paginator'  => $paginator,
            'tabs'       => $navTabs,
            'users'      => $users,
            'avatars'    => $avatars,
            'active'     => $active,
            'delete'     => $delete,
            'user'       => $user,
            'url'        => $url,
        ));
    }
}
