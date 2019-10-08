<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Front;

use Module\Article\Entity;
use Module\Article\Model\Article;
use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Zend\Db\Sql\Expression;

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

        $category   = $this->params('category', '');
        $categoryId = is_numeric($category)
            ? (int)$category : $modelCategory->slugToId($category);
        $page       = $this->params('p', 1);
        $page       = $page > 0 ? $page : 1;

        $module = $this->getModule();
        $config = Pi::config('', $module);
        $limit  = (int)$config['page_limit_all'] ?: 40;
        $where  = [];

        $route = 'article';

        // Get category nav
        $rowset        = Pi::model('category', $module)->enumerate(null, null);
        $rowset        = array_shift($rowset);
        $navs          = $this->canonizeCategory($rowset['child'], $route);
        $allNav['all'] = [
            'label'      => __('All'),
            'route'      => $route,
            'controller' => 'list',
            'params'     => [
                'category' => 'all',
            ],
        ];
        $navs          = $allNav + $navs;

        // Get all categories
        $categories = [
            'all' => [
                'id'    => 0,
                'title' => __('All articles'),
                'image' => '',
                'url'   => Pi::service('url')->assemble(
                    'article',
                    [
                        'module'     => $module,
                        'controller' => 'list',
                        'action'     => 'all',
                        'list'       => 'all',
                    ]
                ),
            ],
        ];
        $rowset     = Pi::model('category', $module)->enumerate(null, null, true);
        foreach ($rowset as $row) {
            if ('root' == $row['name']) {
                continue;
            }
            $url                    = Pi::service('url')->assemble('', [
                'module'     => $module,
                'controller' => 'category',
                'action'     => 'list',
                'category'   => $row['id'],
            ]);
            $categories[$row['id']] = [
                'id'    => $row['id'],
                'title' => $row['title'],
                'image' => $row['image'],
                'url'   => $url,
            ];
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
            ->where([
                'category'         => $categoryIds,
                'active'           => 1,
                'time_publish < ?' => time(),
            ])
            ->columns(['category', 'count' => new Expression('count(*)')])
            ->group(['category']);
        $resultCount  = $modelArticle->selectWith($select);
        $counts       = [];
        foreach ($resultCount as $row) {
            $counts[$row['category']] = $row['count'];
        }

        // Get articles
        $columns          = [
            'id', 'subject', 'time_publish', 'category', 'summary', 'author',
            'image',
        ];
        $resultsetArticle = Entity::getAvailableArticlePage(
            $where,
            $page,
            $limit,
            $columns,
            null,
            $module
        );

        $articleCategoryIds = $authorIds = [];
        foreach ($resultsetArticle as $row) {
            $authorIds[]          = $row['author'];
            $articleCategoryIds[] = $row['category'];
        }

        // Get author
        $authors = [];
        if (!empty($authorIds)) {
            $rowAuthor = $this->getModel('author')
                ->select(['id' => $authorIds]);
            foreach ($rowAuthor as $row) {
                $authors[$row->id] = $row->toArray();
            }
        }

        // Total count
        $where        = array_merge($where, [
            'time_publish <= ?' => time(),
            'status'            => Article::FIELD_STATUS_PUBLISHED,
            'active'            => 1,
        ]);
        $modelArticle = $this->getModel('article');
        $totalCount   = $modelArticle->getSearchRowsCount($where);

        // Pagination
        $paginator = Paginator::factory($totalCount, [
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => [
                'page_param' => 'p',
                'params'     => [
                    'module'   => $module,
                    'category' => $category,
                ],
            ],
        ]);

        $module = $this->getModule();
        $config = Pi::config('', $module);

        $this->view()->assign([
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
            'elements'      => $config['list_item'],
            'authors'       => $authors,
            'length'        => $config['list_summary_length'],
            'navs'          => $this->config('enable_list_nav') ? $navs : '',
            //'seo'           => $this->setupSeo($categoryId),
        ]);

        $this->view()->viewModel()->getRoot()->setVariables([
            'breadCrumbs' => true,
            'Tag'         => $categoryInfo['title'],
        ]);
    }

    /**
     * Canonize category structure
     *
     * @params array  $categories
     * @params string $route
     */
    protected function canonizeCategory(&$categories, $route)
    {
        foreach ($categories as &$row) {
            $row['label']      = $row['title'];
            $row['controller'] = 'category';
            $row['action']     = 'list';
            $row['params']     = [
                'category' => $row['slug'] ?: $row['id'],
            ];
            $row['route']      = $route;
            if (isset($row['child'])) {
                $row['pages'] = $row['child'];
                unset($row['child']);
                $this->canonizeCategory($row['pages'], $route);
            }
        }

        return $categories;
    }
}
