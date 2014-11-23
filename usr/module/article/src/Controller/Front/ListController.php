<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Front;

use Pi\Mvc\Controller\ActionController;
use Module\Article\Model\Article;
use Module\Article\Entity;
use Pi\Paginator\Paginator;
use Pi;

/**
 * List controller
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ListController extends ActionController
{
    /**
     * Parse action name
     * 
     * @param string  $action
     * @return string
     */
    public static function getMethodFromAction($action)
    {
        $module = Pi::service('module')->current();
        $page   = Pi::api('page', $module)->get($action);
        $name   = isset($page['action']) ? $page['action'] : '';

        return parent::getMethodFromAction($name ?: $action);
    }
    
    /**
     * List articles for users to review 
     */
    public function indexAction()
    {
        $module = $this->getModule();
        $config = $this->config('');
        
        $page   = (int) $this->params('p', 1);
        $limit  = (int) $this->params('limit', $this->config('page_limit_all'));
        $offset = $limit * ($page - 1);
        $sort   = $this->params('sort', 'new');

        $params = array('sort' => $sort);
        $where  = array(
            'status'           => Article::FIELD_STATUS_PUBLISHED,
            'active'           => 1,
            'time_publish < ?' => time(),
        );
        
        // Get category condition
        $category = $this->params('category', 0);
        $params['category'] = $category;
        if (!empty($category) && 'all' != $category) {
            $category = Pi::api('category', $module)->slugToId($category);
            $children = Pi::api('category', $module)->getDescendantIds($category);
            if (empty($children)) {
                return $this->jumpTo404(__('Invalid category id'));
            }
            $where['category'] = $children;
        }
        
        $categoryDetail = $clusterDetail = array();
        // Jump to 404 if category is not activated
        if ('all' != $category) {
            $categoryDetail = Pi::api('category', $module)->get($category);
            if (empty($categoryDetail) || !$categoryDetail['active']) {
                $this->jumpTo404(__('Page not found.'));
            }
        }
        
        // Get cluster condition
        $cluster = $this->params('cluster', 0);
        $params['cluster'] = $cluster;
        if (!empty($cluster) && 'all' != $cluster) {
            $cluster  = Pi::api('cluster', $module)->slugToId($cluster);
            $children = Pi::api('cluster', $module)->getDescendantIds($cluster);
            if (empty($children)) {
                return $this->jumpTo404(__('Invalid cluster id'));
            }
            $where['cluster'] = $children;
        }
        
        // Jump to 404 if cluster is not activated
        if ('all' != $cluster) {
            $clusterDetail = Pi::api('cluster', $module)->get($cluster);
            if (empty($clusterDetail) || !$clusterDetail['active']) {
                $this->jumpTo404(__('Page not found.'));
            }
        }
        
        if ('hot' == $sort) {
            $items = Entity::getTopVisitArticles(
                'A',
                $where,
                null,
                $offset,
                $limit,
                $module
            );
        } else {
            $options = array(
                'section'    => 'front',
                'controller' => 'list',
                'action'     => 'index',
            );
            $order = Pi::api('api', $module)->canonizeOrder($options);
            $items = Entity::getAvailableArticlePage(
                $where,
                $page,
                $limit,
                null,
                $order ?: 'time_update DESC, time_publish DESC',
                $module
            );
        }
        
        // Paginator
        $count     = Entity::count($where, true);
        $paginator = Paginator::factory($count, array(
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => array(
                'page_param'    => 'p',
                'params'        => array_merge(
                    array(
                        'module'        => $module,
                        'controller'    => 'list',
                        'action'        => 'index',
                    ),
                    $params
                ),
            ),
        ));
        
        // Get category navigation
        $options = array(
            'controller' => 'list',
            'action'     => 'index',
        );
        $navs   = Pi::api('category', $module)->navigation($options);
        
        $urlOptions = array(
            'category' => $categoryDetail,
            'cluster'  => $clusterDetail,
        );
        $urlHot = Pi::api('api', $module)->getUrl(
            'list',
            array('category' => $category, 'sort' => 'hot'),
            $urlOptions
        );
        $urlNew = Pi::api('api', $module)->getUrl(
            'list',
            array('category' => $category),
            $urlOptions
        );
        
        // Get SEO meta
        $seo = Pi::api('page', $module)->getSeoMeta($this->params('action'));

        $this->view()->assign(array(
            'articles'   => $items,
            'paginator'  => $paginator,
            'config'     => $config,
            'categories' => Pi::api('category', $module)->getList(),
            'navs'       => $navs,
            'category'   => $category,
            'cluster'    => $cluster,
            'url'        => array(
                'hot'       => $urlHot,
                'new'       => $urlNew,
            ),
            'seo'        => $seo,
        ));
        
        $theme = $this->config('theme');
        if ($theme) {
            Pi::service('theme')->setTheme($theme);
        }
        $this->view()->setTemplate('list-index');
    }
}
