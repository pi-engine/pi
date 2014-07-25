<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-7-17
 * Time: 下午6:24
 */

namespace Module\Test\Controller\Front;


use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Test\Form\BootstrapForm;
use Module\Test\Form\BootstrapFilter;

class FormController extends ActionController{
    public function indexAction()
    {
        $messages = array();
        $form = new BootstrapForm('bootstrap');
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new BootstrapFilter);
           // var_dump($form);exit;
                if ($form->isValid()) {
                    $messages[] = _a('Form submitted successfully.');
                }
                if (!$form->isValid()) {
                    $this->view()->assign('form', $form);
                    return ;
                }
                $data = $form->getData();
                $values = array(
                    'username'  => $data['username'],
                    'content'   => $data['content'],
                );
                $row = $this->getModel('user')->createRow($values);
                $row->save();
                $form = new BootstrapForm('bootstrap');
                if (!$row->id) {
                    return false;
                }
        }
        $this->view()->assign(array(
            'form'      => $form,
            'messages'  => $messages,
        ));

        //$this->view()->setTemplate('form-index');
    }

    protected function checkAccess()
    {
        // If disabled
        $registerDisable = $this->config('login_disable');
        var_dump($registerDisable);exit;
        if ($registerDisable) {
            $this->view()->setTemplate('register-disabled');
            return false;
        }

        if (Pi::service('user')->hasIdentity()) {
            $this->redirect()->toUrl(Pi::service('user')->getUrl('profile'));
            return false;
        }

        return true;
    }
}