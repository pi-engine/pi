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
use Module\Article\Form\DraftEditForm;
use Module\Article\Form\DraftEditFilter;
use Module\Article\Model\Draft;
use Module\Article\Model\Article;
use Module\Article\Service;
use Module\Article\Compiled;
use Module\Article\Entity;
use Pi\File\Transfer\Upload as UploadHandler;
use Module\Article\Media;
use Module\Article\Controller\Front\DraftController as FrontDraft;

/**
 * Draft controller
 * 
 * Feature list:
 * 
 * 1. List pending article
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DraftController extends FrontDraft
{
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
        $page   = Service::getParam($this, 'p', 1);
        $limit  = Service::getParam($this, 'limit', 20);

        $where['status']        = $status;
        $where['article < ?']   = 1;
        if ('my' == $from) {
            $where['uid']       = Pi::user()->id;
        }
        if (isset($options['keyword'])) {
            $where['subject like ?'] = sprintf('%%%s%%', $options['keyword']);
        }

        $module         = $this->getModule();
        $modelDraft     = $this->getModel('draft');

        $resultsetDraft = Service::getDraftPage($where, $page, $limit);

        // Total count
        $totalCount = (int) $modelDraft->getSearchRowsCount($where);
        $action     = $this->getEvent()->getRouteMatch()->getParam('action');

        // Paginator
        $paginator = Paginator::factory($totalCount);
        $paginator->setItemCountPerPage($limit)
                  ->setCurrentPageNumber($page)
                  ->setUrlOptions(array(
                    'router'    => $this->getEvent()->getRouter(),
                    'route'     => $this->getEvent()
                        ->getRouteMatch()
                        ->getMatchedRouteName(),
                    'params'    => array(
                        'module'        => $module,
                        'controller'    => 'draft',
                        'action'        => $action,
                        'status'        => $status,
                        'from'          => $from,
                        'where'         => urlencode(json_encode($options)),
                        'limit'         => $limit,
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
     * List articles for management
     */
    public function listAction()
    {
        $status = Service::getParam($this, 'status', Draft::FIELD_STATUS_DRAFT);
        $from   = Service::getParam($this, 'from', 'my');
        $where  = Service::getParam($this, 'where', '');
        $where  = json_decode(urldecode($where), true);
        $where  = array_filter($where);
        if (!in_array($from, array('my', 'all'))) {
            throw new \Exception(__('Invalid source'));
        }
        
        if (Draft::FIELD_STATUS_DRAFT == $status) {
            return $this->redirect()->toRoute(
                'default',
                array(
                    'controller' => 'draft',
                    'action'     => 'list',
                    'status'     => $status,
                )
            );
        }
        
        // Getting permission
        $rules      = Service::getPermission('my' == $from ? true : false);
        $categories = array_keys($rules);
        $where['category'] = empty($categories) ? 0 : $categories;
        
        $this->showDraftPage($status, $from, $where);
        
        $title  = '';
        switch ($status) {
            case Draft::FIELD_STATUS_DRAFT:
                $title = __('Draft');
                $name  = 'draft';
                break;
            case Draft::FIELD_STATUS_PENDING:
                $title = __('Pending');
                $name  = 'pending';
                break;
            case Draft::FIELD_STATUS_REJECTED:
                $title = __('Rejected');
                $name  = 'rejected';
                break;
        }
        $flags = array(
            'draft'     => Draft::FIELD_STATUS_DRAFT,
            'pending'   => Draft::FIELD_STATUS_PENDING,
            'rejected'  => Draft::FIELD_STATUS_REJECTED,
            'published' => \Module\Article\Model\Article::FIELD_STATUS_PUBLISHED,
        );

        $this->view()->assign(array(
            'title'   => $title,
            'summary' => Service::getSummary($from, $rules),
            'flags'   => $flags,
            'rules'   => $rules,
        ));
        
        if ('all' == $from) {
            $template = sprintf('%s-%s', 'article', $name);
            $this->view()->setTemplate($template);
        }
    }
    
    /**
     * Add draft
     *  
     */
    public function addAction()
    {
        parent::addAction();
        $template = sprintf(
            '%s/%s/template/front/draft-edit.phtml',
            Pi::path('module'),
            $this->getModule()
        );
        
        $this->view()->setTemplate($template);
    }
    
    /**
     * Edit draft
     * 
     */
    public function editAction()
    {
        parent::editAction();
        $template = sprintf(
            '%s/%s/template/front/draft-edit.phtml',
            Pi::path('module'),
            $this->getModule()
        );
        
        $this->view()->setTemplate($template);
    }
}
