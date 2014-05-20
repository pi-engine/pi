<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Message\Controller\Front;

use Module\Message\Form\SendForm;
use Module\Message\Form\SendFilter;
use Module\Message\Form\ReplyForm;
use Module\Message\Form\ReplyFilter;
use Module\Message\Service;
use Pi\Paginator\Paginator;
use Pi\Mvc\Controller\ActionController;
use Pi;

/**
 * Private message controller
 *
 * Feature list:
 *
 *  - List of messages
 *  - Show details of a message
 *  - Reply a message
 *  - Send a message
 *  - Mark the messages as read
 *  - Delete one or more messages
 *
 * @author Xingyu Ji <xingyu@eefocus.com>
 */
class IndexController extends ActionController
{
    /**
     * List private messages
     *
     * @return void
     */
    public function indexAction()
    {
        $page = _get('p', 'int');
        $page = $page ?: 1;
        $limit = Pi::config('list_number');
        $offset = (int) ($page - 1) * $limit;

        //current user id
        Pi::service('authentication')->requireLogin();
        $userId = Pi::user()->getId();

        // dismiss alert
        Pi::user()->message->dismissAlert($userId);

        $model = $this->getModel('message');
        //get private message list count
        $select = $model->select()
                        ->columns(array(
                            'count' => new \Zend\Db\Sql\Predicate\Expression(
                                'count(*)'
                            )
                        ))
                        ->where(function($where) use ($userId) {
                            $fromWhere = clone $where;
                            $toWhere = clone $where;
                            $fromWhere->equalTo('uid_from', $userId);
                            $fromWhere->equalTo('is_deleted_from', 0);
                            $toWhere->equalTo('uid_to', $userId);
                            $toWhere->equalTo('is_deleted_to', 0);
                            $where->andPredicate($fromWhere)
                                  ->orPredicate($toWhere);
                        });
        $count = $model->selectWith($select)->current()->count;

        if ($count) {
            //get private message list group by user
            $select = $model->select()
                            ->where(function($where) use ($userId) {
                                $fromWhere = clone $where;
                                $toWhere = clone $where;
                                $fromWhere->equalTo('uid_from', $userId);
                                $fromWhere->equalTo('is_deleted_from', 0);
                                $toWhere->equalTo('uid_to', $userId);
                                $toWhere->equalTo('is_deleted_to', 0);
                                $where->andPredicate($fromWhere)
                                      ->orPredicate($toWhere);
                            })
                            ->order('time_send DESC')
                            ->limit($limit)
                            ->offset($offset);
            $rowset = $model->selectWith($select);
            $messageList = $rowset->toArray();
            //jump to last page
            if (empty($messageList) && $page > 1) {
                $this->redirect()->toRoute('', array(
                    'controller' => 'index',
                    'action'     => 'index',
                    'p'          => ceil($count / $limit),
                ));

                return;
            }

            array_walk($messageList, function (&$v, $k) use ($userId) {
                //format messages
//                $v['content'] = Service::messageSummary($v['content']);

                // markup content
                $v['content'] = Pi::service('markup')->render(
                    $v['content'],
                    'text',
                    false,
                    array('newline' => false)
                );

                if ($userId == $v['uid_from']) {
                    $v['is_read'] = 1;
                    $user = Pi::user()->getUser($v['uid_to'])
                        ?: Pi::user()->getUser(0);
                    // get username url
                    $v['name'] = $user->name;
                    // username link, 4 locations
                    $v['profileUrl'] = Pi::user()->getUrl('profile',
                                                          $v['uid_to']);
                    //get avatar
                    $v['avatar'] = Pi::user()->avatar($v['uid_to'], 'small');
                } else {
                    $v['is_read'] = $v['is_read_to'];
                    $user = Pi::user()->getUser($v['uid_from'])
                        ?: Pi::user()->getUser(0);
                    //get username url
                    $v['name'] = $user->name;
                    $v['profileUrl'] = Pi::user()->getUrl('profile',
                                                          $v['uid_from']);
                    //get avatar
                    $v['avatar'] = Pi::user()->avatar($v['uid_from'], 'small');
                }

                unset(
                    $v['is_read_from'],
                    $v['is_read_to'],
                    $v['delete_status_from'],
                    $v['delete_status_to']
                );
            });

            $paginator = Paginator::factory(intval($count), array(
                'page'          => $page,
                'limit'         => $limit,
                'url_options'   => array(
                    'page_param' => 'p',
                    'params'        => array(
                        'module'        => $this->getModule(),
                        'controller'    => 'index',
                        'action'        => 'index',
                    ),
                ),
            ));

            $this->view()->assign('paginator', $paginator);
            $this->view()->assign('uid', $userId);
        } else {
            $messageList = array();
        }
        $this->renderNav();
        $this->view()->assign('messages', $messageList);

        return;
    }

    /**
     * Render new message count of tab navigation
     *
     * @return void
     */
    protected function renderNav()
    {
        //current user id
        Pi::service('authentication')->requireLogin();
        $userId = Pi::user()->getUser()->id;

        $messageTitle = sprintf(
            __('Private message(%s unread)'),
            Service::getUnread($userId, 'message')
        );
        $notificationTitle = sprintf(
            __('Notification(%s  unread)'),
            Service::getUnread($userId, 'notification')
        );
        $this->view()->assign('messageTitle', $messageTitle);
        $this->view()->assign('notificationTitle', $notificationTitle);
    }

    /**
     * Send a private message
     *
     * @return void
     */
    public function sendAction()
    {
        Pi::service('authentication')->requireLogin();
        $uid = Pi::user()->getId();
        $toUserId = _get('uid');
        $name     = Pi::user()->get($toUserId, 'name');
        $form     = $this->getSendForm('send');
        $form->setData(array('name' => $name));
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new SendFilter);
            if (!$form->isValid()) {
                $this->renderSendForm($form);

                return;
            }
            $data   = $form->getData();
            //check name
            $toUserId = Pi::user()->getUids(array('name' => $data['name']));
            $toUserId = array_shift($toUserId);
            if (!$toUserId) {
                $this->view()->assign(
                    'errMessage',
                    __('Username is invalid, please try again.'
                ));
                $this->renderSendForm($form);

                return;
            }

            //current user id
            $result = Pi::api('api', 'message')->send(
                $toUserId,
                $data['content'],
                $uid
            );
            if (!$result) {
                $this->view()->assign(
                    'errMessage',
                    __('Send failed, please try again.'
                ));
                $this->renderSendForm($form);

                return;
            }

            $this->redirect()->toRoute('', array(
                'controller' => 'index',
                'action'     => 'index'
            ));

            return;
        }
        $this->renderSendForm($form);
    }

    /**
     * Check if username exists
     *
     * @return string json type
     */
    public function checkUsernameAction()
    {
        try {
            $username = _get('username', 'string');
            $user = Pi::user()->getUser($username, 'identity');
            $uid = $user ? $user->id : 0;
            //current user id
            $selfUid = Pi::user()->getUser()->id;
            //check username
            if (!$uid) {
                return array(
                    'status'  => 0,
                    'message' => __('User')
                               . ' '
                               . $username
                               . ' '
                               . __('not found')
                );
            } elseif ($uid == $selfUid) {
                return array(
                    'status'  => 0,
                    'message' => __(
                        __('Sorry, you can\'t send message to yourself.')
                    )
                );
            } else {
                return array(
                    'status'   => 1,
                    'username' => $username
                );
            }
        } catch (Exception $e) {
            return array(
                'status'    => 0,
                'message'   => __('An error occurred, please try again.')
            );
        }
    }

    /**
     * Initialize send form instance
     *
     * @param  string   $name
     * @return SendForm
     */
    protected function getSendForm($name)
    {
        $form = new SendForm($name);
        $form->setAttribute('action', $this->url('', array(
            'action' => 'send'
        )));

        return $form;
    }

    /**
     * Render send form
     *
     * @param  SendForm $form
     * @return void
     */
    protected function renderSendForm($form)
    {
        $this->view()->assign('title', __('Send message'));
        $this->view()->assign('form', $form);
        $this->renderNav();
    }

    /**
     * Message detail and reply message
     *
     * @return void
     */
    public function detailAction()
    {
        Pi::service('authentication')->requireLogin();
        $messageId = _get('mid', 'int');
        $messageId = $messageId ?: 0;
        //current user id
        $userId = Pi::user()->getUser()->id;

        $form = new ReplyForm('reply');
        $form->setAttribute('action', $this->url('', array(
            'action' => 'detail',
            'mid' => $messageId,
        )));
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new ReplyFilter);
            if (!$form->isValid()) {
                $this->view()->assign('form', $form);
                $this->showDetail($messageId);

                return;
            }
            $data = $form->getData();

            $result = Pi::api('api', 'message')->send(
                $data['uid_to'],
                $data['content'],
                $userId
            );
            if (!$result) {
                $this->view()->assign(
                    'errMessage',
                    __('Send failed, please try again.'
                ));
                $this->view()->assign('form', $form);
                $this->showDetail($messageId);

                return;
            }

            $this->redirect()->toRoute('', array(
                'controller' => 'index',
                'action' => 'index'
            ));

            return;
        } else {
            $detail = $this->showDetail($messageId);
            if ($userId == $detail['uid_from']) {
                $toId = $detail['uid_to'];
            } else {
                $toId = $detail['uid_from'];
            }
            $form->setData(array('uid_to' => $toId));
            $this->view()->assign('form', $form);
        }
    }

    /**
     * Show details of a message
     *
     * @param  int   $messageId
     * @return array
     */
    protected function showDetail($messageId)
    {
        Pi::service('authentication')->requireLogin();
        //current user id
        $userId = Pi::user()->getUser()->id;

        // dismiss alert
        Pi::user()->message->dismissAlert($userId);

        $model = $this->getModel('message');
        //get private message
        $select = $model->select()
                        ->where(function($where) use ($messageId, $userId) {
                            $subWhere = clone $where;
                            $subWhere->equalTo('uid_from', $userId);
                            $subWhere->or;
                            $subWhere->equalTo('uid_to', $userId);
                            $where->equalTo('id', $messageId)
                                  ->andPredicate($subWhere);
                        });
        $rowset = $model->selectWith($select)->current();
        if (!$rowset) {
            return;
        }
        $detail = $rowset->toArray();
        //get avatar
        $detail['avatar'] = Pi::user()->avatar($detail['uid_from'], 'small');
        $detail['profileUrl'] = Pi::user()->getUrl(
            'profile',
            $detail['uid_from']
        );

        if ($userId == $detail['uid_from']) {
            //get username url
            $user = Pi::user()->getUser($detail['uid_to'])
                ?: Pi::user()->getUser(0);
            $detail['name'] = $user->name;
        } else {
            //get username url
            $user = Pi::user()->getUser($detail['uid_from'])
                ?: Pi::user()->getUser(0);
            $detail['name'] = $user->name;
        }

        //markup content
        $detail['content'] = Pi::service('markup')->render($detail['content']);

        if (!$detail['is_read_to'] && $userId == $detail['uid_to']) {
            //mark the message as read
            $model->update(array('is_read_to' => 1), array('id' => $messageId));
        }

        $this->view()->assign('message', $detail);
        $this->view()->assign('uid', $userId);
        $this->renderNav();

        return $detail;
    }

    /**
     * Mark the message as read
     *
     * @return void
     */
    public function markAction()
    {
        $messageIds = _get('ids', 'regexp', array('regexp' => '/^[0-9,]+$/'));
        $page = _get('p', 'int');
        $page = $page ?: 1;
        //current user id
        $userId = Pi::user()->getUser()->id;
        if (empty($messageIds)) {
            $this->redirect()->toRoute('', array(
                'controller' => 'index',
                'action'     => 'index',
                'p'          => $page
            ));
        }

        if (strpos($messageIds, ',')) {
            $messageIds = explode(',', $messageIds);
        }

        $model = $this->getModel('message');
        $result = $model->update(array('is_read_to' => 1), array(
            'id'     => $messageIds,
            'uid_to' => $userId
        ));

        $this->redirect()->toRoute('', array(
            'controller' => 'index',
            'action'     => 'index',
            'p'          => $page
        ));
    }

    /**
     * Delete messages
     *
     * @return void
     */
    public function deleteAction()
    {
        $messageIds = _get('ids', 'regexp', array('regexp' => '/^[0-9,]+$/'));
        $toId = _get('tid', 'int');
        $page = _get('p', 'int');
        $page = $page ?: 1;

        if (strpos($messageIds, ',')) {
            $messageIds = explode(',', $messageIds);
        }
        if (empty($messageIds)) {
            $this->redirect()->toRoute('', array(
                'controller' => 'index',
                'action'     => 'index',
                'p'          => $page
            ));
        }
        $userId = Pi::user()->getUser()->id;
        $model = $this->getModel('message');

        if ($toId) {
            if ($userId == $toId) {
                $model->update(array('is_deleted_to' => 1), array(
                    'id'     => $messageIds,
                    'uid_to' => $userId
                ));
            } else {
                $model->update(array('is_deleted_from' => 1), array(
                    'id'       => $messageIds,
                    'uid_from' => $userId
                ));
            }
        } else {
            $model->update(array('is_deleted_from' => 1), array(
                'uid_from' => $userId,
                'id'       => $messageIds
            ));
            $model->update(array('is_deleted_to' => 1), array(
                'uid_to' => $userId,
                'id'     => $messageIds
            ));
        }

        $this->redirect()->toRoute('', array(
            'controller' => 'index',
            'action'     => 'index',
            'p'          => $page
        ));

        return;
    }
    
    /*
     * Archive
     */
    public function archiveAction()
    {
        Pi::service('authentication')->requireLogin();
        $this->renderNav();
    }
}
