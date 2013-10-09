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
use Module\Article\Form\TopicEditForm;
use Module\Article\Form\TopicEditFilter;
use Module\Article\Form\SimpleSearchForm;
use Module\Article\Model\Topic;
use Zend\Db\Sql\Expression;
use Module\Article\Service;
use Module\Article\Model\Article;
use Module\Article\Entity;
use Module\Article\Topic as TopicService;
use Pi\File\Transfer\Upload as UploadHandler;

/**
 * Topic controller
 * 
 * Feature list:
 * 
 * 1. List\add\edit\delete a topic
 * 2. Pull\remove article to\from topic
 * 3. All topic list
 * 4. AJAX action used to save or remove a topic image
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class TopicController extends ActionController
{
    /**
     * Custom template path 
     */
    const TEMPLATE_PATH = 'article/template/front';
    
    /**
     * Custom template format 
     */
    const TEMPLATE_FORMAT = '/^topic-custom-(.+)/';
    
    /**
     * Get topic form object
     * 
     * @param string $action  Form name
     * @return \Module\Article\Form\TopicEditForm 
     */
    protected function getTopicForm($action = 'add')
    {
        $form = new TopicEditForm();
        $form->setAttributes(array(
            'action'  => $this->url('', array('action' => $action)),
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
            'class'   => 'form-horizontal',
        ));

        return $form;
    }

    /**
     * Save topic information
     * 
     * @param  array    $data  Topic information
     * @return boolean
     * @throws \Exception 
     */
    protected function saveTopic($data)
    {
        $module     = $this->getModule();
        $modelTopic = $this->getModel('topic');
        $fakeId     = $image = null;

        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
        }
        //$data['active'] = 1;

        $fakeId = Service::getParam($this, 'fake_id', 0);

        unset($data['image']);
        
        if (isset($data['slug']) && empty($data['slug'])) {
            unset($data['slug']);
        }

        if (empty($id)) {
            $rowTopic = $modelTopic->createRow($data);
            $rowTopic->save();
            $id       = $rowTopic->id;
        } else {
            $rowTopic = $modelTopic->find($id);

            if (empty($rowTopic->id)) {
                return false;
            }

            $rowTopic->assign($data);
            $rowTopic->save();
        }

        // Save image
        $session    = Service::getUploadSession($module, 'topic');
        if (isset($session->$id) 
            || ($fakeId && isset($session->$fakeId))
        ) {
            $uploadInfo = isset($session->$id)
                ? $session->$id : $session->$fakeId;

            if ($uploadInfo) {
                $fileName = $rowTopic->id;

                $pathInfo = pathinfo($uploadInfo['tmp_name']);
                if ($pathInfo['extension']) {
                    $fileName .= '.' . $pathInfo['extension'];
                }
                $fileName = $pathInfo['dirname'] . '/' . $fileName;

                $rowTopic->image = rename(
                    Pi::path($uploadInfo['tmp_name']),
                    Pi::path($fileName)
                ) ? $fileName : $uploadInfo['tmp_name'];
                $rowTopic->save();
            }

            unset($session->$id);
            unset($session->$fakeId);
        }

        return $id;
    }
    
    /**
     * Get screenshot image of a template, if the image is not added by user
     * a default image will be used according to configuration
     * 
     * @param string  $name
     * @return string 
     */
    protected function getScreenshot($name)
    {
        $module = $this->getModule();
        $path = sprintf(
            '%s/%s/topic-template/%s.png',
            Pi::path('static'),
            $module,
            $name
        );
        if (file_exists($path)) {
            $url = sprintf(
                '%s/%s/topic-template/%s.png',
                Pi::url('static'),
                $module,
                $name
            );
        } else {
            $url = sprintf(
                '%s/module-%s/%s',
                Pi::url('asset'),
                $module,
                $this->config('default_topic_template_image')
            );
        }
        
        return $url;
    }
    
    /**
     * List articles of a topic for management
     */
    public function listArticleAction()
    {
        $modelTopic = $this->getModel('topic');

        $topic      = Service::getParam($this, 'topic', '');
        $page       = Service::getParam($this, 'p', 1);
        $page       = $page > 0 ? $page : 1;

        $module = $this->getModule();
        $config = Pi::service('module')->config('', $module);
        $limit  = (int) $config['page_limit_management'] ?: 20;
        $offset = ($page - 1) * $limit;
        $where  = array();
        
        if (!empty($topic)) {
            if (is_numeric($topic)) {
                $rowTopic = $this->getModel('topic')->find($topic);
            } else {
                $rowTopic = $this->getModel('topic')->find($topic, 'slug');
            }
            $where['topic'] = $rowTopic->id;
        }
        
        // Selecting articles
        $modelRelation = $this->getModel('article_topic');
        $select        = $modelRelation->select()
                                       ->where($where)
                                       ->offset($offset)
                                       ->limit($limit)
                                       ->order('time DESC');
        $rowArticleSet = $modelRelation->selectWith($select)->toArray();
        
        // Getting article details
        $articleIds = array(0);
        $userIds    = array(0);
        $pulls      = array();
        foreach ($rowArticleSet as $row) {
            $articleIds[] = $row['article'];
            $userIds[]    = $row['user_pull'];
            $pulls[$row['article']] = $row;
        }
        $articleIds    = empty($articleIds) ? 0 : $articleIds;
        $articles      = Entity::getArticlePage(
            array('id' => $articleIds),
            1,
            $limit
        );
        
        // Get users
        $users = Pi::user()->get($userIds, array('id', 'identity'));
        
        // Get topic details
        $rowTopicSet   = $modelTopic->select(array());
        $topics        = array();
        foreach ($rowTopicSet as $row) {
            $topics[$row['id']] = $row['title'];
        }
        
        // Get topic info
        $select     = $modelRelation->select()
            ->where($where)
            ->columns(array('count' => new Expression('count(id)')));
        $totalCount = (int) $modelRelation->selectWith($select)
            ->current()->count;

        // Pagination
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
                    'controller'    => 'topic',
                    'action'        => 'list-article',
                    'topic'         => $topic,
                )),
            ));

        $this->view()->assign(array(
            'title'         => $rowTopic->title,
            'articles'      => $rowArticleSet,
            'details'       => $articles,
            'topics'        => $topics,
            'topic'         => $topic,
            'paginator'     => $paginator,
            'config'        => $config,
            'action'        => 'list-article',
            'count'         => $totalCount,
            'pulls'         => $pulls,
            'users'         => $users,
        ));
    }
    
    /**
     * List all articles for pulling
     */
    public function pullAction()
    {
        // Fetch topic details
        $topic      = Service::getParam($this, 'topic', '');
        
        if (empty($topic)) {
            return $this->jumpTo404(__('Invalid topic ID!'));
        }
        
        if (is_numeric($topic)) {
            $rowTopic = $this->getModel('topic')->find($topic);
        } else {
            $rowTopic = $this->getModel('topic')->find($topic, 'slug');
        }
        
        $where  = array();
        $page   = Service::getParam($this, 'page', 1);
        $limit  = Service::getParam($this, 'limit', 20);

        $data   = $ids = array();

        $module         = $this->getModule();
        $modelArticle   = $this->getModel('article');
        $categoryModel  = $this->getModel('category');
        $modelRelation  = $this->getModel('article_topic');
        
        // Get topic articles
        $rowRelation = $modelRelation->select(array('topic' => $rowTopic->id));
        $topicArticles = array();
        foreach ($rowRelation as $row) {
            $topicArticles[] = $row['article'];
        }

        // Get category
        $category = Service::getParam($this, 'category', 0);
        if ($category > 1) {
            $categoryIds = $categoryModel->getDescendantIds($category);
            if ($categoryIds) {
                $where['category'] = $categoryIds;
            }
        }
        
        // Get topic
        $modelTopic = $this->getModel('topic');
        $topics     = $modelTopic->getList();

        // Build where
        $where['status'] = Article::FIELD_STATUS_PUBLISHED;
        $where['active'] = 1;
        
        $keyword = Service::getParam($this, 'keyword', '');
        if (!empty($keyword)) {
            $where['subject like ?'] = sprintf('%%%s%%', $keyword);
        }

        // Retrieve data
        $data = Entity::getArticlePage($where, $page, $limit);
        
        // Getting article topic
        $articleIds  = array_keys($data);
        if (empty($articleIds)) {
            $articleIds = array(0);
        }
        $rowRelation = $this->getModel('article_topic')
            ->select(array('article' => $articleIds));
        $relation    = array();
        foreach ($rowRelation as $row) {
            if (isset($relation[$row['article']])) {
                $relation[$row['article']] .= ',' . $topics[$row['topic']];
            } else {
                $relation[$row['article']] = $topics[$row['topic']];
            }
        }

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
                'controller'    => 'topic',
                'action'        => 'pull',
                'topic'         => $topic,
                'category'      => $category,
                'keyword'       => $keyword,
            )),
        ));

        // Prepare search form
        $form = new SimpleSearchForm;
        $form->setData($this->params()->fromQuery());
        
        $this->view()->assign(array(
            'title'      => __('All Articles'),
            'data'       => $data,
            'form'       => $form,
            'paginator'  => $paginator,
            'category'   => $category,
            'categories' => Service::getCategoryList(),
            'action'     => 'pull',
            'topics'     => $topics,
            'relation'   => $relation,
            'topic'      => $rowTopic->toArray(),
            'pulled'     => $topicArticles,
        ));
    }
    
    /**
     * Pull articles into topic
     * 
     * @return ViewModel 
     */
    public function pullArticleAction()
    {
        $topic = Service::getParam($this, 'topic', '');
        $id    = Service::getParam($this, 'id', 0);
        $ids   = array_filter(explode(',', $id));
        $from  = Service::getParam($this, 'from', '');
        if (empty($topic)) {
            return Service::jumpToErrorOperation(
                $this, 
                __('Target topic is needed!')
            );
        }
        if (empty($ids)) {
            return Service::jumpToErrorOperation(
                $this, 
                __('No articles are selected, please try again!')
            );
        }
        
        $data  = array();
        $time  = time();
        foreach ($ids as $value) {
            $data[$value] = array(
                'article'   => $value,
                'topic'     => $topic,
                'time'      => $time,
                'user_pull' => Pi::user()-id,
            );
        }
        
        $model = $this->getModel('article_topic');
        $rows  = $model->select(array('article' => $ids));
        foreach ($rows as $row) {
            if ($topic == $row->topic) {
                unset($data[$row->article]);
            }
        }
        
        foreach ($data as $item) {
            $row = $model->createRow($item);
            $row->save();
        }
        
        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            return $this->redirect()->toRoute(
                '',
                array('action' => 'list-article', 'message' => count($ids))
            );
        }
    }
    
    /**
     * Remove pulled articles from a topic
     * 
     * @return ViewModel 
     */
    public function removePullAction()
    {
        $id    = Service::getParam($this, 'id', 0);
        $ids   = array_filter(explode(',', $id));
        $from  = Service::getParam($this, 'from', '');
        if (empty($ids)) {
            return $this->jumpTo404('Invalid ID!');
        }
        
        $this->getModel('article_topic')->delete(array('id' => $ids));
                
        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            return $this->redirect()->toRoute(
                '',
                array('action' => 'list-article')
            );
        }
    }
    
    /**
     * Add topic information
     * 
     * @return ViewModel 
     */
    public function addAction()
    {
        $form   = $this->getTopicForm('add');
        $form->setData(array('fake_id'  => uniqid()));

        Service::setModuleConfig($this);
        $this->view()->assign(array(
            'title'   => __('Add Topic Info'),
            'form'    => $form,
            'module'  => $this->getModule(),
            'url'     => $this->getScreenshot('default'),
        ));
        $this->view()->setTemplate('topic-edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $this->view()->assign('url', $this->getScreenshot($post['template']));
            $form->setInputFilter(new TopicEditFilter);
            $form->setValidationGroup(Topic::getAvailableFields());
            if (!$form->isValid()) {
                return Service::renderForm(
                    $this,
                    $form,
                    __('There are some error occured!')
                );
            }
            
            $data = $form->getData();
            $data['time_create'] = time();
            $id   = $this->saveTopic($data);
            if (!$id) {
                return Service::renderForm(
                    $this,
                    $form,
                    __('Can not save data!')
                );
            }
            return $this->redirect()->toRoute(
                '',
                array('action' => 'list-topic')
            );
        }
    }

    /**
     * Edit topic information
     * 
     * @return ViewModel
     */
    public function editAction()
    {
        Service::setModuleConfig($this);
        $this->view()->assign('title', __('Edit Topic Info'));
        $this->view()->assign('module', $this->getModule());
        
        $form = $this->getTopicForm('edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $this->view()->assign('url', $this->getScreenshot($post['template']));
            $options = array(
                'id'   => $post['id'],
            );
            $form->setInputFilter(new TopicEditFilter($options));
            $form->setValidationGroup(Topic::getAvailableFields());
            if (!$form->isValid()) {
                return Service::renderForm(
                    $this,
                    $form,
                    __('Can not update data!')
                );
            }
            $data = $form->getData();
            $id   = $this->saveTopic($data);

            return $this->redirect()->toRoute(
                '',
                array('action' => 'list-topic')
            );
        }
        
        $id     = $this->params('id', 0);
        if (empty($id)) {
            $this->jumpto404(__('Invalid topic ID!'));
        }

        $model = $this->getModel('topic');
        $row   = $model->find($id);
        if (!$row->id) {
            return $this->jumpTo404(__('Can not find topic!'));
        }
        
        $form->setData($row->toArray());

        $this->view()->assign('form', $form);
        $this->view()->assign('url', $this->getScreenshot($row->template));
    }
    
    /**
     * Delete topic
     * 
     * @return ViewModel 
     */
    public function deleteAction()
    {
        $id     = $this->params('id');
        if (empty($id)) {
            return $this->jumpTo404(__('Invalid topic ID!'));
        }

        $topicModel = $this->getModel('topic');

        // Remove relationship between topic and articles
        $this->getModel('article_topic')->delete(array('topic' => $id));

        // Delete image
        $row = $topicModel->find($id);
        if ($row && $row->image) {
            @unlink(Pi::path($row->image));
        }

        // Remove topic
        $topicModel->delete(array('id' => $id));

        // Go to list page
        return $this->redirect()->toRoute(
            '',
            array('action' => 'list-topic')
        );
    }

    /**
     * List all added topic for management
     */
    public function listTopicAction()
    {
        $module = $this->getModule();
        $config = Pi::service('module')->config('', $module);
        $limit  = (int) $config['page_limit_management'] ?: 20;
        $page   = Service::getParam($this, 'p', 1);
        $page   = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $limit;
        
        // Fetch topics
        $model  = $this->getModel('topic');
        $select = $model->select()
                        ->offset($offset)
                        ->limit($limit);
        $rowset = $model->selectWith($select)->toArray();
        
        $topicIds = array(0);
        foreach ($rowset as $row) {
            $topicIds[] = $row['id'];
        }
        
        // Fetch topic article count
        $modelCount = $this->getModel('article_topic');
        $select = $modelCount->select()
            ->where(array('topic' => $topicIds))
            ->columns(array('topic', 'count' => new Expression('count(*)')))
            ->group(array('topic'));
        $rowCount = $modelCount->selectWith($select);
        $count = array();
        foreach ($rowCount as $row) {
            $count[$row->topic] = $row->count;
        }
        
        // Get total topic count
        $select = $model->select()
            ->columns(array('count' => new Expression('count(*)')));
        $totalCount = (int) $model->selectWith($select)->current()->count;
        
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()
                ->getRouteMatch()
                ->getMatchedRouteName(),
            'params'        => array(
                'module'        => $this->getModule(),
                'controller'    => 'topic',
                'action'        => 'list-topic',
            ),
        ));

        $this->view()->assign(array(
            'title'   => __('Topic List'),
            'topics'  => $rowset,
            'action'  => 'list-topic',
            'route'   => Service::getRouteName($module),
            'count'   => $count,
        ));
    }

    /**
     * Active or deactivate a topic.
     * 
     * @return ViewModel 
     */
    public function activeAction()
    {
        $status = Service::getParam($this, 'status', 0);
        $id     = Service::getParam($this, 'id', 0);
        $from   = Service::getParam($this, 'from', 0);
        if (empty($id)) {
            return $this->jumpTo404(__('Invalid topic ID!'));
        }
        
        $this->getModel('topic')->setActiveStatus($id, $status);
        
        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            return $this->redirect()->toRoute(
                '',
                array('action' => 'list-topic')
            );
        }
    }
    
    /**
     * Saving image by AJAX, but do not save data into database.
     * If the image is fetched by upload, try to receive image by Upload class,
     * if it comes from media, try to copy the image from media to topic path.
     * Finally the image data will be saved into session.
     * 
     */
    public function saveImageAction()
    {
        Pi::service('log')->active(false);
        $module  = $this->getModule();

        $return  = array('status' => false);
        $mediaId = Service::getParam($this, 'media_id', 0);
        $id      = Service::getParam($this, 'id', 0);
        if (empty($id)) {
            $id = Service::getParam($this, 'fake_id', 0);
        }
        // Checking is ID exists
        if (empty($id)) {
            $return['message'] = __('Invalid ID!');
            echo json_encode($return);
            exit;
        }
        
        $extensions = array_filter(
            explode(',', $this->config('image_extension')));
        foreach ($extensions as &$ext) {
            $ext = strtolower(trim($ext));
        }
        
        // Get distination path
        $destination = Service::getTargetDir('topic', $module, true, false);

        if ($mediaId) {
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
            $rename      = $id . '.' . $ext;
            $fileName    = rtrim($destination, '/') . '/' . $rename;
            if (!copy(Pi::path($rowMedia->url), Pi::path($fileName))) {
                $return['message'] = __('Can not create image file!');
                echo json_encode($return);
                exit;
            }
        } else {
            $rawInfo = $this->request->getFiles('upload');

            $ext     = pathinfo($rawInfo['name'], PATHINFO_EXTENSION);
            $rename  = $id . '.' . $ext;

            $upload = new UploadHandler;
            $upload->setDestination(Pi::path($destination))
                   ->setRename($rename)
                   ->setExtension($this->config('image_extension'))
                   ->setSize($this->config('max_image_size'));

            // Checking is uploaded file valid
            if (!$upload->isValid()) {
                $return['message'] = $upload->getMessages();
                echo json_encode($return);
                exit;
            }
            
            $upload->receive();
            $fileName = $destination . '/' . $rename;
        }

        // Scale image
        $uploadInfo['tmp_name'] = $fileName;
        $uploadInfo['w']        = $this->config('topic_width');
        $uploadInfo['h']        = $this->config('topic_height');
        $uploadInfo['thumb_w']  = $this->config('topic_thumb_width');
        $uploadInfo['thumb_h']  = $this->config('topic_thumb_height');

        Service::saveImage($uploadInfo);

        // Save image to topic
        $rowTopic = $this->getModel('topic')->find($id);
        if ($rowTopic) {
            if ($rowTopic->image && $rowTopic->image != $fileName) {
                @unlink(Pi::path($rowTopic->image));
            }

            $rowTopic->image = $fileName;
            $rowTopic->save();
        } else {
            // Or save info to session
            $session = Service::getUploadSession($module, 'topic');
            $session->$id = $uploadInfo;
        }

        $imageSize = getimagesize(Pi::path($fileName));
        $originalName = isset($rawInfo['name']) ? $rawInfo['name'] : $rename;

        // Prepare return data
        $return['data'] = array(
            'originalName' => $originalName,
            'size'         => filesize(Pi::path($fileName)),
            'w'            => $imageSize['0'],
            'h'            => $imageSize['1'],
            'preview_url'  => Pi::url($fileName),
            'filename'     => $fileName,
        );

        $return['status'] = true;
        echo json_encode($return);
        exit();
    }
    
    /**
     * Removing image by AJAX.
     * This operation will also remove image data in database.
     * 
     * @return ViewModel 
     */
    public function removeImageAction()
    {
        Pi::service('log')->active(false);
        $id           = Service::getParam($this, 'id', 0);
        $fakeId       = Service::getParam($this, 'fake_id', 0);
        $affectedRows = 0;
        $module       = $this->getModule();

        if ($id) {
            $rowTopic = $this->getModel('topic')->find($id);

            if ($rowTopic && $rowTopic->image) {
                // Delete image
                @unlink(Pi::path($rowTopic->image));

                // Update db
                $rowTopic->image = '';
                $affectedRows    = $rowTopic->save();
            }
        } else if ($fakeId) {
            $session = Service::getUploadSession($module, 'topic');

            if (isset($session->$fakeId)) {
                $uploadInfo = isset($session->$id) 
                    ? $session->$id : $session->$fakeId;

                @unlink(Pi::path($uploadInfo['tmp_name']));

                unset($session->$id);
                unset($session->$fakeId);
            }
        }

        return array(
            'status'    => $affectedRows ? true : false,
            'message'   => 'ok',
        );
    }
    
    /**
     * Get all avaliable templates 
     */
    public function getTemplateAction()
    {
        Pi::service('log')->active(false);
        $return = array('status' => false);
        
        $limit  = (int) $this->params('limit', 1);
        $page   = (int) $this->params('page', 1);
        $offset = $limit * ($page - 1);
        
        $path      = sprintf(
            '%s/%s', 
            rtrim(Pi::path('module'), '/'), 
            self::TEMPLATE_PATH
        );
        $iterator  = new \DirectoryIterator($path);
        $templates = array('default' => 'Default');
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }
            $filename = $fileinfo->getFilename();
            $name     = substr($filename, 0, strrpos($filename, '.'));
            if (!preg_match(self::TEMPLATE_FORMAT, $name, $matches)) {
                continue;
            }
            $displayName = preg_replace('/[-_]/', ' ', $matches[1]);
            $templates[$matches[1]] = ucfirst($displayName);
        }
        asort($templates);
        
        $data  = array();
        $index = 0;
        $count = 0;
        foreach ($templates as $name => $title) {
            // Get template from the given offset
            if ($index++ < $offset) {
                continue;
            }
            
            // Get template
            $url = $this->getScreenshot($name);
            $fullSize = $this->url(
                '',
                array(
                    'action' => 'view-fullsize',
                    'name'   => $name
                )
            );
            $data[] = array(
                'name'      => $name,
                'title'     => $title,
                'url'       => $url,
                'fullsize'  => $fullSize,
            );
            
            // Select template according to limit
            if (++$count >= $limit) {
                break;
            }
        }
        $nextPage = ($offset + $limit) >= count($templates) ? 0 : $page + 1;
        
        $return = array(
            'status'    => true,
            'message'   => __('Success'),
            'data'      => $data,
            'previous'  => $page - 1,
            'next'      => $nextPage,
        );
        echo json_encode($return);
        exit;
    }
    
    /**
     * View the fullsize image of template screenshot
     * 
     * @return ViewModel 
     */
    public function viewFullsizeAction()
    {
        $name = $this->params('name', 0);
        if (empty($name)) {
            return Service::jumpToErrorOperation(
                $this,
                __('Invalid template name!')
            );
        }
        
        $url = $this->getScreenshot($name);
        
        header('Content-type: image/png');
        readfile($url);
        exit();
    }
}
