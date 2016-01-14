<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
namespace Module\Message\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Message\Form\PruneForm;

class PruneController extends ActionController
{
    public function indexAction()
    {
        $form = new PruneForm('prune');
        $message = __('You can prune all old message, select time to remove messages before that time and you can filter list  of removed messages by this from option.');
        if ($this->request->isPost()) {
            // Set form date
            $values = $this->request->getPost();
            // Set prune create
            $where = array('`time_send` < ?' => strtotime($values['date']));
            // Set prune read
            if ($values['read']) {
                $where['is_read_to'] = 1;
            }
            // Set prune deleted
            if ($values['deleted']) {
                $where['is_deleted_to'] = 1;
            }
            // Delete storys
            $number = Pi::model('message', $this->params('module'))->delete($where);
            if ($number) {
                $message = sprintf(__('%s old messages removed'), $number);
            } else {
                $message = __('No messages removed ! check your selected filter and try again');
            }
        }
        $this->view()->setTemplate('prune-index');
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Prune old messages'));
        $this->view()->assign('message', $message);
    }
}