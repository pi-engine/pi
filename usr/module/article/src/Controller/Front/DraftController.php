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
use Module\Article\Model\Draft as DraftModel;
use Module\Article\Model\Article;
use Module\Article\Rule;
use Module\Article\Compiled;
use Module\Article\Entity;
use Module\Article\Draft;


/**
 * Draft controller
 * 
 * Feature list:
 * 
 * 1. Add/edit/delete draft
 * 2. List draft/pending/rejected article
 * 3. Approve/publish/update draft
 * 4. AJAX action used to save/remove feature image
 * 5. AJAX action used to save assets
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DraftController extends ActionController
{
    /**
     * Section identifier
     * @var string
     */
    protected $section = 'front';

    /**
     * Publish a article, the status of article will be changed to pendding
     * 
     * @param int  $id  Article ID
     * @return array 
     */
    protected function publish($id)
    {
        $result = array(
            'status'    => false,
            'message'   => array(),
            'data'      => array(),
        );

        if (!$id) {
            return array('message' => __('Not enough parameter.'));
        }
        
        $modelDraft = $this->getModel('draft');
        $rowDraft   = $modelDraft->find($id);

        if (!$rowDraft->id 
            or !in_array(
                $rowDraft->status,
                array(DraftModel::FIELD_STATUS_DRAFT, DraftModel::FIELD_STATUS_REJECTED)
            )
        ) {
            return array('message' => __('Invalid draft.'));
        }
        
        if ($rowDraft->article) {
            return array('message' => __('Draft has been published.'));
        }
        
        $rowDraft->status      = DraftModel::FIELD_STATUS_PENDING;
        $rowDraft->time_submit = time();
        $rowDraft->save();

        $result['status']   = true;
        $result['message']  = __('Draft submitted successfully.');
        $result['data']     = array(
            'id'          => $id,
            'time_submit' => $rowDraft->time_submit,
            'status'      => __('Pending'),
            'btn_value'   => __('Approve'),
        );

        return $result;
    }

    /**
     * Approve an article, the article details will be storing 
     * into article table, status of article in article table will be 
     * changed to published, and draft will be deleted.
     * 
     * @param int    $id        Article ID
     * @return array
     */
    protected function approve($id)
    {
        if (!$id) {
            return array('message' => __('Not enough parameter.'));
        }
        
        $model  = $this->getModel('draft');
        $row    = $model->findRow($id, 'id', false);
        if (!$row->id or $row->status != DraftModel::FIELD_STATUS_PENDING) {
            return array('message' => __('Invalid draft.'));
        }
        
        $result = array(
            'status'    => false,
            'message'   => array(),
            'data'      => array(),
        );
        
        $module         = $this->getModule();
        $modelArticle   = $this->getModel('article');
        
        // move draft to article
        $article = (array) $row;
        $article['status']       = Article::FIELD_STATUS_PUBLISHED;
        $article['active']       = 1;
        $article['time_publish'] = $row->time_publish ?: time();
        $article['time_update']  = $row->time_update ?: time();
        unset($article['id']);
        $modelArticle->canonizeColumns($article);
        $rowArticle = $modelArticle->createRow($article);
        $rowArticle->save();
        $articleId = $rowArticle->id;
        
        // Compiled article content
        $modelCompiled   = $this->getModel('compiled');
        $compiledType    = $this->config('compiled_type') ?: 'html';
        $compiledContent = Compiled::compiled(
            $rowArticle->markup,
            $rowArticle->content,
            $compiledType
        );
        $compiled        = array(
            'name'            => $articleId . '-' . $compiledType,
            'article'         => $articleId,
            'type'            => $compiledType,
            'content'         => $compiledContent,
        );
        $rowCompiled     = $modelCompiled->createRow($compiled);
        $rowCompiled->save();
        
        // Save custom element data
        $custom = Pi::registry('field', $module)->read('custom');
        foreach (array_keys($custom) as $name) {
            $handler = Pi::api('field', $module)->loadCustomFieldHandler($name);
            $handler->add($articleId, $row->$name);
        }
        
        // Save compound data
        $compound = Pi::registry('field', $module)->read('compound');
        foreach (array_keys($compound) as $name) {
            $handler = Pi::api('field', $module)->loadCompoundFieldHandler($name);
            $handler->add($articleId, $row->$name);
        }

        // delete draft
        $model->delete(array('id' => $id));

        $result['status']   = true;
        $result['data']['redirect'] = $this->url(
            '',
            array('action' => 'published', 'controller' => 'article')
        );

        return $result;
    }

    /**
     * Update published article data
     * 
     * @param  int $id  Article ID
     * @return array 
     */
    protected function update($id)
    {
        $result = array(
            'status'    => false,
            'message'   => array(),
            'data'      => array(),
        );

        if (!$id) {
            $result['message'] = __('No enough parameter.');
        }
        
        $modelDraft = $this->getModel('draft');
        $rowDraft   = $modelDraft->findRow($id, 'id', false);

        if (!$rowDraft->id or !$rowDraft->article) {
            $result['message'] = __('Invalid draft.');
        }
        
        $module         = $this->getModule();
        $modelArticle   = $this->getModel('article');
        
        // move draft to article
        $articleId = $rowDraft->article;
        $article = (array) $rowDraft;
        $article['user_update'] = Pi::user()->getId();
        $article['time_update'] = time();
        unset($article['status']);
        unset($article['id']);
        $rowArticle = $modelArticle->find($rowDraft->article);
        $modelArticle->canonizeColumns($article);
        $rowArticle->assign($article);
        $rowArticle->save();
        
        // Compiled article content
        $modelCompiled   = $this->getModel('compiled');
        $compiledType    = $this->config('compiled_type') ?: 'html';
        $compiledContent = Compiled::compiled(
            $rowArticle->markup,
            $rowArticle->content,
            $compiledType
        );
        $name            = $articleId . '-' . $compiledType;
        $compiled        = array(
            'name'            => $name,
            'article'         => $articleId,
            'type'            => $compiledType,
            'content'         => $compiledContent,
        );
        $rowCompiled     = $modelCompiled->find($name, 'name');
        if ($rowCompiled->id) {
            $rowCompiled->assign($compiled);
            $rowCompiled->save();
        } else {
            $rowCompiled = $modelCompiled->createRow($compiled);
            $rowCompiled->save();
        }
        
        // Save custom element data
        $custom = Pi::registry('field', $module)->read('custom');
        foreach (array_keys($custom) as $field) {
            $handler = Pi::api('field', $module)->loadCustomFieldHandler($field);
            $handler->delete($articleId);
            $handler->add($articleId, $rowDraft->$field);
        }
        
        // Save compound data
        $compound = Pi::registry('field', $module)->read('compound');
        foreach (array_keys($compound) as $field) {
            $handler = Pi::api('field', $module)->loadCompoundFieldHandler($field);
            $handler->delete($articleId);
            $handler->add($articleId, $rowDraft->$field);
        }

        // Delete draft
        $modelDraft->delete(array('id' => $rowDraft->id));

        $result['status']   = true;
        $result['data']['redirect'] = $this->url(
            '',
            array('action' => 'published', 'controller' => 'article')
        );
        $result['message'] = __('Article update successfully.');

        return $result;
    }

    /**
     * Save new article into draft table, 
     * and the status of article will be draft.
     * 
     * @param array  $data  Posted article details
     * @return boolean 
     */
    protected function saveDraft($data)
    {
        $rowDraft   = $id = null;
        $modelDraft = $this->getModel('draft');

        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
        }
        unset($data['article']);
        
        $pages = Draft::breakPage($data['content']);
        $data['pages']     = count($pages);
        $data['time_save'] = time();
        $data['uid']       = $data['uid'] ?: Pi::user()->getId();

        if (empty($id)) {
            $data['status'] = DraftModel::FIELD_STATUS_DRAFT;
            $rowDraft = $modelDraft->saveRow($data);

            if (empty($rowDraft->id)) {
                return false;
            }
        } else {
            if (isset($data['status'])) {
                unset($data['status']);
            }

            $rowDraft = $modelDraft->find($id);
            if (empty($rowDraft->id)) {
                return false;
            }

            $modelDraft->updateRow($data, array('id' => $rowDraft->id));
        }

        return $rowDraft->id;
    }
    
    /**
     * Get articles by condition
     * 
     * @param int     $status   Draft status flag
     * @param string  $from     Show all articles or my articles
     * @param array   $options  Where condition
     */
    public function showDraftPage($status, $from = 'my', $options = array())
    {
        $where  = $options;
        $page   = $this->params('p', 1);
        $limit  = $this->params('limit', 20);

        $where['status']  = $status;
        $where['article'] = 0;
        if ('my' == $from) {
            $where['uid'] = Pi::user()->getId();
        }
        if (isset($options['keyword'])) {
            $where['subject like ?'] = sprintf('%%%s%%', $options['keyword']);
        }

        $resultsetDraft = Draft::getDraftPage($where, $page, $limit);

        // Total count
        $totalCount = (int) $this->getModel('draft')->count($where);

        // Paginator
        $paginator = Paginator::factory($totalCount, array(
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => array(
                'page_param'    => 'p',
                'params'        => array(
                    'status'        => $status,
                    'from'          => $from,
                    //'where'         => urlencode(json_encode($options)),
                    //'limit'         => $limit,
                ),
            ),
        ));

        $this->view()->assign(array(
            'data'      => $resultsetDraft,
            'paginator' => $paginator,
            'status'    => $status,
            'from'      => $from,
            //'page'      => $page,
            //'limit'     => $limit,
        ));
    }

    /**
     * AJAX action, save article to draft.
     * 
     * @return JSON 
     */
    public function saveAction()
    {
        // Denied user viewing if no front-end management permission assigned
        if (!$this->config('enable_front_edit') && 'front' == $this->section) {
            return $this->jumpTo404();
        }
        
        if (!$this->request->isPost()) {
            return $this->jumpTo404();
        }
        $result = array(
            'status'    => false,
            'message'   => array(),
            'data'      => array(),
        );

        $module = $this->getModule();
        $form   = Pi::api('form', $module)->loadForm('draft', true);
        $form->setData($this->request->getPost());
        
        if (!$form->isValid()) {
            return array(
                'message' => $form->getMessages(),
            );
        }
        
        $data = $form->getData();
        $data['user_update'] = Pi::user()->getId();
        $id   = $this->saveDraft($data);
        if (!$id) {
            return array(
                'message' => __('Failed to save draft.'),
            );
        }
        
        $result['status']   = true;
        $result['data']     = array('id' => $id);

        $result['data']['preview_url'] = Pi::api('api', $module)->getUrl(
            'detail',
            array(
                'time'      => date('Ymd', time()),
                'id'        => $id,
                'preview'   => 1,
            ),
            $data
        );
        $result['message'] = __('Draft saved successfully.');

        return $result;
    }
    
    /**
     * List articles for management
     */
    public function listAction()
    {
        // Denied user viewing if no front-end management permission assigned
        if (!$this->config('enable_front_edit') && 'front' == $this->section) {
            return $this->jumpTo404();
        }
        
        $status = $this->params('status', DraftModel::FIELD_STATUS_DRAFT);
        $from   = $this->params('from', 'my');
        $where  = $this->params('where', '');
        $where  = json_decode(urldecode($where), true);
        $where  = is_array($where) ? array_filter($where) : array();
        if (!in_array($from, array('my', 'all'))) {
            throw new \Exception(__('Invalid source'));
        }
        
        // Getting permission
        $rules      = Rule::getPermission('my' == $from ? true : false);
        $categories = array_keys($rules);
        $where['category'] = empty($categories) ? 0 : $categories;
        
        $this->showDraftPage($status, $from, $where);
        
        $title  = '';
        switch ($status) {
            case DraftModel::FIELD_STATUS_DRAFT:
                $title = __('Draft');
                $name  = 'draft';
                break;
            case DraftModel::FIELD_STATUS_PENDING:
                $title = __('Pending');
                $name  = 'pending';
                break;
            case DraftModel::FIELD_STATUS_REJECTED:
                $title = __('Rejected');
                $name  = 'rejected';
                break;
        }
        $flags = array(
            'draft'     => DraftModel::FIELD_STATUS_DRAFT,
            'pending'   => DraftModel::FIELD_STATUS_PENDING,
            'rejected'  => DraftModel::FIELD_STATUS_REJECTED,
            'published' => \Module\Article\Model\Article::FIELD_STATUS_PUBLISHED,
        );

        $this->view()->assign(array(
            'title'   => $title,
            'summary' => Entity::getSummary($from, $rules),
            'flags'   => $flags,
            'rules'   => $rules,
            'section' => $this->section,
        ));
        
        $module = $this->getModule();
        if ('all' == $from) {
            $template = sprintf('%s-%s', 'article', $name);
            $this->view()->setTemplate($template, $module, 'front');
        } else {
            $this->view()->setTemplate('draft-list', $module, 'front');
        }
    }
    
    /**
     *  Add a draft
     * 
     * @return ViewModel 
     */
    public function addAction()
    {
        // Denied user viewing if no front-end management permission assigned
        if (!$this->config('enable_front_edit') && 'front' == $this->section) {
            return $this->jumpTo404();
        }
        
        $rules        = Rule::getPermission();
        $denied       = true;
        $listCategory = array();
        $approve      = array();
        $delete       = array();
        foreach ($rules as $key => $rule) {
            if (isset($rule['compose']) and $rule['compose']) {
                $denied = false;
                $listCategory[$key] = true;
            }
            if (isset($rule['approve']) and $rule['approve']) {
                $approve[] = $key;
            }
            if (isset($rule['approve-delete']) and $rule['approve-delete']) {
                $delete[] = $key;
            }
        }
        if ($rules && $denied) {
            return $this->jumpToDenied();
        }
        
        $module     = $this->getModule();
        $form       = Pi::api('form', $module)->loadForm('draft');
        if ($form->has('category')) {
            $form->get('category')->getValueOptions($listCategory);
        }
        
        $form->setData(array(
            'category'      => $this->config('default_category'),
            'source'        => $this->config('default_source'),
            'uid'           => Pi::user()->getId(),
        ));
        
        $columns = $this->getModel('article')->getColumns(true);
        $template = array_flip(array_merge($columns, array('article', 'time_save')));
        array_walk($template, function(&$value) {
            $value = '';
        });
        
        $this->view()->assign(array(
            'form'          => $form,
            'rules'         => $rules,
            'approve'       => $approve,
            'delete'        => $delete,
            'status'        => DraftModel::FIELD_STATUS_DRAFT,
            'draft'         => $template,
            'currentDelete' => true,
            'autoSave'      => $this->config('autosave_interval'),
        ));
        $this->view()->setTemplate('draft-edit', $module, 'front');
    }
    
    /**
     * Edit a draft
     * 
     * @return ViewModel 
     */
    public function editAction()
    {
        // Denied user viewing if no front-end management permission assigned
        if (!$this->config('enable_front_edit') && 'front' == $this->section) {
            return $this->jumpTo404();
        }
        
        $id       = $this->params('id', 0);
        $module   = $this->getModule();

        if (!$id) {
            return ;
        }
        
        $draftModel = $this->getModel('draft');
        $row        = $draftModel->findRow($id, 'id', false);

        // Generate user permissions
        $status = '';
        switch ((int) $row->status) {
            case DraftModel::FIELD_STATUS_DRAFT:
                $status = 'draft';
                break;
            case DraftModel::FIELD_STATUS_PENDING:
                $status = 'pending';
                break;
            case DraftModel::FIELD_STATUS_REJECTED:
                $status = 'rejected';
                break;
        }
        if ($row->article) {
            $status = 'publish';
        }
        $isMine = $row->uid == Pi::user()->getId();
        $rules  = Rule::getPermission($isMine);
        if ($row->category
            && !(isset($rules[$row->category][$status . '-edit']) 
            && $rules[$row->category][$status . '-edit'])
        ) {
            return $this->jumpToDenied();
        }
        $categories = array();
        $approve    = array();
        $delete     = array();
        foreach ($rules as $key => $rule) {
            if (isset($rule[$status . '-edit']) and $rule[$status . '-edit']) {
                $categories[$key] = true;
            }
            // Getting approving and deleting permission for draft article
            if (isset($rule['approve']) and $rule['approve']) {
                $approve[] = $key;
            }
            if (isset($rule['approve-delete']) and $rule['approve-delete']) {
                $delete[] = $key;
            }
        }
        $currentDelete = (isset($rules[$row->category][$status . '-delete']) 
                          and $rules[$row->category][$status . '-delete']) 
            ? true : false;
        $currentApprove = (isset($rules[$row->category]['approve']) 
                           and $rules[$row->category]['approve']) 
            ? true : false;

        if (empty($row)) {
            return ;
        }
        
        // prepare data
        $data = (array) $row;
        $form = Pi::api('form', $module)->loadForm('draft');
        $form->get('category')->getValueOptions($categories);
        $form->setData($data);
        
        // Get update user info
        if ($data['user_update']) {
            $userUpdate = Pi::user()->get($data['user_update'], array('id', 'name'));
            if ($userUpdate) {
                $this->view()->assign('userUpdate', array(
                    'id'   => $userUpdate['id'],
                    'name' => $userUpdate['name'],
                ));
            }
        }
        
        $this->view()->assign(array(
            'title'          => __('Edit Article'),
            'form'           => $form,
            'draft'          => (array) $row,
            'config'         => Pi::config('', $module),
            'from'           => $this->params('from', ''),
            'status'         => $row->article ? Article::FIELD_STATUS_PUBLISHED : $row->status,
            'rules'          => $rules,
            'approve'        => $approve,
            'delete'         => $delete,
            'currentDelete'  => $currentDelete,
            'currentApprove' => $currentApprove,
            'autoSave'       => $this->config('autosave_interval'),
        ));

        $this->view()->setTemplate('draft-edit', $module, 'front');
    }

    /**
     * Delete a draft
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
        $status = $this->params('status', 0);
        $source = $this->params('source', '');

        if (empty($ids)) {
            return $this->jumpTo404(__('Invalid draft ID'));
        }
        
        // Delete draft articles that user has permission to do
        $isMine = false;
        if (ARTICLE::FIELD_STATUS_PUBLISHED != $status and 'my' == $source) {
            $isMine = true;
        }
        $model = $this->getModel('draft');
        $rules = Rule::getPermission($isMine);
        if (1 == count($ids)) {
            $row      = $model->find($ids[0]);
            if ($row) {
                $slug     = Draft::getStatusSlug($row->status);
                $resource = $slug . '-delete';
                if (!(isset($rules[$row->category][$resource]) 
                    and $rules[$row->category][$resource])
                ) {
                    return $this->jumpToDenied();
                }
            }
        } else {
            $rows     = $model->select(array('id' => $ids));
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
        
        // Delete draft
        if (!empty($ids)) {
            $model->delete(array('id' => $ids));
        }

        // Redirect to original page
        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            return $this->redirect()->toRoute('', array(
                'action'        => 'list',
                'controller'    => 'draft',
                'status'        => DraftModel::FIELD_STATUS_DRAFT,
            ));
        }
    }

    /**
     * AJAX action, publish a draft
     * 
     * @return JSON
     */
    public function publishAction()
    {
        // Denied user viewing if no front-end management permission assigned
        if (!$this->config('enable_front_edit') && 'front' == $this->section) {
            return $this->jumpTo404();
        }
        
        if (!$this->request->isPost()) {
            return $this->jumpToDenied();
        }
        
        $form    = Pi::api('form', $this->getModule())->loadForm('draft', true);
        $form->setData($this->request->getPost());

        if (!$form->isValid()) {
            return array('message' => $form->getMessages());
        }
        
        $data = $form->getData();
        $id   = $this->saveDraft($data);
        if (!$id) {
            return array('message' => __('Failed to save draft.'));
        }
        
        $result = $this->publish($id);

        return $result;
    }

    /**
     * Reject a draft, article status will be changed to rejected
     * 
     * @return ViewModel 
     */
    public function rejectAction()
    {
        // Denied user viewing if no front-end management permission assigned
        if (!$this->config('enable_front_edit') && 'front' == $this->section) {
            return $this->jumpTo404();
        }
        
        $result = array(
            'status'    => true,
            'message'   => array(),
            'data'      => array(),
        );

        $id           = $this->params('id', 0);
        $rejectReason = $this->params('memo', '');

        if (!$id) {
            return array('message' => __('Not enough parameter.'));
        }
        
        $model = $this->getModel('draft');
        $row   = $model->find($id);
        if (!$row->id or $row->status != DraftModel::FIELD_STATUS_PENDING) {
            return array('message' => __('Invalid draft.'));
        }
        
        // Getting permission and checking it
        $rules = Rule::getPermission();
        if (!(isset($rules[$row->category]['approve']) 
            and $rules[$row->category]['approve'])
        ) {
            return $this->jumpToDenied();
        }
        
        $row->status        = DraftModel::FIELD_STATUS_REJECTED;
        $row->reject_reason = $rejectReason;
        $row->save();

        $result['status']   = true;
        $result['data']['redirect'] = $this->url(
            '',
            array('action'=>'list', 'controller' => 'draft')
        );

        return $result;
    }

    /**
     * AJAX action, approve a draft.
     * 
     * @return JSON 
     */
    public function approveAction()
    {
        // Denied user viewing if no front-end management permission assigned
        if (!$this->config('enable_front_edit') && 'front' == $this->section) {
            return $this->jumpTo404();
        }
        
        $form = Pi::api('form', $this->getModule())->loadForm('draft', true);
        $form->setData($this->request->getPost());

        if (!$form->isValid()) {
            return array('message' => $form->getMessages());
        }
        
        $data = $form->getData();
        $id   = $this->saveDraft($data);

        if (!$id) {
            return array('message' => __('Failed to save draft.'));
        }
        $row = $this->getModel('draft')->findRow($id);
        
        // Getting permission and checking it
        $rules = Rule::getPermission();
        if (!(isset($rules[$row['category']]['approve']) 
            and $rules[$row['category']]['approve'])
        ) {
            return $this->jumpToDenied();
        }
        
        $result = $this->approve($id);
        $result['message'] = __('approve successfully.');

        return $result;
    }

    /**
     * Batch approve draft 
     */
    public function batchApproveAction()
    {
        // Denied user viewing if no front-end management permission assigned
        if (!$this->config('enable_front_edit') && 'front' == $this->section) {
            return $this->jumpTo404();
        }
        
        $id     = $this->params('id', '');
        $ids    = array_filter(explode(',', $id));
        $from   = $this->params('from', '');

        if ($ids) {
            // To approve articles that user has permission to approve
            $model = $this->getModel('draft');
            $rules = Rule::getPermission();
            if (1 == count($ids)) {
                $row = $model->find($ids[0]);
                if (!(isset($rules[$row->category]['approve']) 
                    and $rules[$row->category]['approve'])
                ) {
                    return $this->jumpToDenied();
                }
            } else {
                $rows = $model->select(array('id' => $ids));
                $ids  = array();
                foreach ($rows as $row) {
                    if (isset($rules[$row->category]['approve']) 
                        and $rules[$row->category]['approve']
                    ) {
                        $ids[] = $row->id;
                    }
                }
            }
            // Approve articles
            foreach ($ids as $id) {
                $this->approve($id);
            }
        }

        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            // Go to list page
            return $this->redirect()->toRoute(
                '',
                array('controller' =>'article', 'action' => 'published')
            );
        }
    }

    /**
     * Preview a draft article.
     * 
     * @return ViewModel 
     */
    public function previewAction()
    {
        $id       = $this->params('id');
        $page     = $this->params('p', 1);
        $remain   = $this->params('r', '');

        $time    = time();
        $module  = $this->getModule();
        $row     = $this->getModel('draft')->findRow($id, 'id', false);
        $details = Pi::api('field', $module)->resolver((array) $row);
        $details['time_publish'] = $time;
        
        $params = array(
            'module'    => $module,
            'preview'   => 1,
            'time'      => date('Ymd', $details['time_publish']),
            'id'        => $id,
        );
        
        if (!$id) {
            return $this->jumpTo404(__('Page not found'));
        }
        
        foreach ($details['content'] as &$value) {
            $value['url'] = Pi::api('api', $module)->getUrl(
                'detail',
                array_merge($params, array('p' => $value['page'])),
                $details
            );
        }
        
        $params['r']       =  -1;
        $details['view']   = Pi::api('api', $module)->getUrl('detail', $params, $details);;
        $params['r']       = $page;
        $details['remain'] = Pi::api('api', $module)->getUrl('detail', $params, $details);

        $this->view()->assign(array(
            'details'     => $details,
            'page'        => $page,
            'config'      => Pi::config('', $module),
            'remain'      => $remain,
        ));

        $theme = $this->config('theme');
        if ($theme) {
            Pi::service('theme')->setTheme($theme);
        }
        $this->view()->setTemplate('article-detail');
    }

    /**
     * Update a publish article
     * 
     * @return ViewModel 
     */
    public function updateAction()
    {
        // Denied user viewing if no front-end management permission assigned
        if (!$this->config('enable_front_edit') && 'front' == $this->section) {
            return $this->jumpTo404();
        }
        
        $form = Pi::api('form', $this->getModule())->loadForm('draft', true);
        $form->setData($this->request->getPost());

        if (!$form->isValid()) {
            return array('message' => $form->getMessages());
        }
        
        $data = $form->getData();
        $id   = $this->saveDraft($data);
        if (!$id) {
            return array('message', __('Failed to save draft.'));
        }
        $result = $this->update($id);

        return $result;
    }
}
