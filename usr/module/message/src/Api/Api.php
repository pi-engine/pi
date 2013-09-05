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
 *  Pi::user()->message()->send(1, 'message 2=>1', 2);
 *  or
 *  Pi::api('message')->send(1, 'message 2=>1', 2);
 *  // Send notification to a user
 *  Pi::user()->message()
 *            ->notify(1, 'notification to 1', 'subject', 'announcement');
 *  or
 *  Pi::api('message')->notify(
 *      1,
 *      'notification to 1',
 *      'subject',
 *      'announcement'
 *  );
 *  // Get message total count of current user
 *  Pi::user()->message()->getCount(1);
 *  or
 *  Pi::api('message')->getCount(1);
 *  // Get message alert (new) count of current user
 *  Pi::user()->message()->getAlert(1);
 *  or
 *  Pi::api('message')->getAlert(1);
 * ```
 *
 * @author Xingyu Ji <xingyu@eefocus.com>
 */
class Api extends AbstractApi
{
    /**
     * The number of records in each insertion in the loop
     *
     * @var int
     */
    protected static $batchInsertLen = 1000;

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
        $result = true;
        $model  = Pi::model('message', $this->getModule());
        $privateMessage = array(
            'uid_from'   => $from,
            'uid_to'     => $to,
            'is_read_to'    => 0,
            'is_read_from'  => 1,
            'content'    => $message,
            'time_send'  => time(),
        );
        $row = $model->createRow($privateMessage);
        try {
            $row->save();
        } catch (\Exception $e) {
            $result = false;
        }
        if ($result) {
            $names = Pi::user()->get(array($from, $to), 'identity');
            //audit log
            $args = array(
                $names[$from],
                $names[$to],
                $message,
            );
            Pi::service('audit')->log('message', $args);
        }

        // increase message alert
        $this->increaseAlert($to);

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
                $uids = Pi::user()->getUids();
            } elseif (is_array($to)) {
                $uids = $to;
            } else {
                return false;
            }
            if (!empty($uids)) {
                $columns = array(
                    'uid',
                    'subject',
                    'content',
                    'tag',
                    'time_send'
                );
                $values         = array($subject, $message, $tag, time());
                $columnString   = '';
                $valueString    = '%s, ';
                foreach ($columns as $column) {
                    $columnString .= $model->quoteIdentifier($column) . ',';
                }
                foreach ($values as $value) {
                    $valueString .= $model->quoteValue($value) . ',';
                }
                $valueString = '(' . substr($valueString, 0, -1) . '),';
                $sql = 'INSERT INTO '
                     . $model->quoteIdentifier($model->getTable())
                     . ' (' . substr($columnString, 0, -1) . ') VALUES ';
                while (!empty($uids)) {
                    $mySql = $sql;
                    $loop = 0;
                    $passUids = array();
                    foreach ($uids as $key => $uid) {
                        $mySql .= sprintf(
                            $valueString,
                            $model->quoteValue($uid)
                        ) . ',';
                        $passUids[] = $uids[$key];
                        unset($uids[$key]);
                        if (++$loop > static::$batchInsertLen) {
                            break;
                        }
                    }
                    $mySql = substr($mySql, 0, -1);
                    try {
                        Pi::db()->query($mySql);

                        // increase message alert
                        foreach ($passUids as $uid) {
                            $this->increaseAlert($uid);
                        }

                        $result = true;
                    } catch (\Exception $e) {
                        $result = false;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get total count
     *
     * @param int    $uid
     * @param bool   $includeRead   Include read messages
     * @param string $type          Message, notification, all
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
            $model  = Pi::model('notification', $this->getModule());
            $select = $model->select();
            $select->columns(array(
                'count' => Pi::db()->expression('count(*)'),
            ));
            $where = array(
                'uid'           => $uid,
                'is_deleted'    => 0
            );
            if (!$includeRead) {
                $where['is_read'] = 0;
            }
            $select->where($where);
            $row = $model->selectWith($select)->current();
            $count = (int) $row['count'];
        } elseif ('message' == $type) {
            $model  = Pi::model('message', $this->getModule());
            $select = $model->select();
            $select->columns(array(
                'count' => Pi::db()->expression('count(*)'),
            ));
            $whereTo = array(
                'uid_to'        => $uid,
                'is_deleted_to' => 0,
            );
            $whereFrom = array(
                'uid_from'          => $uid,
                'is_deleted_from'   => 0,
            );
            if (!$includeRead) {
                $whereTo['is_read_to']     = 0;
                $whereFrom['is_read_from'] = 0;
            }

            $where = Pi::db()->where();
            $where->addPredicate(Pi::db()->where($whereTo));
            $where->orPredicate(Pi::db()->where($whereFrom));
            $select->where($where);
            $row = $model->selectWith($select)->current();
            $count = (int) $row['count'];
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
        return Pi::user()->data()->get($uid, 'message-alert');
    }

    /**
     * Dismiss message alter by resetting alert count to zero
     *
     * @param  int       $uid
     * @return bool
     */
    public function dismissAlert($uid)
    {
        return Pi::user()->data()->increment($uid, 'message-alert', 0);
    }

    /**
     * Increment message alter
     *
     * @param  int       $uid
     * @return bool
     */
    public function increaseAlert($uid)
    {
        return Pi::user()->data()->increment($uid, 'message-alert', 1);
    }
}
