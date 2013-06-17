<?php
namespace Module\Demo\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Demo\Form\DemoForm;

class FormController extends ActionController
{
	public function indexAction()
	{
		$form = new DemoForm('demo');
		$message = '';
		if ($this->request->isPost()) {
			$post = $this->request->getPost();
  			$form->setData($post);	
			if ($form->isValid()) {
				$message = __('Demo data saved successfully.');
				d($message);
			} else {
				d($form->getMessages());
			}
		}
		$this->view()->assign(array(
			'form' => $form,
			'message' => $message
		));
	}
}