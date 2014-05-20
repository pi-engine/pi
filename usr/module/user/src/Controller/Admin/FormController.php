<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\User\Form\RegisterForm;
use Module\User\Form\RegisterFilter;
use Module\User\Form\ProfileCompleteForm;
use Module\User\Form\ProfileCompleteFilter;

/**
 * Form preview controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class FormController extends ActionController
{
    public function indexAction()
    {
        return $this->redirect('', array('action' => 'register'));
    }

    /**
     * Preview register form
     *
     */
    public function registerAction()
    {
        list($fields, $filters) = $this->canonizeForm('custom.register');
        $form = new RegisterForm('register-preview', $fields);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setInputFilter(new RegisterFilter($filters));
            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
            }
        }


        $this->view()->assign(array(
            'form' => $form,
            'data' => $data,
        ));
    }

    /**
     * Preview profile complete form
     */
    public function ProfileCompleteAction()
    {
        list($fields, $filters) = $this->canonizeForm('complete.custom.profile');
        $form = new ProfileCompleteForm('profile-complete', $fields);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setInputFilter(new ProfileCompleteFilter($filters));
            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
            }
        }


        $this->view()->assign(array(
            'form' => $form,
            'data' => $data,
        ));

        $this->view()->setTemplate('form-profile-complete');
    }

    /**
     * Canonize form
     *
     * @param $file
     * @return array
     */
    protected function canonizeForm($file)
    {
        $elements = array();
        $filters  = array();

        $file = strtolower($file);
        $configFile = sprintf(
            '%s/extra/%s/config/%s.php',
            Pi::path('usr'),
            $this->getModule(),
            $file
        );

        if (!file_exists($configFile)) {
            $configFile = sprintf(
                '%s/%s/extra/%s/config/%s.php',
                Pi::path('module'),
                $this->getModule(),
                $this->getModule(),
                $file
            );
            if (!file_exists($configFile)) {
                return;
            }
        }

        $config = include $configFile;

        foreach ($config as $value) {
            if (is_string($value)) {
                $elements[] = Pi::api('form', 'user')->getElement($value);
                $filters[]  = Pi::api('form', 'user')->getFilter($value);
            } else {
                if ($value['element']) {
                    $elements[] = $value['element'];
                }

                if ($value['filter']) {
                    $filters[] = $value['filter'];
                }
            }
        }

        return array($elements, $filters);

    }
}
