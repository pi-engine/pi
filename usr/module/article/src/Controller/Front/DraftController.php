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
use Module\Article\Controller\Admin\SetupController as Setup;
use Pi\Paginator\Paginator;
use Module\Article\Form\DraftEditForm;
use Module\Article\Form\DraftEditFilter;
use Module\Article\Model\Draft as DraftModel;
use Module\Article\Model\Article;
use Module\Article\Rule;
use Module\Article\Compiled;
use Module\Article\Entity;
use Module\Article\Media;
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
    
    const TAG_DELIMITER = ',';
    
    const RESULT_FALSE = false;
    const RESULT_TRUE  = true;

    /**
     * Get draft form instance
     * 
     * @param string  $action   The action to request when forms are submitted
     * @param array   $options  Optional parameters
     * @return \Module\Article\Form\DraftEditForm 
     */
    protected function getDraftForm($action, $options = array())
    {
        $form = new DraftEditForm('draft', $options);
        $form->setAttributes(array(
            'action'  => $this->url('', array('action' => $action)),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        ));

        return $form;
    }
    
    /**
     * Reading configuration data and assigning to template 
     */
    protected function setModuleConfig()
    {
        $module        = Pi::service('module')->current();
        $config        = Pi::config('', $module);
        $imageExt      = $config['image_extension'];
        $mediaExt      = $config['media_extension'];
        $attachmentExt = array_diff(
            array_map('trim', explode(',', $mediaExt)), 
            array_map('trim', explode(',', $imageExt))
        );
        $maxImageSize = $config['max_image_size'];
        $maxMediaSize = $config['max_media_size'];
        $defaultFeatureThumb = Pi::service('asset')
            ->getModuleAsset($config['default_feature_thumb'], $module);
        $this->view()->assign(array(
            'width'               => $config['feature_width'],
            'height'              => $config['feature_height'],
            'thumbWidth'          => $config['feature_thumb_width'],
            'thumbHeight'         => $config['feature_thumb_height'],
            'imageExtension'      => $imageExt,
            'maxImageSize'        => $maxImageSize,
            'mediaExtension'      => $mediaExt,
            'maxMediaSize'        => $maxMediaSize,
            'autoSave'            => $config['autosave_interval'],
            'maxSummaryLength'    => $config['max_summary_length'],
            'maxSubjectLength'    => $config['max_subject_length'],
            'maxSubtitleLength'   => $config['max_subtitle_length'],
            'defaultSource'       => $config['default_source'],
            'defaultFeatureThumb' => $defaultFeatureThumb,
            'contentImageWidth'   => $config['content_thumb_width'],
            'contentImageHeight'  => $config['content_thumb_height'],
            'attachmentExtension' => implode(',', $attachmentExt),
        ));
    }

    /**
     * Valid form such as subject, subtitle, category, slug etc.
     * 
     * @param array   $data      Posted data for validating
     * @param string  $article   
     * @param array   $elements  Displaying form defined by user
     * @return array 
     */
    protected function validateForm($data, $article = null, $elements = array())
    {
        $result = array(
            'status'    => self::RESULT_TRUE,
            'message'   => array(),
            'data'      => array(),
        );
        $config       = Pi::config('', $this->getModule());
        $modelArticle = $this->getModel('article');

        // Validate subject
        if (in_array('subject', $elements)) {
            $subjectLength = new \Zend\Validator\StringLength(array(
                'max'       => $config['max_subject_length'],
                'encoding'  => 'utf-8',
            ));
            if (empty($data['subject'])) {
                $result['status'] = self::RESULT_FALSE;
                $result['message']['subject'] = array(
                    'isEmpty' => __('Subject cannot be empty.')
                );
            } else if (!$subjectLength->isValid($data['subject'])) {
                $result['status'] = self::RESULT_FALSE;
                $result['message']['subject'] = $subjectLength->getMessages();
            } else if ($modelArticle->checkSubjectExists(
                $data['subject'],
                $article)
            ) {
                $result['status'] = self::RESULT_FALSE;
                $result['message']['subject'] = array(
                    'duplicated' => __('Subject is used by another article.')
                );
            }
        }

        // Validate slug
        if (in_array('slug', $elements) and !empty($data['slug'])) {
            if (!$subjectLength->isValid($data['slug'])) {
                $result['status'] = self::RESULT_FALSE;
                $result['message']['slug'] = $subjectLength->getMessages();
            }
        }

        // Validate subtitle
        if (in_array('subtitle', $elements)) {
            $subtitleLength = new \Zend\Validator\StringLength(array(
                'max'       => $config['max_subtitle_length'],
                'encoding'  => 'utf-8',
            ));
            if (isset($data['subtitle']) 
                && !$subtitleLength->isValid($data['subtitle'])
            ) {
                $result['status'] = self::RESULT_FALSE;
                $result['message']['subtitle'] = $subtitleLength->getMessages();
            }
        }

        // Validate summary
        if (in_array('summary', $elements)) {
            $summaryLength = new \Zend\Validator\StringLength(array(
                'max'       => $config['max_summary_length'],
                'encoding'  => 'utf-8',
            ));
            if (isset($data['summary']) 
                && !$summaryLength->isValid($data['summary'])
            ) {
                $result['status'] = self::RESULT_FALSE;
                $result['message']['summary'] = $summaryLength->getMessages();
            }
        }

        // Validate category
        if (in_array('category', $elements) and empty($data['category'])) {
            $result['status'] = self::RESULT_FALSE;
            $result['message']['category'] = array(
                'isEmpty' => __('Category cannot be empty.')
            );
        }

        return $result;
    }

    /**
     * Publish a article, the status of article will be changed to pendding
     * 
     * @param int  $id  Article ID
     * @return array 
     */
    protected function publish($id)
    {
        $result = array(
            'status'    => self::RESULT_FALSE,
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

        $result['status']   = self::RESULT_TRUE;
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
     * @param array  $elements  Form elements to display
     * @return array
     */
    protected function approve($id, $elements = array())
    {
        if (!$id) {
            return array('message' => __('Not enough parameter.'));
        }
        
        $model  = $this->getModel('draft');
        $row    = $model->findRow($id, 'id', false);
        if (!$row->id or $row->status != DraftModel::FIELD_STATUS_PENDING) {
            return array('message' => __('Invalid draft.'));
        }
        
        $result = $this->validateForm((array) $row, null, $elements);
        if (!$result['status']) {
            return $result;
        }
        
        $result = array(
            'status'    => self::RESULT_FALSE,
            'message'   => array(),
            'data'      => array(),
        );
        
        $module         = $this->getModule();
        $modelArticle   = $this->getModel('article');
        
        // move draft to article
        $timestamp = time();
        $article = array(
            'subject'      => $row->subject,
            'subtitle'     => $row->subtitle ?: '',
            'summary'      => $row->summary,
            'content'      => $row->content,
            'markup'       => $row->markup,
            'uid'          => $row->uid,
            'author'       => $row->author,
            'source'       => $row->source ?: '',
            'pages'        => $row->pages,
            'category'     => $row->category,
            'status'       => Article::FIELD_STATUS_PUBLISHED,
            'active'       => 1,
            'time_submit'  => $row->time_submit,
            'time_publish' => $row->time_publish ?: $timestamp,
            'time_update'  => $row->time_update ?: $timestamp,
            'image'        => $row->image ?: '',
        );
        $rowArticle = $modelArticle->createRow($article);
        $rowArticle->save();
        $articleId = $rowArticle->id;
        
        // Move extended fields to extended table
        $modelExtended  = $this->getModel('extended');
        $columns        = $modelExtended->getValidColumns();
        $extended       = array();
        foreach ($columns as $column) {
            $extended[$column] = $row->$column ?: '';
        }
        $extended['article'] = $articleId;
        $rowExtended = $modelExtended->createRow($extended);
        $rowExtended->save();
        
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

        // Move asset
        $modelDraftAsset     = $this->getModel('asset_draft');
        $resultsetDraftAsset = $modelDraftAsset->select(array(
            'draft' => $id,
        ));
        $modelAsset = $this->getModel('asset');
        foreach ($resultsetDraftAsset as $asset) {
            $data = array(
                'media'         => $asset->media,
                'type'          => $asset->type,
                'article'       => $articleId,
            );
            $rowAsset = $modelAsset->createRow($data);
            $rowAsset->save();
        }

        // Clear draft assets info
        $modelDraftAsset->delete(array('draft' => $id));

        // Save tag
        if ($this->config('enable_tag') && !empty($row->tag)) {
            Pi::service('tag')->add(
                $module,
                $articleId,
                '',
                $row->tag
            );
        }

        // Save related articles
        $relatedArticles = $row->related;
        if ($relatedArticles) {
            $relatedModel = $this->getModel('related');
            $relatedModel->saveRelated($articleId, $relatedArticles);
        }

        // delete draft
        $model->delete(array('id' => $id));

        $result['status']   = self::RESULT_TRUE;
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
            'status'    => self::RESULT_FALSE,
            'message'   => array(),
            'data'      => array(),
        );

        if (!$id) {
            $result['message'] = __('Not enough parameter.');
        }
        
        $modelDraft = $this->getModel('draft');
        $rowDraft   = $modelDraft->findRow($id, 'id', false);

        if (!$rowDraft->id or !$rowDraft->article) {
            $result['message'] = __('Invalid draft.');
        }
        
        $module         = $this->getModule();
        $modelArticle   = $this->getModel('article');

        // Update draft to article
        $articleId = $rowDraft->article;
        $timestamp = time();

        $article = array(
            'subject'      => $rowDraft->subject,
            'subtitle'     => $rowDraft->subtitle,
            'summary'      => $rowDraft->summary,
            'content'      => $rowDraft->content,
            'uid'          => $rowDraft->uid,
            'author'       => $rowDraft->author,
            'source'       => $rowDraft->source,
            'pages'        => $rowDraft->pages,
            'time_submit'  => $rowDraft->time_submit,
            'time_publish' => $rowDraft->time_publish,
            'time_update'  => $rowDraft->time_update > $timestamp 
                ? $rowDraft->time_update : $timestamp,
            'user_update'  => Pi::user()->getId(),
        );
        $rowArticle = $modelArticle->find($articleId);
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
            $rowCompiled     = $modelCompiled->createRow($compiled);
            $rowCompiled->save();
        }
        
        // Update value of extended fields
        $extended = array();
        $modelExtended = $this->getModel('extended');
        $columns       = $modelExtended->getValidColumns();
        foreach ($columns as $column) {
            $extended[$column] = $rowDraft->$column;
        }
        $extended['article'] = $articleId;
        $rowExtended   = $modelExtended->find($articleId, 'article');
        if (!$rowExtended) {
            $rowExtended = $modelExtended->createRow($extended);
        } else {
            $rowExtended->assign($extended);
        }
        $rowExtended->save();

        // Move feature image
        if (strcmp($rowDraft->image, $rowArticle->image) != 0) {
            if ($rowArticle->image) {
                $image = Pi::path($rowArticle->image);

                unlink($image);
                unlink(Media::getThumbFromOriginal($image));
            }

            $rowArticle->image = $rowDraft->image;
            $rowArticle->save();
        }

        // Update assets
        $modelAsset          = $this->getModel('asset');
        $modelAsset->delete(array('article' => $articleId));
        $modelDraftAsset     = $this->getModel('asset_draft');
        $resultsetDraftAsset = $modelDraftAsset->select(array(
            'draft' => $id,
        ));
        foreach ($resultsetDraftAsset as $asset) {
            $data = array(
                'media'     => $asset->media,
                'type'      => $asset->type,
                'article'   => $articleId,
            );
            $rowAsset = $modelAsset->createRow($data);
            $rowAsset->save();
        }
        
        // Clear draft assets
        $modelDraftAsset->delete(array('draft' => $id));

        // Save tag
        if ($this->config('enable_tag')) {
            Pi::service('tag')->update(
                $module,
                $articleId,
                '',
                $rowDraft->tag
            );
        }

        // Save related articles
        $relatedArticles = $rowDraft->related;
        if ($relatedArticles) {
            $modelRelated = $this->getModel('related');
            $modelRelated->saveRelated($articleId, $relatedArticles);
        }

        // Delete draft
        $modelDraft->delete(array('id' => $rowDraft->id));

        $result['status']   = self::RESULT_TRUE;
        $result['data']['redirect'] = $this->url(
            '',
            array('action' => 'published', 'controller' => 'article')
        );
        $result['message']= __('Article update successfully.');

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
        $rowDraft   = $id = $fakeId = null;
        $module     = $this->getModule();
        $modelDraft = $this->getModel('draft');

        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
        }

        $fakeId = $this->params('fake_id', 0);
        
        unset($data['article']);
        unset($data['image']);

        if ($this->config('enable_summary') && !$data['summary']) {
            $data['summary'] = Draft::generateArticleSummary(
                $data['content'], 
                $this->config('max_summary_length')
            );
        }

        $pages = Draft::breakPage($data['content']);
        $data['pages'] = count($pages);

        $data['time_publish'] = $data['time_publish'] 
            ? strtotime($data['time_publish']) : 0;
        $data['time_update']  = $data['time_update'] 
            ? strtotime($data['time_update']) : 0;
        $data['time_submit']  = $data['time_submit'] ? $data['time_submit'] : 0;
        $data['time_save']    = time();
        $data['author']       = (int) $data['author'];
        
        if (isset($data['related'])) {
            $data['related'] = array_filter(
                explode(self::TAG_DELIMITER, $data['related'])
            );
        }
        
        // Added current logged user as submitter if there is no submitter
        $data['uid'] = $data['uid'] ?: Pi::user()->getId();

        if (empty($id)) {
            $data['uid']    = Pi::user()->getId();
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
        
        // Save image
        $session = Media::getUploadSession($module, 'feature');
        if (isset($session->$id)
            || ($fakeId && isset($session->$fakeId))
        ) {
            $uploadInfo = isset($session->$id) 
                ? $session->$id : $session->$fakeId;

            if ($uploadInfo) {
                $data['image'] = $uploadInfo['tmp_name'];
                $this->getModel('draft')->updateRow(
                    $data,
                    array('id' => $rowDraft->id)
                );
            }

            unset($session->$id);
            unset($session->$fakeId);
        }

        // Update assets linked to fake id
        if ($fakeId) {
            $this->getModel('asset_draft')->update(
                array('draft' => $rowDraft->id),
                array('draft' => $fakeId)
            );
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

        $where['status']        = $status;
        $where['article < ?']   = 1;
        if ('my' == $from) {
            $where['uid']       = Pi::user()->getId();
        }
        if (isset($options['keyword'])) {
            $where['subject like ?'] = sprintf('%%%s%%', $options['keyword']);
        }

        $module         = $this->getModule();
        $modelDraft     = $this->getModel('draft');

        $resultsetDraft = Draft::getDraftPage($where, $page, $limit);

        // Total count
        $totalCount = (int) $modelDraft->getSearchRowsCount($where);
        $action     = $this->getEvent()->getRouteMatch()->getParam('action');

        // Paginator
        $paginator = Paginator::factory($totalCount, array(
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => array(
                'page_param'    => 'p',
                'params'        => array(
                    'module'        => $module,
                    'controller'    => 'draft',
                    'action'        => $action,
                    'status'        => $status,
                    'from'          => $from,
                    'where'         => urlencode(json_encode($options)),
                    'limit'         => $limit,
                ),
            ),
        ));

        $this->view()->assign(array(
            'data'      => $resultsetDraft,
            'paginator' => $paginator,
            'status'    => $status,
            'from'      => $from,
            'page'      => $page,
            'limit'     => $limit,
        ));
    }
    
    /**
     * Get a empty template of a draft
     * 
     * @return array 
     */
    protected function getDraftEmptyTemplate()
    {
        $columns = DraftModel::getAvailableFields();
        
        $template = array();
        foreach ($columns as $column) {
            $template[$column] = null;
        }
        
        return $template;
    }


    /**
     * Save article to draft.
     * 
     * @return ViewModel 
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
            'status'    => self::RESULT_FALSE,
            'message'   => array(),
            'data'      => array(),
        );

        $options = Setup::getFormConfig();
        $form    = $this->getDraftForm('save', $options);
        $form->setData($this->request->getPost());
        $form->setInputFilter(new DraftEditFilter($options));
        $form->setValidationGroup(DraftModel::getValidFields());
        
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
        
        $result['status']   = self::RESULT_TRUE;
        $result['data']     = array('id' => $id);

        //$module = $this->getModule();
        $route = 'article';
        $result['data']['preview_url'] = $this->url(
            $route,
            array(
                'module'    => $this->getModule(),
                'time'      => date('Ymd', time()),
                'id'        => $id,
                'slug'      => $data['slug'],
                'preview'   => 1,
            )
        );
        $result['message'] = __('Draft saved successfully.');

        return $result;
    }

    /**
     * Default action. 
     */
    public function indexAction()
    {
        // @todo use transaction
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
        if ($denied) {
            return $this->jumpToDenied();
        }
        
        $options = Setup::getFormConfig();
        $form    = $this->getDraftForm('add', $options);
        $categories = $form->get('category')->getValueOptions();
        $form->get('category')->setValueOptions(
                array_intersect_key($categories, $listCategory)
        );
        
        $form->setData(array(
            'category'      => $this->config('default_category'),
            'source'        => $this->config('default_source'),
            'fake_id'       => uniqid(),
            'uid'           => Pi::user()->getId(),
        ));

        $this->setModuleConfig();
        $this->view()->assign(array(
            'title'    => __('Create Article'),
            'form'     => $form,
            'config'   => Pi::config('', $this->getModule()),
            'elements' => $options['elements'],
            'rules'    => $rules,
            'approve'  => $approve,
            'delete'   => $delete,
            'status'   => DraftModel::FIELD_STATUS_DRAFT,
            'draft'    => $this->getDraftEmptyTemplate(),
            'currentDelete' => true,
        ));
        $this->view()->setTemplate('draft-edit', $this->getModule(), 'front');
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
        $options  = Setup::getFormConfig();
        $elements = $options['elements'];

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
        if (!(isset($rules[$row->category][$status . '-edit']) 
            and $rules[$row->category][$status . '-edit'])
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
        $data                 = (array) $row;
        $data['category']     = $data['category'] ?: $this->config('default_category');
        $data['related']      = $data['related'] ? implode(self::TAG_DELIMITER, $data['related']) : '';
        $data['time_publish'] = $data['time_publish'] ? _date($data['time_publish']) : '';
        $data['time_update']  = $data['time_update'] ? _date($data['time_update']) : '';
        
        $featureImage = $data['image'] ? Pi::url($data['image']) : '';
        $featureThumb = $data['image'] ? Pi::url(Media::getThumbFromOriginal($data['image'])) : '';

        $form = $this->getDraftForm('edit', $options);
        $allCategory = $form->get('category')->getValueOptions();
        $form->get('category')->setValueOptions(
            array_intersect_key($allCategory, $categories)
        );
        $form->setData($data);

        // Get author info
        if (in_array('author', $elements) and $data['author']) {
            $author = $this->getModel('author')->find($data['author']);
            if ($author) {
                $this->view()->assign('author', array(
                    'id'   => $author->id,
                    'name' => $author->name,
                ));
            }
        }

        // Get submitter info
        $columns = array('id', 'name');
        if ($data['uid']) {
            $user = Pi::user()->get($data['uid'], $columns);
            if ($user) {
                $this->view()->assign('user', array(
                    'id'   => $user['id'],
                    'name' => $user['name'],
                ));
            }
        }
        
        // Get update user info
        if ($data['user_update']) {
            $userUpdate = Pi::user()->get($data['user_update'], $columns);
            if ($userUpdate) {
                $this->view()->assign('userUpdate', array(
                    'id'   => $userUpdate['id'],
                    'name' => $userUpdate['name'],
                ));
            }
        }

        // Get related articles
        if (in_array('related', $elements)) {
            $related = $relatedIds = array();
            if (!empty($row->related)) {
                $relatedIds = array_flip($row->related);

                $related = Entity::getArticlePage(
                    array('id' => $row->related), 
                    1
                );
                foreach ($related as $item) {
                    if (array_key_exists($item['id'], $relatedIds)) {
                        $relatedIds[$item['id']] = $item;
                    }
                }

                $related = array_filter($relatedIds, function($var) {
                    return is_array($var);
                });
            }
        }

        // Get assets
        $attachments = $images = array();
        $resultsetDraftAsset = $this->getModel('asset_draft')->select(array(
            'draft' => $id,
        ))->toArray();
        // Getting media ID
        $mediaIds = array(0);
        foreach ($resultsetDraftAsset as $asset) {
            $mediaIds[] = $asset['media'];
        }
        // Getting media details
        $resultsetMedia = $this->getModel('media')
            ->select(array('id' => $mediaIds));
        $medias = array();
        foreach ($resultsetMedia as $media) {
            $medias[$media->id] = $media->toArray();
        }
        // Getting assets
        foreach ($resultsetDraftAsset as $asset) {
            $media = $medias[$asset['media']];
            if ('attachment' == $asset['type']) {
                $attachments[] = array(
                    'id'           => $asset['id'],
                    'title'        => $media['title'],
                    'size'         => $media['size'],
                    'deleteUrl'    => $this->url(
                        '',
                        array(
                            'controller' => 'draft',
                            'action'     => 'remove-asset',
                            'id'         => $asset['id'],
                        )
                    ),
                    'downloadUrl' => $this->url(
                        'admin',
                        array(
                            'controller' => 'media',
                            'action'     => 'download',
                            'id'         => $media['id'],
                        )
                    ),
                );
            } else {
                $imageSize = getimagesize(Pi::path($media['url']));

                $images[] = array(
                    'id'           => $asset['id'],
                    'title'        => $media['title'],
                    'size'         => $media['size'],
                    'w'            => $imageSize['0'],
                    'h'            => $imageSize['1'],
                    'downloadUrl'  => $this->url(
                        '',
                        array(
                            'controller' => 'media',
                            'action'     => 'download',
                            'id'         => $media['id'],
                        )
                    ),
                    'preview_url' => Pi::url($media['url']),
                    'thumb_url'   => Pi::url(Media::getThumbFromOriginal($media['url'])),
                );
            }
        }

        $this->setModuleConfig();
        $this->view()->assign(array(
            'title'          => __('Edit Article'),
            'form'           => $form,
            'draft'          => (array) $row,
            'related'        => $related,
            'attachments'    => $attachments,
            'images'         => $images,
            'featureImage'   => $featureImage,
            'featureThumb'   => $featureThumb,
            'config'         => Pi::config('', $module),
            'from'           => $this->params('from', ''),
            'elements'       => $elements,
            'status'         => $row->article ? Article::FIELD_STATUS_PUBLISHED : $row->status,
            'rules'          => $rules,
            'approve'        => $approve,
            'delete'         => $delete,
            'currentDelete'  => $currentDelete,
            'currentApprove' => $currentApprove,
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
            $slug     = Draft::getStatusSlug($row->status);
            $resource = $slug . '-delete';
            if (!(isset($rules[$row->category][$resource]) 
                and $rules[$row->category][$resource])
            ) {
                return $this->jumpToDenied();
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
     * Publish a draft
     * 
     * @return ViewModel
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

        $options = Setup::getFormConfig();
        $form    = $this->getDraftForm('save', $options);
        $form->setInputFilter(new DraftEditFilter($options));
        $form->setValidationGroup(DraftModel::getValidFields());
        $form->setData($this->request->getPost());

        if (!$form->isValid()) {
            return array('message' => $form->getMessages());
        }
        
        $data     = $form->getData();
        $validate = $this->validateForm($data, null, $options['elements']);

        if (!$validate['status']) {
            $form->setMessages($validate['message']);
            return $validate;
        }
        
        $id = $this->saveDraft($data);
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
            'status'    => self::RESULT_TRUE,
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

        $result['status']   = self::RESULT_TRUE;
        $result['data']['redirect'] = $this->url(
            '',
            array('action'=>'list', 'controller' => 'draft')
        );

        return $result;
    }

    /**
     * Approve a draft.
     * 
     * @return ViewModel 
     */
    public function approveAction()
    {
        // Denied user viewing if no front-end management permission assigned
        if (!$this->config('enable_front_edit') && 'front' == $this->section) {
            return $this->jumpTo404();
        }
        
        $options = Setup::getFormConfig();
        $form    = $this->getDraftForm('save', $options);
        $form->setInputFilter(new DraftEditFilter($options));
        $form->setValidationGroup(DraftModel::getValidFields());
        $form->setData($this->request->getPost());

        if (!$form->isValid()) {
            return array('message' => $form->getMessages());
        }
        
        $data     = $form->getData();
        $validate = $this->validateForm($data, null, $options['elements']);

        if (!$validate['status']) {
            $form->setMessages($validate['message']);
            return $validate;
        }
        
        $id = $this->saveDraft($data);

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
        
        $result = $this->approve($id, $options['elements']);
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

        $options = Setup::getFormConfig();
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
                $this->approve($id, $options['elements']);
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
        $slug     = $this->params('slug', '');
        $page     = $this->params('p', 1);
        $remain   = $this->params('r', '');
        
        if ('' !== $remain) {
            $this->view()->assign('remain', $remain);
        }

        $time    = time();
        $details = Draft::getDraft($id);
        $details['time_publish'] = $time;
        $module = $this->getModule();
        $params = array(
            'module'    => $module,
            'preview'   => 1
        );
        
        if (!$id) {
            return $this->jumpTo404(__('Page not found'));
        }
        if (strval($slug) != $details['slug']) {
            $routeParams = array(
                'time'      => $time,
                'id'        => $id,
                'slug'      => $details['slug'],
                'p'         => $page,
            );
            if ($remain) {
                $params['r'] = $remain;
            }
            return $this->redirect()->setStatusCode(301)->toRoute(
                '',
                array_merge($routeParams, $params)
            );
        }
        
        $route = 'article';
        foreach ($details['content'] as &$value) {
            $value['url'] = $this->url($route, array_merge(array(
                'time'      => date('Ymd', $time),
                'id'        => $id,
                'slug'      => $slug,
                'p'         => $value['page'],
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
            'time'        => date('Ymd', $time),
            'id'          => $id,
            'slug'        => $slug,
            'r'           => 0,
        ), $params));
        $details['remain'] = $this->url($route, array_merge(array(
            'time'        => date('Ymd', $time),
            'id'          => $id,
            'slug'        => $slug,
            'r'           => $page,
        ), $params));

        $module = $this->getModule();
        $this->view()->assign(array(
            'details'     => $details,
            'page'        => $page,
            'showTitle'   => isset($showTitle) ? $showTitle : null,
            'config'      => Pi::config('', $module),
        ));

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
        
        $options = Setup::getFormConfig();
        $form    = $this->getDraftForm('save', $options);
        $form->setInputFilter(new DraftEditFilter($options));
        $form->setValidationGroup(DraftModel::getValidFields());
        $form->setData($this->request->getPost());

        if (!$form->isValid()) {
            return array('message' => $form->getMessages());
        }
        
        $data     = $form->getData();
        $validate = $this->validateForm(
            $data,
            $this->params('article', 0),
            $options['elements']
        );

        if (!$validate['status']) {
            $form->setMessages($validate['message']);
            return $validate;
        }
        
        $id = $this->saveDraft($data);
        if (!$id) {
            return array('message', __('Failed to save draft.'));
        }
        $result = $this->update($id);

        return $result;
    }
    
    /**
     * Save image by AJAX, but do not save data into database.
     * If the image is fetched by upload, try to receive image by Upload class,
     * if it comes from media, try to copy the image from media to feature path.
     * Finally the image data will be saved into session.
     * 
     */
    public function saveImageAction()
    {
        Pi::service('log')->mute();
        $module  = $this->getModule();

        $return  = array('status' => false);
        $mediaId = $this->params('media_id', 0);
        $id      = $this->params('id', 0);
        $fakeId  = $this->params('fake_id', 0);
        
        // Checking is id valid
        if (empty($id) && empty($fakeId)) {
            $return['message'] = __('Invalid ID!');
            echo json_encode($return);
            exit;
        }
        $rename  = $id ?: $fakeId;
        
        $extensions = array_filter(explode(',', $this->config('image_extension')));
        foreach ($extensions as &$ext) {
            $ext = strtolower(trim($ext));
        }
        
        // Get distination path
        $destination = Media::getTargetDir('feature', $module, true);
        
        $rowMedia = $this->getModel('media')->find($mediaId);
        // Checking is media exists
        if (!$rowMedia->id or !$rowMedia->url) {
            $return['message'] = __('Media is not exists!');
            echo json_encode($return);
            exit;
        }
        // Checking is media an image
        if (!in_array(strtolower($rowMedia->type), $extensions)) {
            $return['message'] = __('Invalid file extension!');
            echo json_encode($return);
            exit;
        }

        $ext = strtolower(pathinfo($rowMedia->url, PATHINFO_EXTENSION));
        $rename     .= '.' . $ext;
        $fileName    = rtrim($destination, '/') . '/' . $rename;
        if (!copy(Pi::path($rowMedia->url), Pi::path($fileName))) {
            $return['message'] = __('Can not create image file!');
            echo json_encode($return);
            exit;
        }

        // Scale image
        $uploadInfo['tmp_name'] = $fileName;
        $uploadInfo['w']        = $this->config('feature_width');
        $uploadInfo['h']        = $this->config('feature_height');
        $uploadInfo['thumb_w']  = $this->config('feature_thumb_width');
        $uploadInfo['thumb_h']  = $this->config('feature_thumb_height');

        Media::saveImage($uploadInfo);

        // Save image to draft
        $rowDraft = $this->getModel('draft')->find($id);
        if ($rowDraft) {
            $rowDraft->image = $fileName;
            $rowDraft->save();
        } else {
            // Or save info to session
            $session = Media::getUploadSession($module, 'feature');
            $session->$fakeId = $uploadInfo;
        }
        
        $imageSize    = getimagesize(Pi::path($fileName));
        $originalName = isset($rawInfo['name']) ? $rawInfo['name'] : $rename;

        // Prepare return data
        $return['data'] = array(
            'originalName' => $originalName,
            'size'         => filesize(Pi::path($fileName)),
            'w'            => $imageSize['0'],
            'h'            => $imageSize['1'],
            'preview_url'  => Pi::url(Media::getThumbFromOriginal($fileName)),
        );
        $return['status'] = true;
        echo json_encode($return);
        exit();
    }
    
    /**
     * Remove image
     * 
     * @return ViewModel
     */
    public function removeImageAction()
    {
        Pi::service('log')->mute();
        $id           = $this->params('id', 0);
        $fakeId       = $this->params('fake_id', 0);
        $affectedRows = 0;
        $module       = $this->getModule();
        
        if ($id) {
            $rowDraft = $this->getModel('draft')->find($id);

            if ($rowDraft && $rowDraft->image) {

                $thumbUrl = Media::getThumbFromOriginal($rowDraft->image);
                if ($rowDraft->article) {
                    $modelArticle = $this->getModel('article');
                    $rowArticle   = $modelArticle->find($rowDraft->article);
                    if ($rowArticle && $rowArticle->image != $rowDraft->image) {
                        // Delete file
                        @unlink(Pi::path($rowDraft->image));
                        @unlink(Pi::path($thumbUrl));
                    }
                } else {
                    @unlink(Pi::path($rowDraft->image));
                    @unlink(Pi::path($thumbUrl));
                }

                // Update db
                $rowDraft->image = '';
                $affectedRows    = $rowDraft->save();
            }
        } else if ($fakeId) {
            $session = Media::getUploadSession($module, 'feature');

            if (isset($session->$fakeId)) {
                $uploadInfo = $session->$fakeId;

                $url = Media::getThumbFromOriginal($uploadInfo['tmp_name']);
                $affectedRows = unlink(Pi::path($uploadInfo['tmp_name']));
                @unlink(Pi::path($url));

                unset($session->$id);
                unset($session->$fakeId);
            }
        }

        echo json_encode(array(
            'status'    => $affectedRows ? true : false,
            'message'   => 'ok',
        ));
        exit;
    }
    
    /**
     * Save asset
     */
    public function saveAssetAction()
    {
        Pi::service('log')->mute();
        
        $type    = $this->params('type', 'attachment');
        $mediaId = $this->params('media', 0);
        $draftId = $this->params('id', 0);
        if (empty($draftId)) {
            $draftId = $this->params('fake_id', 0);
        }
        
        $return = array('status' => false);
        // Checking if is draft exists
        if (empty($draftId)) {
            $return['message'] = __('Invalid draft ID!');
            echo json_encode($return);
            exit();
        }
        
        // Checking if is media exists
        $rowMedia = $this->getModel('media')->find($mediaId);
        if (empty($rowMedia)) {
            $return['message'] = __('Invalid media!');
            echo json_encode($return);
            exit();
        }
        
        // Saving asset data
        $model     = $this->getModel('asset_draft');
        $rowAssets = $model->select(array(
            'draft' => $draftId,
            'media' => $mediaId
        ));
        if ($rowAssets->count() == 0) {
            $data     = array(
                'draft'   => $draftId,
                'media'   => $mediaId,
                'type'    => $type,
            );
            $row = $model->createRow($data);
            $row->save();
            
            if (!$row->id) {
                $return['message'] = __('Can not save data!');
                echo json_encode($return);
                exit();
            }
        }
        
        $return['data']    = array(
            'media'       => $mediaId,
            'id'          => $row->id,
            'title'       => $rowMedia->title,
            'size'        => $rowMedia->size,
            'downloadUrl' => $this->url('default', array(
                'controller' => 'media',
                'action'     => 'download',
                'id'         => $mediaId,
            )),
        );
        // Generating a preview url for image file
        if ('image' == $type) {
            $return['data']['preview_url'] = Pi::url($rowMedia->url);
        }
        $return['status']  = true;
        $return['message'] = __('Save asset successful!');
        echo json_encode($return);
        exit();
    }
    
    /**
     * Delete asset
     */
    public function removeAssetAction()
    {
        Pi::service('log')->mute();
        
        $return = array('status' => false);
        $id = $this->params('id', 0);
        if (empty($id)) {
            $return['message'] = __('Invalid ID!');
            echo json_encode($return);
            exit();
        }
        
        $result = $this->getModel('asset_draft')->delete(array('id' => $id));
        if ($result) {
            $return['message'] = __('Delete item sucessful!');
            $return['status']  = true;
        } else {
            $return['message'] = __('Can not delete item!');
        }
        
        echo json_encode($return);
        exit();
    }
}
