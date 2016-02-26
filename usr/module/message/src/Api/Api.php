<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Message\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Zend\Db\Sql\Predicate\Expression;

/**
 * Message manipulation API
 *
 * Usage:
 *
 * ```
 *  // Send message to a user
 *  Pi::user()->message()->send(1, 'message 2=>1', 2);
 *  or
 *  Pi::api('api', 'message')->send(1, 'message 2=>1', 2);
 *  // Send notification to a user
 *  Pi::user()->message()
 *            ->notify(1, 'notification to 1', 'subject', 'announcement');
 *  or
 *  Pi::api('api', 'message')->notify(
 *      1,
 *      'notification to 1',
 *      'subject',
 *      'announcement'
 *  );
 *  // Get message total count of current user
 *  Pi::user()->message()->getCount(1);
 *  or
 *  Pi::api('api', 'message')->getCount(1);
 *  // Get message alert (new) count of current user
 *  Pi::user()->message()->getAlert(1);
 *  or
 *  Pi::api('api', 'message')->getAlert(1);
 *  // get Unread
 *  Pi::api('api', 'message')->getUnread($uid, $type);
 *  //
 *  Pi::api('api', 'message')->setConversation();
 *
 * @author Xingyu Ji <xingyu@eefocus.com>
 */
class Api extends AbstractApi
{
    /**
     * Send a message
     *
     * @param  int $to
     * @param  string $message
     * @param  int $from
     * @param  string $conversation
     * @return bool
     */
    public function send($to, $message, $from, $conversation = '')
    {
        $result = true;
        $model = Pi::model('message', $this->getModule());

        $conversation = empty($conversation) ? $this->setConversation() : $conversation;
        $messageData = array(
            'uid_from' => $from,
            'uid_to' => $to,
            'is_read_to' => 0,
            'is_read_from' => 1,
            'content' => $message,
            'time_send' => time(),
            'conversation' => $conversation,
        );
        $row = $model->createRow($messageData);
        try {
            $row->save();
        } catch (\Exception $e) {
            $result = false;
        }
        if ($result) {
            //audit log
            Pi::service('audit')->log('message', array($from, $to));
        }

        // increase message alert
        $this->increaseAlert($to);

        // Send mail
        $this->sendMail($to, $message, $from);

        return $result;
    }

    /**
     * Send a notification
     *
     * @param  int $to
     * @param  string $message
     * @param  string $subject
     * @param  string $tag
     * @return int|bool
     */
    public function notify($to, $message, $subject, $tag = '')
    {
        $message = array(
            'uid' => $to,
            'subject' => $subject,
            'content' => $message,
            'tag' => $tag,
            'time_send' => time(),
        );
        $model = Pi::model('notification', $this->getModule());
        $row = $model->createRow($message);
        $row->save();
        if (!$row->id) {
            return false;
        }

        return $row->id;
    }

    /**
     * Get total count
     *
     * @param int $uid
     * @param bool $includeRead Include read messages
     * @param string $type Message, notification, all
     *
     * @return int
     */
    public function getCount($uid, $includeRead = false, $type = '')
    {
        switch ($type) {
            case 'message':
            case 'notification':
                break;
            default:
                $type = '';
                break;
        }
        if ('notification' == $type) {
            $where = array(
                'uid' => $uid,
                'is_deleted' => 0
            );
            if (!$includeRead) {
                $where['is_read'] = 0;
            }
            $model = Pi::model('notification', $this->getModule());
            /*
            $select = $model->select();
            $select->columns(array(
                'count' => Pi::db()->expression('count(*)'),
            ));
            $select->where($where);
            $row = $model->selectWith($select)->current();
            $count = (int) $row['count'];
            */
        } elseif ('message' == $type) {
            $whereTo = array(
                'uid_to' => $uid,
                'is_deleted_to' => 0,
            );
            $whereFrom = array(
                'uid_from' => $uid,
                'is_deleted_from' => 0,
            );
            if (!$includeRead) {
                $whereTo['is_read_to'] = 0;
                $whereFrom['is_read_from'] = 0;
            }

            $where = Pi::db()->where();
            $where->addPredicate(Pi::db()->where($whereTo));
            $where->orPredicate(Pi::db()->where($whereFrom));
            $model = Pi::model('message', $this->getModule());
            /*
            $select = $model->select();
            $select->columns(array(
                'count' => Pi::db()->expression('count(*)'),
            ));

            $select->where($where);
            $row = $model->selectWith($select)->current();
            $count = (int) $row['count'];
            */
            $count = $model->count($where);
        } else {
            $count = $this->getCount($uid, $includeRead, 'message')
                + $this->getCount($uid, $includeRead, 'notification');
        }

        return $count;
    }

    /**
     * Get new message count to alert
     *
     * Alert user the new message he receives since last visit.
     *
     * @param  int $uid
     * @return int
     */
    public function getAlert($uid)
    {
        return (int)Pi::user()->data()->get($uid, 'message-alert');
    }

    /**
     * Dismiss message alter by resetting alert count to zero
     *
     * @param  int $uid
     * @return bool
     */
    public function dismissAlert($uid)
    {
        return Pi::user()->data()->increment($uid, 'message-alert', 0);
    }

    /**
     * Increment message alter
     *
     * @param  int $uid
     * @return bool
     */
    public function increaseAlert($uid)
    {
        return Pi::user()->data()->increment($uid, 'message-alert', 1);
    }

    /**
     * Send notification as mail
     *
     * @param  int $uid
     * @return bool
     */
    public function sendMail($to, $message, $from)
    {
        if (Pi::service('module')->isActive('notification')) {
            // Get user info
            $user = Pi::user()->get($to, array(
                'id', 'identity', 'name', 'email'
            ));
            // Get sender info
            $sender = Pi::user()->get($from, array(
                'id', 'identity', 'name', 'email'
            ));
            // Set to user
            $toUser = array(
                $user['email'] => $user['name'],
            );
            // Set information
            $information = array(
                'name' => $user['name'],
                'sender' => $sender['name'],
            );
            // Send mail
            Pi::api('mail', 'notification')->send(
                $toUser,
                'notification',
                $information,
                'message'
            );
        }
    }

    /**
     * Message summary
     *
     * @param  string $message
     * @param  int $length
     * @return string
     */
    /* public static function messageSummary($message, $length = 40)
    {
        $encoding = Pi::service('i18n')->getCharset();
        $message = trim($message);

        if ($length && strlen($message) > $length) {
            $wordscut = '';
            if (strtolower($encoding) == 'utf-8') {
                $n = 0;
                $tn = 0;
                $noc = 0;
                while ($n < strlen($message)) {
                    $t = ord($message[$n]);
                    if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                        $tn = 1;
                        $n++;
                        $noc++;
                    } elseif (194 <= $t && $t <= 223) {
                        $tn = 2;
                        $n += 2;
                        $noc += 2;
                    } elseif (224 <= $t && $t < 239) {
                        $tn = 3;
                        $n += 3;
                        $noc += 2;
                    } elseif (240 <= $t && $t <= 247) {
                        $tn = 4;
                        $n += 4;
                        $noc += 2;
                    } elseif (248 <= $t && $t <= 251) {
                        $tn = 5;
                        $n += 5;
                        $noc += 2;
                    } elseif ($t == 252 || $t == 253) {
                        $tn = 6;
                        $n += 6;
                        $noc += 2;
                    } else {
                        $n++;
                    }
                    if ($noc >= $length) {
                        break;
                    }
                }
                if ($noc > $length) {
                    $n -= $tn;
                }
                $wordscut = substr($message, 0, $n);
            } else {
                for ($i = 0; $i < $length - 1; $i++) {
                    if (ord($message[$i]) > 127) {
                        $wordscut .= $message[$i] . $message[$i + 1];
                        $i++;
                    } else {
                        $wordscut .= $message[$i];
                    }
                }
            }
            $message = $wordscut . '...';
        }

        return trim($message);
    } */

    /**
     * Get unread message count
     *
     * @param  int $uid
     * @param  string $type
     * @return int|false
     */
    public static function getUnread($uid, $type = '')
    {
        switch ($type) {
            case 'message':
            case 'notification':
                break;
            default:
                $type = '';
                break;
        }
        if ('notification' == $type) {
            $where = array(
                'uid' => $uid,
                'is_deleted' => 0,
                'is_read' => 0,
            );
            $model = Pi::model('notification', 'message');
            /*
            $select = $model->select();
            $select->columns(array(
                'count' => Pi::db()->expression('count(*)'),
            ));
            $select->where($where);
            $row = $model->selectWith($select)->current();
            $count = (int) $row['count'];
            */
            $count = $model->count($where);
        } elseif ('message' == $type) {
            $where = array(
                'uid_to' => $uid,
                'is_deleted_to' => 0,
                'is_read_to' => 0,
            );
            $model = Pi::model('message', 'message');
            /*
            $select = $model->select();
            $select->columns(array(
                'count' => Pi::db()->expression('count(*)'),
            ));
            $select->where($where);
            $row = $model->selectWith($select)->current();
            $count = (int) $row['count'];
            */
            $count = $model->count($where);
        } else {
            $count = static::getUnread($uid, 'message')
                + static::getUnread($uid, 'notification');
        }

        return $count;
    }

    public function setConversation($time = '')
    {
        $time = !empty($time) ? $time : time();
        return md5($time);
    }

    public function canonizeMessage($message)
    {
        // Set message to array
        $message = $message->toArray();
        //current user id
        $userId = Pi::user()->getUser()->id;
        // Set modle
        $model = Pi::model('message', $this->getModule());
        // Get user
        if ($userId == $message['uid_from']) {
            //get username url
            $user = Pi::user()->getUser($message['uid_to'])
                ?: Pi::user()->getUser(0);
            $message['name'] = $user->name;
        } else {
            //get username url
            $user = Pi::user()->getUser($message['uid_from'])
                ?: Pi::user()->getUser(0);
            $message['name'] = $user->name;
        }

        // Get avatar
        $message['avatar'] = Pi::user()->avatar($message['uid_from'], 'medium', array(
            'alt' => $user->name,
            'class' => 'img-circle',
        ));

        // Set profile Url
        $message['profileUrl'] = Pi::user()->getUrl(
            'profile',
            $message['uid_from']
        );

        // Set content type
        $type = ($this->is_html($message['content'])) ? 'html' : 'text';

        //markup content
        $message['content'] = Pi::service('markup')->render($message['content'], 'html', $type);

        if (!$message['is_read_to'] && $userId == $message['uid_to']) {
            //mark the message as read
            $model->update(array('is_read_to' => 1), array('id' => $message['id']));
        }

        return $message;
    }

    public function is_html($message)
    {
        // return $string != strip_tags($string) ? true:false;
        return preg_match("/<[^<]+>/",$message,$m) != 0;
    }
}
