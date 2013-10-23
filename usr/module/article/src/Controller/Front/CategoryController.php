<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Controller\Front;

use Pi\Mvc\Controller\ActionController;
use Pi;
use Pi\Paginator\Paginator;
use Module\Article\Form\CategoryEditForm;
use Module\Article\Form\CategoryEditFilter;
use Module\Article\Form\CategoryMergeForm;
use Module\Article\Form\CategoryMergeFilter;
use Module\Article\Form\CategoryMoveForm;
use Module\Article\Form\CategoryMoveFilter;
use Module\Article\Model\Category;
use Zend\Db\Sql\Expression;
use Module\Article\Service;
use Module\Article\Model\Article;
use Module\Article\Entity;
use Pi\File\Transfer\Upload as UploadHandler;

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
     * List articles of a category
     */
    public function listAction()
    {
        $modelCategory = $this->getModel('category');

        $category   = Service::getParam($this, 'category', '');
        $categoryId = is_numeric($category)
            ? (int) $category : $modelCategory->slugToId($category);
        $page       = Service::getParam($this, 'p', 1);
        $page       = $page > 0 ? $page : 1;

        $module = $this->getModule();
        $config = Pi::service('module')->config('', $module);
        $limit  = (int) $config['page_limit_all'] ?: 40;
        $where  = array();
        
        $route  = Service::getRouteName($module);

        // Get category info
        $categories = Service::getCategoryList();
        foreach ($categories as &$row) {
            $row['url'] = $this->url($route, array(
                'category' => $row['slug'] ?: $row['id'],
            ));
        }
        $categoryIds = $modelCategory->getDescendantIds($categoryId);
        if (empty($categoryIds)) {
            return $this->jumpTo404(__('Invalid category id'));
        }
        $where['category'] = $categoryIds;
        $categoryInfo      = $categories[$categoryId];
        
        // Get subcategories article count
        $modelArticle = $this->getModel('article');
        $select       = $modelArticle->select()
            ->where(array('category' => $categoryIds))
            ->columns(array('category', 'count' => new Expression('count(*)')))
            ->group(array('category'));
        $resultCount  = $modelArticle->selectWith($select);
        $counts       = array();
        foreach ($resultCount as $row) {
            $counts[$row['category']] = $row['count'];
        }

        // Get articles
        $columns           = array('id', 'subject', 'time_publish', 'category');
        $resultsetArticle  = Entity::getAvailableArticlePage(
            $where,
            $page,
            $limit,
            $columns,
            null,
            $module
        );

        // Total count
        $where = array_merge($where, array(
            'time_publish <= ?' => time(),
            'status'            => Article::FIELD_STATUS_PUBLISHED,
            'active'            => 1,
        ));
        $modelArticle   = $this->getModel('article');
        $totalCount     = $modelArticle->getSearchRowsCount($where);

        // Pagination
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit)
            ->setCurrentPageNumber($page)
            ->setUrlOptions(array(
                'router'    => $this->getEvent()->getRouter(),
                'route'     => $route,
                'params'    => array(
                    'category'      => $category,
                ),
            ));

        $this->view()->assign(array(
            'title'         => __('Article List in Category'),
            'articles'      => $resultsetArticle,
            'paginator'     => $paginator,
            'categories'    => $categories,
            'categoryInfo'  => $categoryInfo,
            'category'      => $category,
            'p'             => $page,
            'config'        => $config,
            'counts'        => $counts,
            'categoryId'    => array_shift($categoryIds),
            'subCategoryId' => $categoryIds,
            'route'         => $route,
            //'seo'           => $this->setupSeo($categoryId),
        ));

        $this->view()->viewModel()->getRoot()->setVariables(array(
            'breadCrumbs' => true,
            'Tag'         => $categoryInfo['title'],
        ));
    }
}
