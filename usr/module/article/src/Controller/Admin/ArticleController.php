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
use Module\Article\Controller\Front\ArticleController as FrontArticle;
use Module\Article\Rule;

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
class ArticleController extends FrontArticle
{
    /**
     * Section identifier
     * @var string
     */
    protected $section = 'admin';
    
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
     * Active or deactivate articles
     * 
     * @return ViewModel
     */
    public function activateAction()
    {
        $id     = $this->params('id', '');
        $ids    = array_filter(explode(',', $id));
        $status = $this->params('status', 0);
        $from   = $this->params('from', '');

        if ($ids) {
            $module         = $this->getModule();
            $modelArticle   = $this->getModel('article');
            
            // Activing articles that user has permission to do
            $rules = Rule::getPermission();
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
}
