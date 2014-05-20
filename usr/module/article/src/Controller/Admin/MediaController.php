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
use Module\Article\Controller\Front\MediaController as FrontMedia;
use Pi\Paginator\Paginator;
use Module\Article\Form\MediaEditForm;
use Module\Article\Form\MediaEditFilter;
use Module\Article\Form\SimpleSearchForm;
use Module\Article\Media;

/**
 * Media controller
 * 
 * Feature list
 * 
 * 1. List\add\edit\delete media
 * 2. AJAX action for checking upload file
 * 3. AJAX action for saving media
 * 4. AJAX action for removing media
 * 5. AJAX action for search media
 * 6. Download media
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class MediaController extends FrontMedia
{
    const AJAX_RESULT_TRUE  = true;
    const AJAX_RESULT_FALSE = false;
    
    /**
     * Media index page, which will redirect to list page
     * 
     * @return ViewModel
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute('', array('action' => 'list'));
    }
    
    /**
     * Processing media list
     */
    public function listAction()
    {
        $params  = $where = array();
        $type    = $this->params('type', 'image');
        $keyword = $this->params('keyword', '');
        
        $style   = 'default';
        if ('image' == $type) {
            $style = $this->params('style', 'normal');
            $params['style'] = $style;
        }
        
        $where['type'] = $this->getExtension($type);
        $types  = array();
        foreach ($where['type'] as $item) {
            $types[$item] = $item;
        }
        
        $miniType = $this->params('mini_type', '');
        if (!empty($miniType)) {
            $where['type'] = $miniType;
            $params['mini_type'] = $miniType;
        }
        if (!empty($keyword)) {
            $where['title like ?'] = '%' . $keyword . '%';
            $params['keyword'] = $keyword;
        }
        
        $model = $this->getModel('media');

        $page  = $this->params('p', 1);
        $page  = $page > 0 ? $page : 1;

        $module = $this->getModule();
        $config = Pi::config('', $module);
        $limit  = (int) $config['page_limit_all'] ?: 40;
        
        $resultSet = Media::getList($where, $page, $limit, null, null, $module);

        // Total count
        $count  = $model->count($where);

        // Pagination
        $paginator = Paginator::factory($count, array(
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => array(
                'page_param'    => 'p',
                'params'     => array_merge(array(
                    'module'     => $this->getModule(),
                    'controller' => 'media',
                    'action'     => 'list',
                    'type'       => $type,
                    'style'      => $style,
                ), $params),
            ),
        ));

        // Getting search form
        $form = new SimpleSearchForm;

        $this->view()->assign(array(
            'title'         => _a('All Media'),
            'medias'        => $resultSet,
            'paginator'     => $paginator,
            'type'          => $type,
            'keyword'       => $keyword,
            'types'         => $types,
            'form'          => $form,
            'count'         => $count,
            'miniType'      => $miniType,
            'style'         => $style,
        ));
    }
    
    /**
     * Details page to implement media. 
     */
    public function detailAction()
    {
        $id = $this->params('id', 0);
        if (empty($id)) {
            return $this->jumpTo404($this, _a('Invalid ID!'));
        }
        
        $module = $this->getModule();
        $config = Pi::config('', $module);
        
        $media = $this->getModel('media')->find($id);
        
        $type  = '';
        $imageExt = array_map('trim', explode(',', $config['image_format']));
        if (in_array($media->type, $imageExt)) {
            $type = 'image';
            header('Content-type: image/' . $media->type);
            readfile(Pi::url($media->url));
            exit();
        } else {
            $this->view()->assign(array(
                'content' => _a('This page have not been considered yet!'))
            );
            $this->view()->setTemplate(false);
            return ;
        }
        
        $this->view()->assign(array(
            'title'     => _a('Media Detail'),
            'type'      => $type,
            'media'     => $media->toArray(),
        ));
        $this->view()->setTemplate('media-detail-' . $type);
    }
    
    /**
     * Adding media information
     * 
     * @return ViewModel 
     */
    public function addAction()
    {
        $module  = $this->getModule();
        $configs = Pi::config('', $module);
        $configs['max_media_size'] = Pi::service('file')
            ->transformSize($configs['max_media_size']);
        
        $form = $this->getMediaForm('add');
        $form->setData(array('fake_id' => uniqid()));

        $this->view()->assign(array(
            'title'    => _a('Add Media'),
            'form'     => $form,
            'configs'  => $configs,
        ));
        $this->view()->setTemplate('media-edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new MediaEditFilter);
            $columns = array('id', 'name', 'title', 'description', 'url');
            $form->setValidationGroup($columns);
            if (!$form->isValid()) {
                return $this->renderForm(
                    $form,
                    _a('There are some error occured!')
                );
            }
            
            $data = $form->getData();
            $id   = $this->saveMedia($data);
            if (!$id) {
                return $this->renderForm(
                    $form,
                    _a('Can not save data!')
                );
            }
            return $this->redirect()->toRoute('', array('action' => 'list'));
        }
    }

    /**
     * Editing media information.
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
            'title'   => _a('Edit Media Info'),
            'configs' => $configs,
        ));
        
        $form = $this->getMediaForm('edit');
        
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $options = array(
                'id' => $post['id'],
            );
            $form->setInputFilter(new MediaEditFilter($options));
            $columns = array('id', 'name', 'title', 'description', 'url');
            $form->setValidationGroup($columns);
            if (!$form->isValid()) {
                return $this->renderForm(
                    $form,
                    _a('Can not update data!')
                );
            }
            $data = $form->getData();
            $id   = $this->saveMedia($data);

            return $this->redirect()->toRoute('', array('action' => 'list'));
        }
        
        $id = $this->params('id', 0);
        if (empty($id)) {
            $this->jumpto404(_a('Invalid media ID!'));
        }

        $model = $this->getModel('media');
        $row   = $model->find($id);
        if (!$row->id) {
            return $this->jumpTo404(_a('Can not find media!'));
        }
        
        $data = $row->toArray();
        $form->setData($data);

        $this->view()->assign(array(
            'form' => $form,
        ));
    }
    
    /**
     * Deleting a media
     * 
     * @throws \Exception 
     */
    public function deleteAction()
    {
        $from   = $this->params('from', '');
        
        $id     = $this->params('id', 0);
        $ids    = array_filter(explode(',', $id));

        if (empty($ids)) {
            return $this->jumpTo404(_a('Invalid media ID'));
        }
        
        // Checking if media is in used
        $rowAsset = $this->getModel('asset')->select(array('media' => $ids));
        $medias   = array();
        foreach ($rowAsset as $row) {
            $medias[$row->media] = $row->media;
        }
        if (!empty($medias)) {
            return $this->jumpTo404(
                _a('The following medias is in used, and can not be delete: ')
                . implode(', ', $medias)
            );
        }
        
        // Removing media in asset_draft
        $this->getModel('asset_draft')->delete(array('media' => $ids));
        
        // Removing media stats
        $model = $this->getModel('media_stats');
        $model->delete(array('media' => $ids));
        
        // Removing media
        $rowset = $this->getModel('media')->select(array('id' => $ids));
        foreach ($rowset as $row) {
            if ($row->url) {
                unlink(Pi::path($row->url));
            }
        }
        
        $this->getModel('media')->delete(array('id' => $ids));

        // Go to list page or original page
        if ($from) {
            $from = urldecode($from);
            return $this->redirect()->toUrl($from);
        } else {
            return $this->redirect()->toRoute('', array('action' => 'list'));
        }
    }
    
    /**
     * Getting media form object
     * 
     * @param string $action  Form name
     * @return \Module\Article\Form\MediaEditForm 
     */
    protected function getMediaForm($action = 'add')
    {
        $form = new MediaEditForm();
        $form->setAttributes(array(
            'action'  => $this->url('', array('action' => $action)),
        ));

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
     * Get extension of given type
     * 
     * @param string  $type
     * @return array 
     */
    protected function getExtension($type = '')
    {
        if ($type and 
            !in_array($type, array('image', 'doc', 'video', 'zip'))
        ) {
            return array();
        }
        
        $module = $this->getModule();
        $config = Pi::config('', $module);
        
        // Get image
        $images = array_filter(explode(',', $config['image_format']));
        $images = array_map('trim', $images);
        
        // Get doc
        $doc    = array_filter(explode(',', $config['doc_format']));
        $doc    = array_map('trim', $doc);
        
        // Get video
        $video  = array_filter(explode(',', $config['video_format']));
        $video  = array_map('trim', $video);
        
        // Get compression
        $zip    = array_filter(explode(',', $config['zip_format']));
        $zip    = array_map('trim', $zip);
        
        $result = array(
            'image' => $images,
            'doc'   => $doc,
            'video' => $video,
            'zip'   => $zip,
        );
        
        return $type ? $result[$type] : $result;
    }
}
