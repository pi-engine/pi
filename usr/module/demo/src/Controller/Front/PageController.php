<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Zend\Db\Sql\Predicate\Expression;

class PageController extends ActionController
{
    /**
     * For page render
     */
    public function indexAction()
    {
        $id = _get('id');

        $row = $this->getModel('page')->find($id);
        $page = $row->toArray();
        $page['module'] = $this->getModule();

        $this->view()->assign('page', $page);
        $this->view()->setTemplate('page-content');
    }
}