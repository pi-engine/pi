<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Sitemap\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Module\Sitemap\Form\TopForm;
use Module\Sitemap\Form\TopFilter;
use Module\Sitemap\Lib\Generate;

class IndexController extends ActionController
{
    protected $listColumns = array(
        'id', 'loc', 'lastmod', 'changefreq', 'priority', 'time_create', 'module', 'table', 'item', 'status', 'top'
    );

    protected $generateColumns = array(
        'id', 'file', 'time_create', 'time_update', 'start', 'end'
    );

    /**
     * Default action
     */
    public function indexAction()
    {
        // Get info
        $select = $this->getModel('generate')->select()->order(array('time_update DESC'));
        $rowset = $this->getModel('generate')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $generate[$row->id] = $row->toArray();
            $generate[$row->id]['time_create'] = _date($generate[$row->id]['time_create']);
            $generate[$row->id]['time_update'] = ($generate[$row->id]['time_update']) ? _date($generate[$row->id]['time_update']) : __('Never');
            $generate[$row->id]['file_url'] = Pi::url($generate[$row->id]['file']);
            $generate[$row->id]['file_path'] = Pi::path($generate[$row->id]['file']);
            $generate[$row->id]['file_exists'] = (Pi::service('file')->exists($generate[$row->id]['file'])) ? 1 : 0;
            $generate[$row->id]['file_main_url'] = Pi::url(sprintf('upload/sitemap/%s', $generate[$row->id]['file']));
            $generate[$row->id]['file_main_path'] = Pi::path(sprintf('upload/sitemap/%s', $generate[$row->id]['file']));
            $generate[$row->id]['file_main_exists'] = (Pi::service('file')->exists(sprintf('upload/sitemap/%s', $generate[$row->id]['file']))) ? 1 : 0;
            $generate[$row->id]['generate_link'] = $this->url('', array(
                'module' =>  'sitemap',
                'action' =>  'generate',
                'file'   =>  $generate[$row->id]['file'],
                'start'  =>  ($generate[$row->id]['start']) ? $generate[$row->id]['start'] : '',
                'end'    =>  ($generate[$row->id]['end']) ? $generate[$row->id]['end'] : '',
            ));
        }
        // Set sitemap.xml if not exist
        if (empty($generate)) {
            $generate[0]['file'] = 'sitemap.xml';
            $generate[0]['time_create'] = _date(time());
            $generate[0]['file_url'] = Pi::url('sitemap.xml');
            $generate[0]['file_path'] = Pi::path('sitemap.xml');
            $generate[0]['file_exists'] = (Pi::service('file')->exists('sitemap.xml')) ? 1 : 0;
            $generate[0]['file_main_url'] = Pi::url('upload/sitemap/sitemap.xml');
            $generate[0]['file_main_path'] = Pi::path('upload/sitemap/sitemap.xml');
            $generate[0]['file_main_exists'] = (Pi::service('file')->exists('upload/sitemap/sitemap.xml')) ? 1 : 0;
            $generate[0]['generate_link'] = $this->url('', array(
                'module' =>  'sitemap',
                'action' =>  'generate',
                'file'   =>  'sitemap.xml'
            ));
        }
        // Set view
        $this->view()->setTemplate('index_index');
        $this->view()->assign('generate', $generate);
    }

    public function generateAction()
    {
        $file = $this->params('file', 'sitemap.xml');
        $start = $this->params('start');
        $end = $this->params('end');
        // Remove old files if exists
        $fileRoot = Pi::path($file);
        $fileMain = Pi::path(sprintf('upload/sitemap/%s', $file));
        // remove fileRoot
        if (Pi::service('file')->exists($fileRoot)) {
            Pi::service('file')->remove($fileRoot);
        }
        // remove fileMain
        if (Pi::service('file')->exists($fileMain)) {
            Pi::service('file')->remove($fileMain);
        }
        // Generat sitemap
        $generate = new Generate($file, $start, $end);
        $sitemap = $this->view()->navigation($generate->content())->sitemap();
        $sitemap = $sitemap->setFormatOutput(true)->render();
        $generate->write($sitemap);
        // Set view
        $this->view()->setTemplate(false);
        $this->jump(array('action' => 'index'), __('New XML file generated'));
    }  

    public function deleteAction()
    {
        $this->view()->setTemplate(false);
        $file = $this->params('file');
        if ($file == 'sitemap.xml') {
            $this->jump(array('action' => 'index'), __('You can not delete sitemap.xml build method')); 
        } else {
            $row = $this->getModel('generate')->find($file, 'file');
            if ($row) {
                $row->delete();
                $this->jump(array('action' => 'index'), __('This sitemap method deleted'));
            } else {
                $this->jump(array('action' => 'index'), __('Please select sitemap method'));   
            }
        }
    }

    public function deletefileAction()
    {
        $this->view()->setTemplate(false);
        $file = $this->params('file');
        if ($file) {
            $fileRoot = Pi::path($file);
            $fileMain = Pi::path(sprintf('upload/sitemap/%s', $file));
            // remove fileRoot
            if (Pi::service('file')->exists($fileRoot)) {
                Pi::service('file')->remove($fileRoot);
            }
            // remove fileMain
            if (Pi::service('file')->exists($fileMain)) {
                Pi::service('file')->remove($fileMain);
            }
            $this->jump(array('action' => 'index'), __('Selected file delete')); 
        } else {
            $this->jump(array('action' => 'index'), __('Please selete file')); 
        }
    } 

    public function copyfileAction()
    {
        $this->view()->setTemplate(false);
        $file = $this->params('file');
        if ($file) {
            $fileRoot = Pi::path($file);
            $fileMain = Pi::path(sprintf('upload/sitemap/%s', $file));
            if (!Pi::service('file')->exists($fileRoot)) {
                Pi::service('file')->copy($fileMain, $fileRoot, true);
                $this->jump(array('action' => 'index'), __('Selected file copy to root'));
            } else {
                $this->jump(array('action' => 'index'), __('Your origin file path is website root')); 
            }
        } else {
            $this->jump(array('action' => 'index'), __('Please selete file')); 
        }
    } 

    /**
     * Top action
     */
    public function topAction()
    {
        // Get info
        $module = $this->params('module');
        $page = $this->params('page', 1);
        // Set info
        $where = array('top' => 1);
        $order = array('id DESC', 'time_create DESC');
        $limit = intval($this->config('admin_perpage'));
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        // Get info
        $select = $this->getModel('url_list')->select()->where($where)->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('url_list')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $link[$row->id] = $row->toArray();
            $link[$row->id]['time_create'] = _date($link[$row->id]['time_create']);
        }
        // Set paginator
        $count = array('count' => new \Zend\Db\Sql\Predicate\Expression('count(*)'));
        $select = $this->getModel('url_list')->select()->columns($count);
        $count = $this->getModel('url_list')->selectWith($select)->current()->count;
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'router'    => $this->getEvent()->getRouter(),
            'route'     => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'    => array_filter(array(
                'module'        => $this->getModule(),
                'controller'    => 'index',
                'action'        => 'top',
            )),
        ));
        // Set view
        $this->view()->setTemplate('index_top');
        $this->view()->assign('links', $link);
        $this->view()->assign('paginator', $paginator);
    }

    /**
     * Top update action
     */
    public function updateAction()
    {
        // Get id
        $id = $this->params('id');
        $module = $this->params('module');
        // Set form
        $form = new TopForm();
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new TopFilter());
            $form->setData($data);
            if ($form->isValid()) {
            	$values = $form->getData();
            	// Set just story fields
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->listColumns)) {
                        unset($values[$key]);
                    }
                }
                // Add / update time 
                $values['time_create'] = time();
                $values['top'] = 1;
                // Save values
                if (!empty($values['id'])) {
                    $row = $this->getModel('url_list')->find($values['id']);
                } else {
                    $row = $this->getModel('url_list')->createRow();
                }
                $row->assign($values);
                $row->save();
                // jump
                $message = __('Link saved successfully.');
                $url = array('action' => 'top');
                $this->jump($url, $message);
            }	
        } else {
            if ($id) {
                $values = $this->getModel('url_list')->find($id)->toArray();
                $form->setData($values);
            }
        }
        // Set view
        $this->view()->setTemplate('index_update');
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Add a link'));
    }

    /**
     * Add to top action
     */
    public function topaddAction()
    {
        // Set view
        $this->view()->setTemplate(false);
        $id = $this->params('id');
        $row = $this->getModel('url_list')->find($id);
        if ($row) {
            $row->top = 1;
            $row->save();
            // jump
            $this->jump(array('action' => 'list'), __('This link add as top link'));
        } else {
            $this->jump(array('action' => 'list'), __('Please select link'));   
        }
    }
    	
    /**
     * List action
     */
    public function listAction()
    {
        // Get info
        $module = $this->params('module');
        $page = $this->params('page', 1);
        $link = array();
        // Set info
        $order = array('id DESC', 'time_create DESC');
        $limit = intval($this->config('admin_perpage'));
        $offset = (int)($page - 1) * $this->config('admin_perpage');
        // Get info
        $select = $this->getModel('url_list')->select()->order($order)->offset($offset)->limit($limit);
        $rowset = $this->getModel('url_list')->selectWith($select);
        // Make list
        foreach ($rowset as $row) {
            $link[$row->id] = $row->toArray();
            $link[$row->id]['time_create'] = _date($link[$row->id]['time_create']);
        }
        // Set paginator
        $count = array('count' => new \Zend\Db\Sql\Predicate\Expression('count(*)'));
        $select = $this->getModel('url_list')->select()->columns($count);
        $count = $this->getModel('url_list')->selectWith($select)->current()->count;
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($this->config('admin_perpage'));
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'router'    => $this->getEvent()->getRouter(),
            'route'     => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'    => array_filter(array(
                'module'        => $this->getModule(),
                'controller'    => 'index',
                'action'        => 'list',
            )),
        ));
        // Set view
        $this->view()->setTemplate('index_list');
        $this->view()->assign('links', $link);
        $this->view()->assign('paginator', $paginator);
    }

    /**
     * delete link action
     */
    public function deleteLinkAction()
    {
        $this->view()->setTemplate(false);
        $id = $this->params('id');
        $row = $this->getModel('url_list')->find($id);
        if ($row) {
        	$row->delete();
            $this->jump(array('action' => 'list'), __('This link deleted'));
        } else {
        	$this->jump(array('action' => 'list'), __('Please select link'));	
        }
    }
}