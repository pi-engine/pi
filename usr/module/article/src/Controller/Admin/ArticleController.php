<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Controller\Admin;

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
 * 1. Published article list page for management
 * 2. Active/deactivate/detete/edit article
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ArticleController extends ActionController
{
    /**
     * Default page, redirect to published article list page
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute(
            'admin',
            array(
                'action' => 'published',
                'from'   => 'all',
            )
        );
    }
    
    /**
     * Delete published articles
     * 
     * @return ViewModel 
     */
    public function deleteAction()
    {
        $id     = Service::getParam($this, 'id', '');
        $ids    = array_filter(explode(',', $id));
        $from   = Service::getParam($this, 'from', '');

        if (empty($ids)) {
            return $this->jumpTo404(__('Invalid article ID'));
        }
        
        $module         = $this->getModule();
        $modelArticle   = $this->getModel('article');
        $modelAsset     = $this->getModel('asset');
        
        // Delete articles that user has permission to do
        $rules = Service::getPermission();
        if (1 == count($ids)) {
            $row      = $modelArticle->find($ids[0]);
            $slug     = Service::getStatusSlug($row->status);
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
                $slug     = Service::getStatusSlug($row->status);
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
                @unlink(Pi::path(Service::getThumbFromOriginal($article->image)));
            }
        }
        
        // Batch operation
        // Deleting extended fields
        $this->getModel('extended')->delete(array('article' => $ids));
        
        // Deleting statistics
        $this->getModel('statistics')->delete(array('article' => $ids));
        
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
                'controller' => 'article',
                'action'     => 'published',
                'from'       => 'all',
            ));
        }
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
            return $this->redirect()->toRoute('admin', array(
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
        $from   = Service::getParam($this, 'from', 'all');
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
            $user   = Pi::service('user')->getUser();
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
}
