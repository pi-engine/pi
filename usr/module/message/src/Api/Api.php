<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Message\Api;

use Pi;
use Pi\Application\AbstractApi;
use Zend\Db\Sql\Predicate\Expression;

/**
 * Message manipulation API
 *
 * Usage:
 *
 * ```
 *  // Send message to a user
 *  Pi::user()->message(1)->send('message 2=>1', 2);
 *  or
 *  Pi::service('api')->message->send(1, 'message 2=>1', 2);
 *  // Send notification to a user
 *  Pi::user()->message(1)
 *            ->notify('notification to 1', 'subject', 'announcement');
 *  or
 *  Pi::service('api')->message->notify(
 *      1,
 *      'notification to 1',
 *      'subject',
 *      'announcement'
 *  );
 *  // Get message total count of current user
 *  Pi::user()->message(1)->getCount();
 *  or
 *  $api = Pi::service('api')->message;
 *  $api->getCount(1, $api::TYPE_MESSAGE);
 *  // Get message alert (new) count of current user
 *  Pi::user()->message(1)->getAlert();
 *  or
 *  $api = Pi::service('api')->message;
 *  $api->getAlert(1, $api::TYPE_MESSAGE);
 * ```
 *
 * @author Xingyu Ji <xingyu@eefocus.com>
 */
class Api extends AbstractApi
{
    /**
     * The number of records each insertion in the loop
     *
     * @var int
     */
    protected static $batchInsertLen = 1000;

    /**
     * Message type: private message
     *
     * @var int
     */
    const TYPE_MESSAGE = 0;

    /**
     * Message type: notification
     *
     * @var int
     */
    const TYPE_NOTIFICATION = 1;

    /**
     * Send a message
     *
     * @param  int    $to
     * @param  string $message
     * @param  int    $from
     * @return bool
     */
    public function send($to, $message, $from)
    {
        if ($to == $from) {
            return false;
        }
        $model  = Pi::model('private_message', $this->getModule());
        $privateMessage = array(
            'uid_from'   => $from,
            'uid_to'     => $to,
            'content'    => $message,
            'time_send'  => time(),
        );
        $row = $model->createRow($privateMessage);
        $result = $row->save();
        if ($result) {
            //audit log
            $args = array(
                'from:' . Pi::user()->getUser($from)->identity,//TODO
                'to:' . Pi::user()->getUser($from)->identity,
                $message,
            );
            Pi::service('audit')->log('message', $args);
        }

        return $result;
    }

    /**
     * Send a notification
     *
     * @param  int|array $to
     * @param  string    $message
     * @param  string    $subject
     * @param  string    $tag
     * @return int|false
     */
    public function notify($to, $message, $subject, $tag = '')
    {
        $model  = Pi::model('notification', $this->getModule());
        if (is_numeric($to)) {
            $message = array(
                'uid'        => $to,
                'subject'    => $subject,
                'content'    => $message,
                'tag'        => $tag,
                'time_send'  => time(),
            );
            $row = $model->createRow($message);
            $row->save();
            if (!$row->id) {
                return false;
            }
        } else {
            if ($to === '*') {
                $uids = Pi::user()->getIds();//TODO
            } elseif (is_array($to)) {
                $uids = $to;
            } else {
                return false;
            }
            if (!empty($uids)) {
                $tableName      = Pi::db()->prefix('notification',
                                                   $this->getModule());
                $columns        = array(
                    'uid', 'subject',
                    'content', 'tag',
                    'time_send'
                );
                $values         = array($subject, $message, $tag, time());
                $columnString   = '';
                $valueString    = ':uid, ';
                foreach ($columns as $column) {
                    $columnString .= $model->quoteIdentifier($column) . ', ';
                }
                foreach ($values as $value) {
                    $valueString .= $model->quoteValue($value) . ', ';
                }
                $columnString = substr($columnString, 0, -2);
                $valueString = substr($valueString, 0, -2);
                $sql = 'INSERT INTO '
                     . $model->quoteIdentifier($tableName)
                     . ' (' . $columnString . ') VALUES ';
                while (!empty($uids)) {
                    $mySql = $sql;
                    $loop = 0;
                    foreach ($uids as $key => $uid) {
                        $myValueString = str_replace(
                            ':uid',
                            $model->quoteValue($uid), $valueString
                        );
                        $mySql .= '(' . $myValueString . '), ';
                        unset($uids[$key]);
                        if (++$loop > static::$batchInsertLen) {
                            break;
                        }
                    }
                    $mySql = substr($mySql, 0, -2);
                    $model->getAdapter()->query(
                        $mySql,
                        \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE
                    );
                }
            }
        }

        return true;
    }

    /**
     * Get total count
     *
     * @param  int       $uid
     * @param  int       $type
     * @return int|false
     */
    public function getCount($uid, $type = self::TYPE_MESSAGE)
    {
        if ($type == self::TYPE_MESSAGE) {
            //get total private message count
            $privateModel  = Pi::model('private_message', $this->getModule());
            $select = $privateModel->select()
                                   ->columns(array(
                                       'count' => new Expression('count(*)')
                                   ))
                                   ->where(function($where) use ($uid) {
                                       $fromWhere = clone $where;
                                       $toWhere = clone $where;
                                       $fromWhere->equalTo('uid_from', $uid);
                                       $fromWhere->equalTo('delete_status_from', 0);
                                       $toWhere->equalTo('uid_to', $uid);
                                       $toWhere->equalTo('delete_status_to', 0);
                                       $where->andPredicate($fromWhere)
                                             ->orPredicate($toWhere);
                                   });
            $count = $privateModel->selectWith($select)->current()->count;
        } else {
            //get total notification count
            $notifyModel  = Pi::model('notification', $this->getModule());
            $select = $notifyModel->select()
                                   ->columns(array(
                                       'count' => new Expression('count(*)')
                                   ))
                                  ->where(array(
                                      'uid' => $uid,
                                      'delete_status' => 0
                                  ));
            $count = $notifyModel->selectWith($select)->current()->count;
        }

        return $count;
    }

    /**
     * Get new message count to alert
     *
     * @param  int       $uid
     * @param  int       $type
     * @return int|false
     */
    public function getAlert($uid, $type = self::TYPE_MESSAGE)
    {
        if ($type == self::TYPE_MESSAGE) {
            //get new private message count
            $privateModel  = Pi::model('private_message', $this->getModule());
            $select = $privateModel->select()
                                   ->columns(array(
                                       'count' => new Expression('count(*)')
                                   ))
                                   ->where(array(
                                       'uid_to' => $uid,
                                       'delete_status_to' => 0,
                                       'is_new_to' => 1
                                   ));
            $count = $privateModel->selectWith($select)->current()->count;
        } else {
            //get new notification count
            $notifyModel  = Pi::model('notification', $this->getModule());
            $select = $notifyModel->select()
                                   ->columns(array(
                                       'count' => new Expression('count(*)')
                                   ))
                                  ->where(array(
                                      'uid' => $uid,
                                      'delete_status' => 0,
                                      'is_new' => 1
                                  ));
            $count = $notifyModel->selectWith($select)->current()->count;
        }

        return $count;
    }
}
