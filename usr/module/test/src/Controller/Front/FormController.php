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
use Pi\File\Transfer\Upload;
use Module\Test\Form\BootstrapForm;

class FormController extends ActionController{
    public function indexAction()
    {
        $messages = array();
        $form = new BootstrapForm('bootstrap');
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
           // var_dump($form);exit;
            if ($form->isValid()) {

                $messages[] = _a('Form submitted successfully.');
                /* $rename = function ($name) {
                     $name = 'test_for_upload_' . $name;
                     return $name;
                 };*/
                 //添加数据
    //             $data = array(
    //                 'username'      => 'John',
    //                 'content'  => 'test' ,);
            }
                $values = $form->getData();
            //var_dump($values);exit;
                $row = $this->getModel('user')->createRow($values);
                $row->save();
                if (!$row->id) {
                    return false;
                }



//            $uploader = new Upload(array('rename' => $rename));
//            $uploader->setExtension('jpg,png,gif,txt,zip,rar');
//            //->setRename('tmp.%random%');
//            //->setImageSize(array('maxWidth' => 600, 'maxHeight' => 500));
//            if ($uploader->isValid()) {
//                $uploader->receive();
//                $file = $uploader->getUploaded('upload_demo');
//                $messages[] = sprintf(_a('File uploaded and saved as %s'), $file);
//            }
        }


        $this->view()->assign(array(
            'form'      => $form,
            'messages'  => $messages,
        ));

        //$this->view()->setTemplate('form-index');
    }
}