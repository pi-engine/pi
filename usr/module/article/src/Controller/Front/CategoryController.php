<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Zend\Db\Sql\Expression;
use Module\Article\Model\Article;
use Module\Article\Entity;

/**
 * Category controller
 * 
 * Feature list:
 * 
 * 1. List article of a category
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class CategoryController extends ActionController
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
     * Category homepage
     */
    public function indexAction()
    {
        $module = $this->getModule();
        $seo = Pi::api('page', $module)->getSeoMeta($this->params('action'));
        $this->view()->assign('seo', $seo);
        
        $this->view()->setTemplate('category-index');
    }
    
    /**
     * List articles of a category
     */
    public function listAction()
    {
        $module = $this->getModule();
        $config = Pi::config('', $module);

        $category   = $this->params('category', '');
        $categoryId = Pi::api('category', $module)->slugToId($category);
        $page       = (int) $this->params('p', 1);
        $page       = $page > 0 ? $page : 1;
        $limit      = (int) $config['page_limit_all'] ?: 40;
        
        // Jump to 404 if category is not activated
        $detail = Pi::api('category', $module)->get($category);
        if (empty($detail) || !$detail['active']) {
            $this->jumpTo404(__('Page not found.'));
        }

        // Get category navigation
        $options = array(
            'controller' => 'category',
            'action'     => 'list',
        );
        $navs   = Pi::api('category', $module)->navigation($options);
        
        // Get children categories details
        $children = Pi::api('category', $module)->getDescendantIds(
            $categoryId,
            false
        );
        
        $where = array(
            'time_publish <= ?' => time(),
            'status'            => Article::FIELD_STATUS_PUBLISHED,
            'active'            => 1,
            'category'          => array_merge($children, array($categoryId)),
        );

        $counts       = array();
        $modelArticle = $this->getModel('article');
        $select       = $modelArticle->select()
            ->where($where)
            ->columns(array('category', 'count' => new Expression('count(*)')))
            ->group(array('category'));
        $resultCount  = $modelArticle->selectWith($select);
        foreach ($resultCount as $row) {
            $counts[$row['category']] = $row['count'];
        }

        // Get articles
        $columns = array(
            'id', 'subject', 'time_publish', 'category', 'summary', 'author',
            'image', 'uid'
        );
        $resultsetArticle  = Entity::getAvailableArticlePage(
            $where,
            $page,
            $limit,
            $columns,
            null,
            $module
        );
        
        // Pagination
        $totalCount = $modelArticle->count($where);
        $paginator  = Paginator::factory($totalCount, array(
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => array(
                'page_param'    => 'p',
                'params'        => array(
                    'module'        => $module,
                    'category'      => $category,
                ),
            ),
        ));
        
        $seo = Pi::api('page', $module)->getSeoMeta($this->params('action'));
        
        // Get categories URL
        $categories = Pi::api('category', $module)->getList();
        foreach ($categories as &$row) {
            $row['url'] = Pi::api('api', $module)->getUrl(
                'list',
                array('category' => $row['slug'] ?: $row['id']),
                array('category' => $row)
            );
        }

        $this->view()->assign(array(
            'title'         => __('Article List in Category'),
            'articles'      => $resultsetArticle,
            'paginator'     => $paginator,
            'categories'    => $categories,
            'category'      => $category,
            'config'        => $config,
            'counts'        => $counts,
            'categoryId'    => $categoryId,
            'children'      => $children,
            'route'         => Pi::api('api', $module)->getRouteName(),
            'navs'          => $navs,
            'seo'           => $seo,
        ));
        
        $this->view()->setTemplate('category-list');
    }
}
