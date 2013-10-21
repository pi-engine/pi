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
use Module\Article\Service;
use Module\Article\Entity;

/**
 * Tag controller
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class TagController extends ActionController
{
    /**
     * Process article list related with tag
     * 
     * @return ViewModel 
     */
    public function listAction()
    {
        $tag    = Service::getParam($this, 'tag', '');
        $page   = Service::getParam($this, 'p', 1);
        $page   = $page > 0 ? $page : 1;
        $where  = $articleIds = $articles = array();

        if (empty($tag)) {
            return $this->jumpTo404(__('Cannot find this page'));
        }

        $module = $this->getModule();
        $config = Pi::service('module')->config('', $module);
        $limit  = $config['page_limit_all'] ?: 40;
        $offset = ($page - 1) * $limit;

        // Total count
        $totalCount = (int) Pi::service('tag')->getCount($module, $tag);

        // Get article ids
        $articleIds = Pi::service('tag')->getList(
            $module, 
            $tag, 
            null, 
            $limit, 
            $offset
        );

        if ($articleIds) {
            $where['id']    = $articleIds;
            $articles       = array_flip($articleIds);
            $columns        = array('id', 'subject', 'time_publish', 'category');

            $resultsetArticle   = Entity::getAvailableArticlePage(
                $where, 
                1, 
                $limit, 
                $columns, 
                '', 
                $module
            );

            foreach ($resultsetArticle as $key => $val) {
                $articles[$key] = $val;
            }

            $articles = array_filter($articles, function($var) {
                return is_array($var);
            });
        }

        $route = Service::getRouteName();
        // Pagination
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit)
            ->setCurrentPageNumber($page)
            ->setUrlOptions(array(
                'router'    => $this->getEvent()->getRouter(),
                'route'     => $route,
                'params'    => array(
                    'tag'           => $tag,
                ),
            ));

        $this->view()->assign(array(
            'title'     => __('Articles on Tag '),
            'articles'  => $articles,
            'paginator' => $paginator,
            'p'         => $page,
            'tag'       => $tag,
            'config'    => $config,
            'count'     => $totalCount,
        ));

        $this->view()->viewModel()->getRoot()->setVariables(array(
            'breadCrumbs' => true,
            'Tag'         => $tag,
        ));
    }
}
