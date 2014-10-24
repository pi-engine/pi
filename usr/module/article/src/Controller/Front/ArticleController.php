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
     * Article homepage, all page content are dressed up by user 
     */
    public function indexAction()
    {
        if ($this->config('default_homepage')) {
            return $this->redirect()
                        ->toUrl(Pi::url($this->config('default_homepage')));
        }
        
        $module = $this->getModule();
        $seo = Pi::api('page', $module)->getSeoMeta($this->params('action'));
        $this->view()->assign('seo', $seo);
        
        $theme = $this->config('theme');
        if ($theme) {
            Pi::service('theme')->setTheme($theme);
        }
        $this->view()->setTemplate('article-index');
    }
    
    /**
     * Article detail page
     * 
     * @return ViewModel 
     */
    public function detailAction()
    {
        $id       = $this->params('id');
        $page     = $this->params('p', 1);
        $remain   = $this->params('r', '');
        $module   = $this->getModule();

        $details = Entity::getEntity($id);
        
        // Return 404 if category or cluster is deactivated
        $category = Pi::api('category', $module)->getList(array(
            'id'     => $details['category'],
            'active' => 1,
        ));
        $cluster  = Pi::api('cluster', $module)->getList(array(
            'id'     => $details['cluster']['id'],
            'active' => 1,
        ));
        if (($details['category'] && empty($category))
            || ($details['cluster'] && empty($cluster))) {
            return $this->jumpTo404(__('Page not found'));
        }

        if (!$id or ($details['time_publish'] > time())) {
            return $this->jumpTo404(__('Page not found'));
        }
        if (empty($details['active'])) {
            return $this->jumpToException(
                __('The article requested is not active'),
                503
            );
        }

        $params = array(
            'time'        => date('Ymd', $details['time_publish']),
            'id'          => $id,
            'r'           => -1,
        );
        $details['view']   = Pi::api('api', $module)->getUrl('detail', $params, $details);
        $params['r']       = $page;
        $details['remain'] = Pi::api('api', $module)->getUrl('detail', $params, $details);
        
        $this->view()->assign(array(
            'details'     => $details,
            'page'        => $page,
            'config'      => Pi::config('', $module),
            'module'      => $module,
            'remain'      => $remain,
        ));
        
        $theme = $this->config('theme');
        if ($theme) {
            Pi::service('theme')->setTheme($theme);
        }
        $this->view()->setTemplate('article-detail');
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
        
        $row   = $this->getModel('article')->find($id);

        // Check user has permission to edit
        $rules = Rule::getPermission();
        $slug  = Draft::getStatusSlug($row->status);
        $resource = $slug . '-edit';
        if ($row->category
            && !(isset($rules[$row->category][$resource]) 
            && $rules[$row->category][$resource])
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
        
        // Copy article details to draft table
        $draft = $row->toArray();
        $draft['article'] = $draft['id'];
        $draft['status']  = DraftModel::FIELD_STATUS_DRAFT;
        unset($draft['id']);

        // Get compound data
        $compound = Pi::registry('field', $module)->read('compound');
        foreach (array_keys($compound) as $name) {
            $handler = Pi::api('field', $module)->loadCompoundFieldHandler($name);
            $data    = $handler->encode($draft['article']);
            $draft   = array_merge($draft, $data);
        }
        
        // Get custom data
        $custom = Pi::registry('field', $module)->read('custom');
        foreach (array_keys($custom) as $name) {
            $handler = Pi::api('field', $module)->loadCustomFieldHandler($name);
            $data    = $handler->encode($draft['article']);
            $draft   = array_merge($draft, $data);
        }
        
        // Save as draft
        $draftRow = $draftModel->saveRow($draft);
        $draftId  = $draftRow->id;
        
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

        $module     = $this->getModule();
        $page       = $this->params('p', 1);
        $limit      = $this->params('limit', 40);
        $from       = $this->params('from', 'my');
        $keyword    = $this->params('keyword', '');
        $category   = $this->params('category', null);
        $cluster    = $this->params('cluster', null);
        $filter     = $this->params('filter', '');
        
        // Get permission
        $rules      = Rule::getPermission();
        $allowedCategories = array_keys($rules);
        if ($category > 1) {
            $children = Pi::api('category', $module)->getDescendantIds($category);
            $allowedCategories = array_intersect($allowedCategories, $children);
        } else {
            // In case an article is not belong to any category
            $allowedCategories[] = 0;
        }
        if (!empty($category)
            && !in_array($category, $allowedCategories)
        ) {
            return $this->jumpToDenied();
        }
        
        // Build where
        $where = array(
            'status' => Article::FIELD_STATUS_PUBLISHED,
        );
        if ($cluster) {
            $where['cluster'] = $cluster;
        }
        if ('my' == $from) {
            $where['uid'] = Pi::user()->getId() ?: 0;
        }
        $where['category'] = $allowedCategories ?: 0;
        if (!empty($keyword)) {
            $where['subject like ?'] = sprintf('%%%s%%', $keyword);
        }
        $where = array_filter($where, function($v) {
            return $v !== null;
        });
        
        // Active condition
        if ($filter == 'active') {
            $where['active'] = 1;
        } else if ($filter == 'deactive') {
            $where['active'] = 0;
        }

        // Retrieve data
        $options = array(
            'section'    => 'admin',
            'controller' => 'article',
            'action'     => 'published',
        );
        $order   = Pi::api('api', $module)->canonizeOrder($options);
        $columns = Pi::api('api', $module)->canonizeColumns($options);
        $data = Entity::getArticlePage(
            $where,
            $page,
            $limit,
            $columns ?: null,
            $order ?: 'time_publish DESC'
        );
        
        // Get article operation stats data
        $ids   = array_keys($data);
        $stats = Stats::getTotalVisit($ids, 'A');

        // Total count
        $totalCount = Entity::count($where);

        $params = array(
            'module'    => $module,
        );
        foreach (array('cluster', 'category', 'filter', 'keyword', 'from') as $key) {
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

        $categories = Pi::api('category', $module)->getList(array(
            'id' => array_keys($rules),
        ));
        
        if (Pi::api('form', $module)->isDisplayField('cluster')) {
            $clusters = Pi::api('cluster', $module)->getList();
            $this->view()->assign('clusters', $clusters);
        }
        
        $this->view()->assign(array(
            'title'      => __('Published'),
            'data'       => $data,
            'form'       => $form,
            'paginator'  => $paginator,
            'summary'    => Entity::getSummary($from, $rules),
            'category'   => $category,
            'filter'     => $filter,
            'categories' => $categories,
            'action'     => 'published',
            'flags'      => $flags,
            'status'     => Article::FIELD_STATUS_PUBLISHED,
            'from'       => $from,
            'rules'      => $rules,
            'cluster'    => $cluster,
            'stats'      => $stats,
            'section'    => $this->section,
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
        
        // Deleting statistics
        $this->getModel('stats')->delete(array('article' => $ids));
        
        // Deleting compiled article
        $this->getModel('compiled')->delete(array('article' => $ids));
        
        // Remove compound data
        $compound = Pi::registry('field', $module)->read('compound');
        foreach (array_keys($compound) as $name) {
            $handler = Pi::api('field', $module)->loadCompoundFieldHandler($name);
            $handler->delete($ids);
        }
        
        // Remove custom data
        $custom = Pi::registry('field', $module)->read('custom');
        foreach (array_keys($custom) as $name) {
            $handler = Pi::api('field', $module)->loadCustomFieldHandler($name);
            $handler->delete($ids);
        }

        // Delete stats data
        $this->getModel('stats')->delete(array('article' => $ids));

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
            $total      = $articleModel->count($where);
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
     * AJAX action, count browse number.
     * 
     * @param `id` Article ID
     * @return JSON
     */
    public function statsAction()
    {
        Pi::service('log')->mute();
        
        $id = $this->params('id', 0);
        if (!empty($id)) {
            // Write visitor info into log file
            $args = array(
                'article'  => $id,
                'time'     => time(),
                'ip'       => Pi::user()->getIp(),
                'uid'      => Pi::user()->getId() ?: 0,
            );
            Pi::service('audit')->attach('csv', array(
                'file' => Pi::path('log') . '/article-browse.csv',
            ));
            Pi::service('audit')->log('csv', $args);
            
            // Increase visit number
            $this->getModel('stats')->increase($id);
        }
        
        echo json_encode(array(
            'status'  => true,
            'message' => __('success'),
        ));
        exit;
    }
}
