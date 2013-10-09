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
use Module\Article\Model\Article;
use Module\Article\Model\Draft;
use Module\Article\Form\SimpleSearchForm;
use Zend\Db\Sql\Expression;
use Module\Article\Service;
use Module\Article\Entity;

/**
 * Article controller
 * 
 * Feature list:
 * 
 * 1. Article homepage
 * 2. Article detail page
 * 5. AJAX action for seaching article
 * 6. AJAX action for checking article subject exists
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ArticleController extends ActionController
{
    /**
     * Article homepage, all page content are dressed up by user 
     */
    public function indexAction()
    {

    }
    
    /**
     * Article detail page
     * 
     * @return ViewModel 
     */
    public function detailAction()
    {
        $id       = $this->params('id');
        $slug     = $this->params('slug', '');
        $page     = $this->params('p', 1);
        $remain   = $this->params('r', '');
        
        if ('' !== $remain) {
            $this->view()->assign('remain', $remain);
        }

        if (empty($id)) {
            $id = $this->getModel('extended')->slugToId($slug);
        }

        $details = Entity::getEntity($id);
        $params  = array();
        
        if (!$id or ($details['time_publish'] > time())) {
            return $this->jumpTo404(__('Page not found'));
        }
        if (empty($details['active'])) {
            return $this->jumpToException(
                __('The article requested is not active'),
                503
            );
        }
        $route = Service::getRouteName();
        if (strval($slug) != $details['slug']) {
            $routeParams = array(
                'time'       => date('Ymd', $details['time_publish']),
                'id'         => $id,
                'slug'       => $details['slug'],
                'p'          => $page,
                'controller' => 'article',
                'action'     => 'detail',
            );
            if ($remain) {
                $params['r'] = $remain;
            }
            return $this->redirect()
                ->setStatusCode(301)
                ->toRoute($route, array_merge($routeParams, $params));
        }
        
        foreach ($details['content'] as &$value) {
            $value['url'] = $this->url($route, array_merge(array(
                'time'       => date('Ymd', $details['time_publish']),
                'id'         => $id,
                'slug'       => $slug,
                'p'          => $value['page'],
                'controller' => 'article',
                'action'     => 'detail',
            ), $params));
            if (isset($value['title']) 
                and preg_replace('/&nbsp;/', '', trim($value['title'])) !== ''
            ) {
                $showTitle = true;
            } else {
                $value['title'] = '';
            }
        }
        $details['view'] = $this->url($route, array_merge(array(
            'time'        => date('Ymd', $details['time_publish']),
            'id'          => $id,
            'slug'        => $slug,
            'r'           => 0,
            'controller'  => 'article',
            'action'      => 'detail',
        ), $params));
        $details['remain'] = $this->url($route, array_merge(array(
            'time'        => date('Ymd', $details['time_publish']),
            'id'          => $id,
            'slug'        => $slug,
            'r'           => $page,
            'controller'  => 'article',
            'action'      => 'detail',
        ), $params));

        $config = Pi::service('module')->config('', $this->getModule());
        $this->view()->assign(array(
            'details'     => $details,
            'page'        => $page,
            'showTitle'   => isset($showTitle) ? $showTitle : null,
            'config'      => $config,
        ));
    }

    

    /**
     * Active or deactivate articles
     * 
     * @return ViewModel
     */
    public function activateAction()
    {
        $id     = Service::getParam($this, 'id', '');
        $ids    = array_filter(explode(',', $id));
        $status = Service::getParam($this, 'status', 0);
        $from   = Service::getParam($this, 'from', '');

        if ($ids) {
            $module         = $this->getModule();
            $modelArticle   = $this->getModel('article');
            
            // Activing articles that user has permission to do
            $rules = Service::getPermission();
            if (1 == count($ids)) {
                $row      = $modelArticle->find($ids[0]);
                if (!(isset($rules[$row->category]['active']) 
                    and $rules[$row->category]['active'])
                ) {
                    return $this->jumpToDenied();
                }
            } else {
                $rows     = $modelArticle->select(array('id' => $ids));
                $ids      = array();
                foreach ($rows as $row) {
                    if (isset($rules[$row->category]['active']) 
                        and $rules[$row->category]['active']
                    ) {
                        $ids[] = $row->id;
                    }
                }
            }
            
            $modelArticle->setActiveStatus($ids, $status ? 1 : 0);

            // Clear cache
            Pi::service('render')->flushCache($module);
        }

        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            // Go to list page
            return $this->redirect()->toRoute(
                '', 
                array('action' => 'published', 'from' => 'all')
            );
        }
    }

    /**
     * Edit a published article, the article details will be copied to 
     * draft table, and then redirect to edit page.
     * 
     * @return ViewModel 
     */
    public function editAction()
    {
        $id     = Service::getParam($this, 'id', 0);
        $module = $this->getModule();

        if (!$id) {
            return $this->jumpTo404(__('Invalid article ID'));
        }
        
        $model = $this->getModel('article');
        $row   = $model->find($id);

        // Check user has permission to edit
        $rules = Service::getPermission();
        $slug  = Service::getStatusSlug($row->status);
        $resource = $slug . '-edit';
        if (!(isset($rules[$row->category][$resource]) 
            and $rules[$row->category][$resource])
        ) {
            return $this->jumpToDenied();
        }
        
        // Check if draft exists
        $draftModel = $this->getModel('draft');
        $rowDraft   = $draftModel->find($id, 'article');

        if ($rowDraft) {
            $draftModel->delete(array('id' => $rowDraft->id));
        }

        // Create new draft if no draft exists
        if (!$row->id or $row->status != Article::FIELD_STATUS_PUBLISHED) {
            return $this->jumpTo404(__('Can not create draft'));
        }
        $draft = array(
            'article'         => $row->id,
            'subject'         => $row->subject,
            'subtitle'        => $row->subtitle,
            'summary'         => $row->summary,
            'content'         => $row->content,
            'uid'             => $row->uid,
            'author'          => $row->author,
            'source'          => $row->source,
            'pages'           => $row->pages,
            'category'        => $row->category,
            'status'          => Draft::FIELD_STATUS_DRAFT,
            'time_save'       => time(),
            'time_submit'     => $row->time_submit,
            'time_publish'    => $row->time_publish,
            'time_update'     => $row->time_update,
            'image'           => $row->image,
            'user_update'     => $row->user_update,
        );
        
        // Get extended fields
        $modelExtended = $this->getModel('extended');
        $rowExtended   = $modelExtended->find($row->id, 'article');
        $extendColumns = $modelExtended->getValidColumns();
        foreach ($extendColumns as $col) {
            $draft[$col] = $rowExtended->$col;
        }

        // Get related articles
        $relatedModel = $this->getModel('related');
        $related      = $relatedModel->getRelated($id);
        $draft['related'] = $related;

        // Get tag
        if ($this->config('enable_tag')) {
            $draft['tag'] = Pi::service('tag')->get($module, $id);
        }

        // Save as draft
        $draftRow = $draftModel->saveRow($draft);

        $draftId = $draftRow->id;

        // Copy assets to draft
        $resultsetAsset = $this->getModel('asset')->select(array(
            'article' => $id,
        ));
        $modelDraftAsset = $this->getModel('asset_draft');
        foreach ($resultsetAsset as $asset) {
            $data = array(
                'media'    => $asset->media,
                'type'     => $asset->type,
                'draft'    => $draftId,
            );
            $rowDraftAsset = $modelDraftAsset->createRow($data);
            $rowDraftAsset->save();
        }

        // Redirect to edit draft
        if ($draftId) {
            return $this->redirect()->toRoute('', array(
                'action'     => 'edit',
                'controller' => 'draft',
                'id'         => $draftId,
                'from'       => 'all',
            ));
        }
    }

    /**
     * List all published article for management
     * 
     * @return ViewModel 
     */
    public function publishedAction()
    {
        $where  = array();
        $page   = Service::getParam($this, 'p', 1);
        $limit  = Service::getParam($this, 'limit', 20);
        $from   = Service::getParam($this, 'from', 'my');
        $order  = 'time_publish DESC';

        // Get permission
        $rules = Service::getPermission();
        if (empty($rules)) {
            return $this->jumpToDenied();
        }
        $categories = array();
        foreach (array_keys($rules) as $key) {
            $categories[$key] = true;
        }
        $where['category'] = array_keys($categories);
        
        // Select article of mine
        if ('my' == $from) {
            $where['uid'] = Pi::user()->id ?: 0;
        }

        $module         = $this->getModule();
        $modelArticle   = $this->getModel('article');
        $categoryModel  = $this->getModel('category');

        $category = Service::getParam($this, 'category', 0);
        if (!empty($category) and !in_array($category, $where['category'])) {
            return $this->jumpToDenied();
        }
        if ($category > 1) {
            $categoryIds = $categoryModel->getDescendantIds($category);
            if ($categoryIds) {
                $where['category'] = $categoryIds;
            }
        }

        // Build where
        $where['status'] = Article::FIELD_STATUS_PUBLISHED;
        
        $keyword = Service::getParam($this, 'keyword', '');
        if (!empty($keyword)) {
            $where['subject like ?'] = sprintf('%%%s%%', $keyword);
        }
        $where = array_filter($where);
        
        // The where must be added after array_filter function
        $filter = Service::getParam($this, 'filter', '');
        if ($filter == 'active') {
            $where['active'] = 1;
        } else if ($filter == 'deactive') {
            $where['active'] = 0;
        }

        // Retrieve data
        $data = Entity::getArticlePage($where, $page, $limit, null, $order);

        // Total count
        $select = $modelArticle->select()
            ->columns(array('total' => new Expression('count(id)')))
            ->where($where);
        $resulsetCount = $modelArticle->selectWith($select);
        $totalCount    = (int) $resulsetCount->current()->total;

        // Paginator
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit)
            ->setCurrentPageNumber($page)
            ->setUrlOptions(array(
            'router'    => $this->getEvent()->getRouter(),
            'route'     => $this->getEvent()
                ->getRouteMatch()
                ->getMatchedRouteName(),
            'params'    => array_filter(array(
                'module'        => $module,
                'controller'    => 'article',
                'action'        => 'published',
                'category'      => $category,
                'filter'        => $filter,
                'keyword'       => $keyword,
            )),
        ));

        // Prepare search form
        $form = new SimpleSearchForm;
        $form->setData($this->params()->fromQuery());
        
        $flags = array(
            'draft'     => Draft::FIELD_STATUS_DRAFT,
            'pending'   => Draft::FIELD_STATUS_PENDING,
            'rejected'  => Draft::FIELD_STATUS_REJECTED,
            'published' => Article::FIELD_STATUS_PUBLISHED,
        );

        $cacheCategories = Service::getCategoryList();
        $this->view()->assign(array(
            'title'      => __('Published'),
            'data'       => $data,
            'form'       => $form,
            'paginator'  => $paginator,
            'summary'    => Service::getSummary($from, $rules),
            'category'   => $category,
            'filter'     => $filter,
            'categories' => array_intersect_key($cacheCategories, $categories),
            'action'     => 'published',
            'flags'      => $flags,
            'status'     => Article::FIELD_STATUS_PUBLISHED,
            'from'       => $from,
            'rules'      => $rules,
        ));
        
        if ('my' == $from) {
            return $this->view()->setTemplate('draft-list');
        }
    }
    
    /**
     * Get article by title via AJAX.
     * 
     * @return ViewModel 
     */
    public function getFuzzyArticleAction()
    {
        Pi::service('log')->active(false);
        $articles   = array();
        $pageCount  = $total = 0;
        $module     = $this->getModule();
        $where      = array('status' => Article::FIELD_STATUS_PUBLISHED);

        $keyword = Service::getParam($this, 'keyword', '');
        $type    = Service::getParam($this, 'type', 'title');
        $limit   = Service::getParam($this, 'limit', 10);
        $limit   = $limit > 100 ? 100 : $limit;
        $page    = Service::getParam($this, 'page', 1);
        $exclude = Service::getParam($this, 'exclude', 0);
        $offset  = $limit * ($page - 1);

        $articleModel   = $this->getModel('article');

        if (strcasecmp('tag', $type) == 0) {
            if ($keyword) {
                $total     = Pi::service('tag')->getCount($module, $keyword);
                $pageCount = ceil($total / $limit);

                // Get article ids
                $articleIds = Pi::service('tag')->getList(
                    $module, 
                    $keyword,
                    null, 
                    $limit, 
                    $offset
                );
                if ($articleIds) {
                    $where['id'] = $articleIds;
                    $articles    = array_flip($articleIds);

                    // Get articles
                    $resultsetArticle = Entity::getArticlePage(
                        $where, 
                        1, 
                        $limit, 
                        null, 
                        null, 
                        $module
                    );

                    foreach ($resultsetArticle as $key => $val) {
                        $articles[$key] = $val;
                    }

                    $articles = array_filter($articles, function($var) {
                        return is_array($var);
                    });
                }
            }
        } else {
            // Get resultset
            if ($keyword) {
                $where['subject like ?'] = sprintf('%%%s%%', $keyword);
            }

            $articles = Entity::getArticlePage($where, $page, $limit);

            // Get total
            $total      = $articleModel->getSearchRowsCount($where);
            $pageCount  = ceil($total / $limit);
        }

        foreach ($articles as $key => &$article) {
            if ($exclude && $exclude == $key) {
                unset($articles[$key]);
            }
            $article['time_publish_text'] = date(
                'Y-m-d',
                $article['time_publish']
            );
        }

        echo json_encode(array(
            'status'    => true,
            'message'   => __('OK'),
            'data'      => array_values($articles),
            'paginator' => array(
                'currentPage' => $page,
                'pageCount'   => $pageCount,
                'keyword'     => $keyword,
                'type'        => $type,
                'limit'       => $limit,
                'totalCount'  => $total,
            ),
        ));
        exit ;
    }
    
    /**
     * Check whether article is exists by subject
     * 
     * @return array
     */
    public function checkArticleExistsAction()
    {
        Pi::service('log')->active(false);
        $subject = trim(Service::getParam($this, 'subject', ''));
        $id      = Service::getParam($this, 'id', null);
        $result  = false;

        if ($subject) {
            $articleModel = $this->getModel('article');
            $result = $articleModel->checkSubjectExists($subject, $id);
        }

        return array(
            'status'  => $result ? false : true,
            'message' => $result ? __('Subject is used by another article.') 
                : __('ok'),
        );
    }
}
