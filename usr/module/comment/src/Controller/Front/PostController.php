<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Comment\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Comment\Form\PostForm;
use Module\Comment\Form\PostFilter;

/**
 * Comment post controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PostController extends ActionController
{
    /**
     * Comment post
     *
     * @return string
     */
    public function indexAction()
    {
        $id = _get('id', 'int') ?: 1;
        $post = Pi::api('comment')->getPost($id);
        $target = Pi::api('comment')->getTarget($post['root']);
        $user = Pi::service('user')->get($post['uid'], array('name'));
        $user['url'] =  Pi::service('user')->getUrl('profile', $post['uid']);
        $user['avatar'] = Pi::service('avatar')->get($post['uid']);

        $this->view()->assign(array(
            'post'      => $post,
            'target'    => $target,
            'user'      => $user,
        ));
        $this->view()->setTemplate('comment-view');
    }

    public function editAction()
    {
        $form = Pi::api('comment')->getForm();
    }

    /**
     * Action for comment post submission
     */
    public function submitAction()
    {
        $result = $this->processPost();
        $redirect = '';
        if ($this->request->isPost()) {
            $return = (bool) $this->request->getPost('return');
            if (!$return) {
                $redirect = $this->request->getPost('redirect');
            }
        } else {
            $return = (bool) $this->params('return');
            if (!$return) {
                $redirect = $this->params('redirect');
            }
        }

        if (!$return) {
            $redirect = $redirect
                ? urldecode($redirect)
                : Pi::service('url')->assemble('comment');
            $this->jump($redirect, $result['message']);
        } else {
            return $result;
        }
    }

    /**
     * Process comment post submission
     *
     * @return array
     */
    protected function processPost()
    {
        $id = 0;
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $markup = $data['markup'];
            $form = new PostForm('comment-post', $markup);
            $form->setInputFilter(new PostFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                if (empty($values['id'])) {
                    if (Pi::config('auto_approve', 'comment')) {
                        $values['active'] = 1;
                    }
                    $values['uid'] = Pi::service('user')->getIdentity();
                    $values['ip'] = Pi::service('user')->getIp();
                }
                //vd($values);
                $id = Pi::api('comment')->addPost($values);
                if ($id) {
                    $status = 1;
                    $message = __('Comment post saved successfully.');
                } else {
                    $status = 0;
                    $message = __('Comment post not saved.');
                }
            } else {
                $status = -1;
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $status = -2;
            $message = __('Invalid submission.');
        }

        $result = array(
            'data'      => $id,
            'status'    => $status,
            'message'   => $message,
        );

        return $result;
    }

    /**
     * Approve/disapprove a post
     *
     * @return bool
     */
    public function approveAction()
    {
        $id = _get('id', 'int');
        $flag = _get('flag');
        $return = _get('return');

        if (null === $flag) {
            $status     = Pi::api('comment')->approve($id);
        } else {
            $status = Pi::api('comment')->approve($id, $flag);
        }
        $message = $status
            ? __('Operation succeeded.') : __('Operation failed');

        if (!$return) {
            $this->jump(array('route' => 'comment'), $message);
        } else {
            $result = array(
                'status'    => (int) $status,
                'message'   => $message,
                'data'      => $id,
            );

            return $result;
        }
    }

    /**
     * Delete a comment post
     *
     * @return array
     */
    public function deleteAction()
    {
        $id = _get('id', 'int');
        $return = _get('return');

        $status     = Pi::api('comment')->delete($id);
        $message = $status
            ? __('Operation succeeded.') : __('Operation failed');

        if (!$return) {
            $this->jump(array('route' => 'comment'), $message);
        } else {
            $result = array(
                'status'    => (int) $status,
                'message'   => $message,
                'data'      => $id,
            );

            return $result;
        }
    }
}
