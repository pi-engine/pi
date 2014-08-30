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
use Module\Article\Model\Draft as DraftModel;
use Module\Article\Form\SimpleSearchForm;
use Module\Article\Rule;
use Module\Article\Entity;
use Module\Article\Stats;
use Module\Article\Draft;
use Module\Article\Media;

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
     * Section identifier
     * @var string
     */
    protected $section = 'front';
    
    /**
     * Article homepage, all page content are dressed up by user 
     */
    public function indexAction()
    {
        if ($this->config('default_homepage')) {
            return $this->redirect()
                        ->toUrl(Pi::url($this->config('default_homepage')));
        }
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
        $details['id'] = $id;

        if (!$id or ($details['time_publish'] > time())) {
            return $this->jumpTo404(__('Page not found'));
        }
        if (empty($details['active'])) {
            return $this->jumpToException(
                __('The article requested is not active'),
                503
            );
        }
        $module = $this->getModule();
        $params  = array(
            'module'        => $module,
        );
        //$route = Pi::api('api', $module)->getRouteName();
        $route = 'article';
        if (strval($slug) != $details['slug']) {
            $routeParams = array(
                'time'          => date('Ymd', $details['time_publish']),
                'id'            => $id,
                'slug'          => $details['slug'],
                'p'             => $page,
                'controller'    => 'article',
                'action'        => 'detail',
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
                'time'          => date('Ymd', $details['time_publish']),
                'id'            => $id,
                'slug'          => $slug,
                'p'             => $value['page'],
                'controller'    => 'article',
                'action'        => 'detail',
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
        
        $config = Pi::config('', $this->getModule());
        $this->view()->assign(array(
            'details'     => $details,
            'page'        => $page,
            'showTitle'   => isset($showTitle) ? $showTitle : null,
            'config'      => $config,
            'module'      => $module,
        ));
    }

    /**
     * Edit a published article, the article details will be copied to 
     * draft table, and then redirect to edit page.
     * 
     * @return ViewModel 
     */
    public function editAction()
    {
        // Denied user viewing if no front-end management permission assigned
        if (!$this->config('enable_front_edit') && 'front' == $this->section) {
            return $this->jumpTo404();
        }
        
        $id     = $this->params('id', 0);
        $module = $this->getModule();

        if (!$id) {
            return $this->jumpTo404(__('Invalid article ID'));
        }
        
        $model = $this->getModel('article');
        $row   = $model->find($id);

        // Check user has permission to edit
        $rules = Rule::getPermission();
        $slug  = Draft::getStatusSlug($row->status);
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
            'status'          => DraftModel::FIELD_STATUS_DRAFT,
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
        /*
        if ($this->config('enable_tag')) {
            $draft['tag'] = Pi::service('tag')->get($module, $id);
        }
        */

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
                'module'        => $module,
                'action'        => 'edit',
                'controller'    => 'draft',
                'id'            => $draftId,
                'from'          => 'all',
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
        // Denied user viewing if no front-end management permission assigned
        if (!$this->config('enable_front_edit') && 'front' == $this->section) {
            return $this->jumpTo404();
        }

        $page       = $this->params('p', 1);
        $limit      = $this->params('limit', 20);
        $from       = $this->params('from', 'my');
        $keyword    = $this->params('keyword', '');
        $category   = $this->params('category', 0);
        $filter     = $this->params('filter', '');
        $order      = 'time_publish DESC';

        $where      = array();
        // Get permission
        $rules = Rule::getPermission();
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
            $where['uid'] = Pi::user()->getId() ?: 0;
        }

        $module         = $this->getModule();
        $modelArticle   = $this->getModel('article');
        $categoryModel  = $this->getModel('category');

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
        
        if (!empty($keyword)) {
            $where['subject like ?'] = sprintf('%%%s%%', $keyword);
        }
        $where = array_filter($where);
        
        // The where must be added after array_filter function
        if ($filter == 'active') {
            $where['active'] = 1;
        } else if ($filter == 'deactive') {
            $where['active'] = 0;
        }

        // Retrieve data
        $data = Entity::getArticlePage($where, $page, $limit, null, $order);

        // Total count
        $totalCount = $modelArticle->count($where);

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
            'params'     => array_filter(array(
                'module'        => $module,
                'controller'    => 'article',
                'action'        => 'published',
                'category'      => $category,
                'filter'        => $filter,
                'keyword'       => $keyword,
            )),
        ));
        */

        $params = array(
            'module'    => $module,
        );
        foreach (array('category', 'filter', 'keyword', 'from') as $key) {
            if (${$key}) {
                $params[$key] = ${$key};
            }
        }
        $paginator = Paginator::factory($totalCount, array(
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => array(
                'page_param'    => 'p',
                'params'        => $params,
            ),
        ));

        // Prepare search form
        $form = new SimpleSearchForm;
        $form->setData($this->params()->fromQuery());
        
        $flags = array(
            'draft'     => DraftModel::FIELD_STATUS_DRAFT,
            'pending'   => DraftModel::FIELD_STATUS_PENDING,
            'rejected'  => DraftModel::FIELD_STATUS_REJECTED,
            'published' => Article::FIELD_STATUS_PUBLISHED,
        );

        $cacheCategories = Pi::api('api', $module)->getCategoryList();
        $this->view()->assign(array(
            'title'      => __('Published'),
            'data'       => $data,
            'form'       => $form,
            'paginator'  => $paginator,
            'summary'    => Entity::getSummary($from, $rules),
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
            $this->view()->setTemplate('draft-list', $module, 'front');
        } else {
            $this->view()->setTemplate('article-published', $module, 'front');
        }
    }
    
    /**
     * Delete published articles
     * 
     * @return ViewModel 
     */
    public function deleteAction()
    {
        // Denied user viewing if no front-end management permission assigned
        if (!$this->config('enable_front_edit') && 'front' == $this->section) {
            return $this->jumpTo404();
        }
        
        $id     = $this->params('id', '');
        $ids    = array_filter(explode(',', $id));
        $from   = $this->params('from', '');

        if (empty($ids)) {
            return $this->jumpTo404(__('Invalid article ID'));
        }
        
        $module         = $this->getModule();
        $modelArticle   = $this->getModel('article');
        $modelAsset     = $this->getModel('asset');
        
        // Delete articles that user has permission to do
        $rules = Rule::getPermission();
        if (1 == count($ids)) {
            $row      = $modelArticle->find($ids[0]);
            $slug     = Draft::getStatusSlug($row->status);
            $resource = $slug . '-delete';
            if (!(isset($rules[$row->category][$resource]) 
                and $rules[$row->category][$resource])
            ) {
                return $this->jumpToDenied();
            }
        } else {
            $rows     = $modelArticle->select(array('id' => $ids));
            $ids      = array();
            foreach ($rows as $row) {
                $slug     = Draft::getStatusSlug($row->status);
                $resource = $slug . '-delete';
                if (isset($rules[$row->category][$resource]) 
                    and $rules[$row->category][$resource]
                ) {
                    $ids[] = $row->id;
                }
            }
        }

        $resultsetArticle = $modelArticle->select(array('id' => $ids));

        // Step operation
        foreach ($resultsetArticle as $article) {
            // Delete feature image
            if ($article->image) {
                @unlink(Pi::path($article->image));
                @unlink(Pi::path(Media::getThumbFromOriginal($article->image)));
            }
        }
        
        // Batch operation
        // Deleting extended fields
        $this->getModel('extended')->delete(array('article' => $ids));
        
        // Deleting statistics
        $this->getModel('stats')->delete(array('article' => $ids));
        
        // Deleting compiled article
        $this->getModel('compiled')->delete(array('article' => $ids));
        
        // Delete tag
        if ($this->config('enable_tag')) {
            Pi::service('tag')->delete($module, $ids);
        }
        // Delete related articles
        $this->getModel('related')->delete(array('article' => $ids));

        // Delete visits
        $this->getModel('visit')->delete(array('article' => $ids));

        // Delete assets
        $modelAsset->delete(array('article' => $ids));

        // Delete article directly
        $modelArticle->delete(array('id' => $ids));

        // Clear cache
        Pi::service('render')->flushCache($module);

        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            // Go to list page
            return $this->redirect()->toRoute('', array(
                'module'        => $module,
                'controller'    => 'article',
                'action'        => 'published',
                'from'          => 'all',
            ));
        }
    }
    
    /**
     * Get article by title via AJAX.
     * 
     * @return ViewModel 
     */
    public function getFuzzyArticleAction()
    {
        Pi::service('log')->mute();
        $articles   = array();
        $pageCount  = $total = 0;
        $module     = $this->getModule();
        $where      = array('status' => Article::FIELD_STATUS_PUBLISHED);

        $keyword = $this->params('keyword', '');
        $type    = $this->params('type', 'title');
        $limit   = $this->params('limit', 10);
        $limit   = $limit > 100 ? 100 : $limit;
        $page    = $this->params('page', 1);
        $exclude = $this->params('exclude', 0);
        $offset  = $limit * ($page - 1);

        $articleModel   = $this->getModel('article');

        if (strcasecmp('tag', $type) == 0) {
            if ($keyword) {
                $total     = Pi::service('tag')->getCount($keyword, $module);
                $pageCount = ceil($total / $limit);

                // Get article ids
                $articleIds = Pi::service('tag')->getList(
                    $keyword, 
                    $module,
                    '', 
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
        Pi::service('log')->mute();
        $subject = trim($this->params('subject', ''));
        $id      = $this->params('id', null);
        $result  = false;

        if ($subject) {
            $articleModel = $this->getModel('article');
            $result = $articleModel->checkSubjectExists($subject, $id);
        }

        echo json_encode(array(
            'status'  => $result ? false : true,
            'message' => $result ? __('Subject is used by another article.') 
                : __('ok'),
        ));
        exit;
    }
    
    /**
     * Count view number by AJAX
     */
    public function countAction()
    {
        Pi::service('log')->mute();
        
        $id = $this->params('id', 0);
        if (!empty($id)) {
            Stats::addVisit($id, $this->getModule());
        }
        
        echo json_encode(array(
            'status'  => true,
            'message' => __('success'),
        ));
        exit;
    }
}
