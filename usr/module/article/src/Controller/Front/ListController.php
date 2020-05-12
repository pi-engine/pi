<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Front;

use Module\Article\Model\Article;
use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Laminas\Db\Sql\Expression;

/**
 * List controller
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ListController extends ActionController
{
    /**
     * Listing all articles for users to review
     */
    public function allAction()
    {
        $page = $this->params('p', 1);
        $sort = $this->params('sort', 'new');

        $params = ['sort' => $sort];
        $where  = [
            'status'           => Article::FIELD_STATUS_PUBLISHED,
            'active'           => 1,
            'time_publish < ?' => time(),
        ];

        $category           = $this->params('category', 0);
        $params['category'] = $category;
        if (!empty($category) && 'all' != $category) {
            $modelCategory = $this->getModel('category');
            if (!is_numeric($category)) {
                $category = $modelCategory->slugToId($category);
            }
            $children = $modelCategory->getDescendantIds($category);
            if (empty($children)) {
                return $this->jumpTo404(__('Invalid category id'));
            }
            $where['category'] = $children;
        }

        //@todo Get limit from module config
        $limit  = (int)$this->config('page_limit_all');
        $limit  = $limit ?: 40;
        $offset = $limit * ($page - 1);

        $model  = $this->getModel('article');
        $select = $model->select()->where($where);
        if ('hot' == $sort) {
            $modelStats = $this->getModel('stats');
            $select->join(
                ['st' => $modelStats->getTable()],
                sprintf('%s.id = st.article', $model->getTable()),
                []
            );
            $order = 'st.visits DESC';
        } else {
            $order = 'time_update DESC, time_publish DESC';
        }
        $select->order($order)->offset($offset)->limit($limit);

        $module      = $this->getModule();
        $route       = 'article';
        $resultset   = $model->selectWith($select);
        $items       = [];
        $categoryIds = $authorIds = [];
        foreach ($resultset as $row) {
            $items[$row->id]        = $row->toArray();
            $publishTime            = date('Ymd', $row->time_publish);
            $items[$row->id]['url'] = $this->url(
                $route,
                [
                    'module' => $module,
                    'id'     => $row->id,
                    'time'   => $publishTime,
                ]
            );
            $authorIds[]            = $row->author;
            $categoryIds[]          = $row->category;
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
        $select                = $model->select()
            ->where($where)
            ->columns(['total' => new Expression('count(id)')]);
        $articleCountResultset = $model->selectWith($select);
        $totalCount            = intval($articleCountResultset->current()->total);

        // Paginator
        $paginator = Paginator::factory($totalCount, [
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => [
                'page_param' => 'p',
                'params'     => array_merge(
                    [
                        'module'     => $this->getModule(),
                        'controller' => 'list',
                        'action'     => 'all',
                    ],
                    $params
                ),
            ],
        ]);

        $config = Pi::config('', $module);

        // Get category nav
        $rowset        = $this->getModel('category')->enumerate(null, null);
        $rowset        = array_shift($rowset);
        $navs          = $this->canonizeCategory($rowset['child'], $route);
        $categoryTitle = $this->getCategoryTitle($category, $rowset['child']);
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
                        'module' => $module,
                        'list'   => 'all',
                    ]
                ),
            ],
        ];
        $rowset     = $this->getModel('category')->enumerate(null, null, true);
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

        $urlHot = $this->url($route, ['category' => $category, 'sort' => 'hot']);
        $urlNew = $this->url($route, ['category' => $category]);

        $title = $categoryTitle ?: __('All Articles');
        $this->view()->assign([
            'title'      => $title,
            'articles'   => $items,
            'paginator'  => $paginator,
            'elements'   => $config['list_item'],
            'authors'    => $authors,
            'categories' => $categories,
            'length'     => $config['list_summary_length'],
            'navs'       => $this->config('enable_list_nav') ? $navs : '',
            'category'   => $category,
            'url'        => [
                'hot' => $urlHot,
                'new' => $urlNew,
            ],
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
            $row['label']  = $row['title'];
            $row['params'] = [
                'category' => $row['slug'] ?: $row['id'],
            ];
            $row['route']  = $route;
            if (isset($row['child'])) {
                $row['pages'] = $row['child'];
                unset($row['child']);
                $this->canonizeCategory($row['pages'], $route);
            }
        }

        return $categories;
    }

    /**
     * Get category title
     *
     * @param int $id Category ID
     * @param array $items Category rowset
     * @return string
     */
    protected function getCategoryTitle($id, $items)
    {
        if (empty($id)) {
            return '';
        }
        foreach ($items as $item) {
            if ($id == $item['id']) {
                return $item['title'];
            }
            if (isset($item['pages'])) {
                $title = $this->getCategoryTitle($id, $item['pages']);
                if ($title) {
                    return $title;
                }
            }
        }
    }
}
