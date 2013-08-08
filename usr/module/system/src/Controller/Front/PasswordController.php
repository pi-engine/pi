<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\System\Form\PasswordForm;
use Module\System\Form\PasswordFilter;

/**
 * Password controller
 *
 * Feature list:
 *
 * 1. Change passwrod
 * 2. Find password
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PasswordController extends ActionController
{
    /**
     * Change password
     *
     * @return void
     */
    public function indexAction()
    {
        $identity = Pi::service('authentication')->getIdentity();
        // Redirect login page if not logged in
        if (!$identity) {
            $this->redirect()->toRoute('', array('controller' => 'login'));

            return;
        }

        $form = new PasswordForm('password-change');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new PasswordFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $row = Pi::model('user')->find($identity, 'identity');
                $row->credential = $values['credential-new'];
                $row->prepare()->save();
                if ($row->id) {
                    $message = __('Password changed successfully.');
                    $this->redirect()->toRoute(
                        '',
                        array('controller' => 'account', 'action' => 'index')
                    );

                    return;
                } else {
                    $message = __('Password not changed.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $form->setData(array('identity' => $identity));
            $message = '';
        }

        $title = __('Change password');
        $this->view()->assign(array(
            'title' => $title,
            'form'      => $form,
            'message'   => $message,
        ));
        $this->view()->setTemplate('password-change');
    }

    /**
     * Page for finding password
     */
    public function findAction()
    {
        $title = __('Find password');
        $this->view()->assign(array(
            'title' => $title,
        ));
        $this->view()->setTemplate('password-find');
    }
}
