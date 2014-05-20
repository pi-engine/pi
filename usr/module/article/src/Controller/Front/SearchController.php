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
use Pi;
use Pi\Paginator\Paginator;
use Module\Article\Model\Article;
use Module\Article\Entity;
use Module\Article\Form\SimpleSearchForm;

/**
 * Search controller
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class SearchController extends ActionController
{
    /**
     * Searching articles by title. 
     */
    public function simpleAction()
    {
        $order  = 'time_publish DESC';
        $page   = $this->params('p', 1);
        $module = $this->getModule();

        $config = Pi::config('', $module);
        $limit  = intval($config['page_limit_all']) ?: 40;
        $offset = $limit * ($page - 1);

        // Build where
        $where   = array();
        $keyword = $this->params('keyword', '');
        if ($keyword) {
            $where['subject like ?'] = sprintf('%%%s%%', $keyword);
        }
        
        // Retrieve data
        $articleResultset = Entity::getAvailableArticlePage(
            $where, 
            $page, 
            $limit, 
            null, 
            $order, 
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

        // Paginator
        $paginator = Paginator::factory($totalCount, array(
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => array(
                'page_param'    => 'p',
                'params'        => array(
                    'module'        => $module,
                    'controller'    => 'search',
                    'action'        => 'simple',
                    'keyword'       => $keyword,
                ),
            ),
        ));

        // Prepare search form
        $form = new SimpleSearchForm;
        $form->setData($this->params()->fromQuery());

        $this->view()->assign(array(
            'title'        => __('Search result of '),
            'articles'     => $articleResultset,
            'keyword'      => $keyword,
            'p'            => $page,
            'paginator'    => $paginator,
            'count'        => $totalCount,
            'form'         => $form,
        ));
    }
}
