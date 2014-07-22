<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-7-17
 * Time: 上午11:50
 */

namespace Module\Test\Controller\Front;


use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Test\Form\ListForm;


class IndexController extends ActionController
{
    public function indexAction(){
        $this->view()->assign('list','hello world');
        $this->view()->setTemplate('contact');
    }

    public function editAction(){
        $this->view()->setTemplate('edit');
    }
}