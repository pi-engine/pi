<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Module\Article\Form\TopicEditForm;
use Module\Article\Form\TopicEditFilter;
use Module\Article\Form\SimpleSearchForm;
use Zend\Db\Sql\Expression;
use Module\Article\Model\Article;
use Module\Article\Entity;

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
     * List articles of a topic for management
     */
    public function listArticleAction()
    {
        $model = $this->getModel('topic');

        $topic = $this->params('topic', '');
        $page  = $this->params('p', 1);
        $page  = $page > 0 ? $page : 1;

        $module = $this->getModule();
        $limit  = (int) $this->params('limit', 20);
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
        $users = Pi::user()->get($userIds, array('id', 'name'));
        
        // Get topic details
        $rowTopicSet   = $model->select(array());
        $topics        = array();
        foreach ($rowTopicSet as $row) {
            $topics[$row['id']] = $row['title'];
        }
        
        // Pagination
        $totalCount = $modelRelation->count($where);
        $paginator  = Paginator::factory($totalCount, array(
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => array(
                'page_param'    => 'p',
                'params'        => array_filter(array(
                    'module'        => $module,
                    'controller'    => 'topic',
                    'action'        => 'list-article',
                    'topic'         => $topic,
                )),
            ),
        ));

        $this->view()->assign(array(
            'title'         => $rowTopic->title,
            'articles'      => $rowArticleSet,
            'details'       => $articles,
            'topics'        => $topics,
            'topic'         => $topic,
            'paginator'     => $paginator,
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
        $topic      = $this->params('topic', '');
        
        if (empty($topic)) {
            return $this->jumpTo404(_a('Invalid topic ID!'));
        }
        
        if (is_numeric($topic)) {
            $rowTopic = $this->getModel('topic')->find($topic);
        } else {
            $rowTopic = $this->getModel('topic')->find($topic, 'slug');
        }
        
        $where  = array();
        $page   = $this->params('p', 1);
        $limit  = $this->params('limit', 20);

        $data   = $ids = array();

        $module         = $this->getModule();
        $categoryModel  = $this->getModel('category');
        $modelRelation  = $this->getModel('article_topic');
        
        // Get topic articles
        $rowRelation = $modelRelation->select(array('topic' => $rowTopic->id));
        $topicArticles = array();
        foreach ($rowRelation as $row) {
            $topicArticles[] = $row['article'];
        }

        // Get category
        $category = $this->params('category', 0);
        if ($category > 1) {
            $categoryIds = $categoryModel->getDescendantIds($category);
            if ($categoryIds) {
                $where['category'] = $categoryIds;
            }
        }
        
        // Get topic
        $model  = $this->getModel('topic');
        $topics = $model->getList();

        // Build where
        $where['status'] = Article::FIELD_STATUS_PUBLISHED;
        $where['active'] = 1;
        
        $keyword = $this->params('keyword', '');
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

        // Paginator
        $totalCount = (int) Entity::count($where);
        $paginator  = Paginator::factory($totalCount, array(
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => array(
                'page_param'    => 'p',
                'params'        => array_filter(array(
                    'topic'         => $topic,
                    'category'      => $category,
                    'keyword'       => $keyword,
                )),
            ),
        ));

        // Prepare search form
        $form = new SimpleSearchForm;
        $form->setData($this->params()->fromQuery());
        
        $count = $this->getModel('article_topic')
                      ->count(array('topic' => $rowTopic->id));
        
        $categories = Pi::api('category', $module)->getList(array('active' => 1));
        
        $this->view()->assign(array(
            'title'      => _a('All Articles'),
            'data'       => $data,
            'form'       => $form,
            'paginator'  => $paginator,
            'category'   => $category,
            'categories' => $categories,
            'action'     => 'pull',
            'topics'     => $topics,
            'relation'   => $relation,
            'topic'      => $rowTopic->toArray(),
            'pulled'     => $topicArticles,
            'count'      => $count,
        ));
    }
    
    /**
     * Pull articles into topic
     * 
     * @return ViewModel 
     */
    public function pullArticleAction()
    {
        $topic = $this->params('topic', '');
        $id    = $this->params('id', 0);
        $ids   = array_filter(explode(',', $id));
        $from  = $this->params('from', '');
        if (empty($topic)) {
            return $this->jumpTo404(_a('Target topic is needed!'));
        }
        if (empty($ids)) {
            return $this->jumpTo404( 
                _a('No articles are selected, please try again!')
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
        $id    = $this->params('id', 0);
        $ids   = array_filter(explode(',', $id));
        $from  = $this->params('from', '');
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
        $module  = $this->getModule();
        $configs = Pi::config('', $module);
        $configs['max_media_size'] = Pi::service('file')
            ->transformSize($configs['max_media_size']);
        
        $form = $this->getTopicForm('add');
        
        $this->view()->assign(array(
            'title'   => _a('Add Topic Info'),
            'form'    => $form,
            'module'  => $module,
            'url'     => $this->getScreenshot('default'),
            'configs' => $configs,
        ));
        $this->view()->setTemplate('topic-edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $this->view()->assign('url', $this->getScreenshot($post['template']));
            $form->setInputFilter(new TopicEditFilter);
            if (!$form->isValid()) {
                return $this->renderForm(
                    $form,
                    _a('There are some error occured!')
                );
            }
            
            $data = $form->getData();
            $data['time_create'] = time();
            $id   = $this->saveTopic($data);
            if (!$id) {
                return $this->renderForm(
                    $form,
                    _a('Can not save data!')
                );
            }
            return $this->redirect()->toRoute('', array(
                'action' => 'list-topic'
            ));
        }
    }

    /**
     * Edit topic information
     * 
     * @return ViewModel
     */
    public function editAction()
    {
        $module  = $this->getModule();
        $configs = Pi::config('', $module);
        $configs['max_media_size'] = Pi::service('file')
            ->transformSize($configs['max_media_size']);
        
        $this->view()->assign(array(
            'title'   => _a('Edit Topic Info'),
            'module'  => $module,
            'configs' => $configs,
        ));
        
        $form = $this->getTopicForm('edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $this->view()->assign('url', $this->getScreenshot($post['template']));
            $options = array(
                'id'   => $post['id'],
            );
            $form->setInputFilter(new TopicEditFilter($options));
            if (!$form->isValid()) {
                return $this->renderForm(
                    $form,
                    _a('Can not update data!')
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
            $this->jumpto404(_a('Invalid topic ID!'));
        }

        $model = $this->getModel('topic');
        $row   = $model->find($id);
        if (!$row->id) {
            return $this->jumpTo404(_a('Can not find topic!'));
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
            return $this->jumpTo404(_a('Invalid topic ID!'));
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
        $limit  = (int) $this->params('limit', 20);
        $page   = $this->params('page', 1);
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
        $totalCount = (int) $model->count();
        $paginator  = Paginator::factory($totalCount, array(
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => array(
                'params'        => array(
                    'module'        => $module,
                    'controller'    => 'topic',
                    'action'        => 'list-topic',
                ),
            ),
        ));

        $this->view()->assign(array(
            'title'     => _a('Topic List'),
            'topics'    => $rowset,
            'action'    => 'list-topic',
            'route'     => Pi::api('api', $module)->getRouteName(),
            'count'     => $count,
            'paginator' => $paginator,
        ));
    }

    /**
     * Active or deactivate a topic.
     * 
     * @return ViewModel 
     */
    public function activeAction()
    {
        $status = $this->params('status', 0);
        $id     = $this->params('id', 0);
        $from   = $this->params('from', 0);
        if (empty($id)) {
            return $this->jumpTo404(_a('Invalid topic ID!'));
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
     * Get all avaliable templates 
     */
    public function getTemplateAction()
    {
        Pi::service('log')->mute();
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
            'message'   => _a('Success'),
            'data'      => $data,
            'previous'  => $page - 1,
            'next'      => $nextPage,
            'count'     => count($data),
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
            return $this->jumpTo404(_a('Invalid template name!'));
        }
        
        $url = $this->getScreenshot($name);
        
        header('Content-type: image/png');
        readfile($url);
        exit();
    }
    
    /**
     * Get topic form object
     * 
     * @param string $action  Form name
     * @return \Module\Article\Form\TopicEditForm 
     */
    protected function getTopicForm($action = 'add')
    {
        $form = new TopicEditForm();
        $form->setAttribute('action', $this->url('', array('action' => $action)));

        return $form;
    }
    
    /**
     * Render form
     * 
     * @param Zend\Form\Form $form     Form instance
     * @param string         $message  Message assign to template
     * @param bool           $error    Whether is error message
     */
    public function renderForm($form, $message = null, $error = true)
    {
        $params = compact('form', 'message', 'error');
        $this->view()->assign($params);
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
        $model  = $this->getModel('topic');

        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
        }
        //$data['active'] = 1;
        
        if (isset($data['slug']) && empty($data['slug'])) {
            unset($data['slug']);
        }

        $model->canonizeColumns($data);
        if (empty($id)) {
            $row = $model->createRow($data);
            $row->save();
            $id  = $row->id;
        } else {
            $row = $model->find($id);

            if (empty($row->id)) {
                return false;
            }

            $row->assign($data);
            $row->save();
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
            Pi::path('upload'),
            $module,
            $name
        );
        if (file_exists($path)) {
            $url = sprintf(
                '%s/%s/topic-template/%s.png',
                Pi::url('upload'),
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
}
