<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Expression;

/**
 * User manage cases controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class MaintenanceController extends ActionController
{
    /**
     * Default action
     *
     * @return array|\Zend\Mvc\Controller\Plugin\Redirect
     */
    public function indexAction()
    {
        $this->view()->setTemplate('maintenance-index');
    }

    /**
     * User statics
     *
     * @return array
     */
    public function staticsAction()
    {
        $data = $this->getStaticsData();
        return $data;
    }

    /**
     * User log
     */
    public function logAction()
    {
        $uid = _get('uid');
        if (!$uid) {
            return $this->jumpTo404('Invalid uid');
        }

        // Check user exist
        $isExist = Pi::api('user', 'user')->getUser($uid)->id;
        if (!$isExist) {
            return $this->jumpTo404('Invalid uid');
        }

        // Get user basic information and user data
        $user = Pi::api('user', 'user')->get(
            $uid,
            array(
                'identity',
                'name',
                'id',
                'active',
                'time_disabled',
                'time_activated',
                'time_created',
                'ip_register',
            )
        );

        // Get user data
        $user['time_last_login'] = Pi::user()->data()->get($uid, 'time_last_login');
        $user['ip_login']        = Pi::user()->data()->get($uid, 'ip_login');
        $user['login_times']     = Pi::user()->data()->get($uid, 'login_times');

        $this->view()->assign(array(
            'user' => $user,
        ));
    }

    /**
     * User log list
     */
    public function logListAction()
    {
        /**
         * Sort type:
         * 1. time_register    default
         * 2. time_last_login
         * 3. time_activated
         */
        $sort = _get('sort') ?: 'time_created';

        $page   = (int) $this->params('p', 1);
        $limit  = 10;
        $offset = (int) ($page -1) * $limit;

        $uids  = $this->getUids($sort, $limit, $offset);
        $count = $this->getCount($sort);

        $logs = $this->getUserLogs($uids);

        $paginator = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
        );

        $data = array(
            'logs' => $logs,
            'paginator' => $paginator,
        );

        return $data;

    }

    /**
     * Deleted user list
     */
    public function deletedListAction()
    {
        $page   = (int) $this->params('p', 1);
        $limit  = 10;
        $offset = (int) ($page -1) * $limit;

        $model  = Pi::model('user_account');

        // Get user
        $select = $model->select()->where(array('time_deleted > ?' => 0));
        $select->columns(
            array(
                'identity',
                'name',
                'email',
                'time_activated',
                'time_created',
                'time_deleted',
                'id'
            )
        );

        $select->limit($limit);
        $select->offset($offset);
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $users[] = $row->toArray();
        }

        // Get count
        $select = $model->select()->where(array('time_deleted > ?' => 0));
        $select->columns(array('count' => new Expression('count(*)')));
        $rowset = $model->selectWith($select);
        if ($rowset) {
            $rowset = $rowset->current();
            $count = $rowset['count'];
        } else {
            $count = 0;
        }

        $paginator = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
        );

        $data = array(
            'users'     => $users,
            'paginator' => $paginator,
        );

        return $data;

    }

    /**
     * Clear user
     */
    public function clearAction()
    {
        $type = _post('type') ?: 'all';
        $uid  = _post('uid');

        $model = Pi::model('user_account');
        if ($type == 'all') {
            // Clear all
            try {
                $model->delete(array('time_deleted > ?' => 0));
                $status = true;
            } catch (\Exception $e) {
                $status = false;
            }

        } elseif ($uid) {
            // Clear special uid
            try {
                $model->delete(array('id' => $uid));
                $status = true;
            } catch (\Exception $e) {
                $status = false;
            }
        } else {
           return false;
        }

        return $status;

    }

    /**
     * Get user ids
     *
     * @param $sort
     * @param int $limit
     * @param int $offset
     */
    protected function getUids($sort, $limit = 0, $offset = 0)
    {
        $modelAccount = Pi::model('user_account');
        $modelData    = Pi::model('user_data');

        $result = array();
        // Not need join
        if (in_array($sort, array('time_created', 'time_activated'))) {
            $select = $modelAccount->select()
                ->where(array('time_deleted' => 0));
            $select->columns(array('id'));
            $order = $sort . ' DESC';
            $select->order($order);

            if ($limit) {
                $select->limit($limit);
            }
            if ($offset) {
                $select->offset($offset);
            }

            $rowset = $modelAccount->selectWith($select);

            foreach ($rowset as $row) {
                $result[] = $row['id'];
            }

            return $result;

        }

        $accountWhere = array('time_deleted' => 0);
        $where = Pi::db()->where();
        $where->add($accountWhere);

        $select = Pi::db()->select();
        $select->from(
            array('account' => $modelAccount->getTable()),
            array('id')
        );
        $select->join(
            array('data' => $modelData->getTable()),
            'data.uid=account.id',
            array()
        );

        $whereData = Pi::db()->where()->create(array(
            'data.name' => $sort
        ));

        $where->add($whereData);

        // Sort
        if ($sort == 'time_last_login') {
            $select->order('data.time DESC');
        }

        // Limit and offset
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }

        $select->where($where);
        $rowset = Pi::db()->query($select);

        foreach ($rowset as $row) {
            $result[] = (int) $row['id'];
        }

        return $result;

    }

    protected function getCount($sort)
    {
        $modelAccount = Pi::model('user_account');
        $modelData    = Pi::model('user_data');

        // Not need join
        if (in_array($sort, array('time_created', 'time_activated'))) {
            $select = $modelAccount->select()
                ->where(array('time_deleted' => 0));
            $select->columns(array('count' => new Expression('count(*)')));
            $order = $sort . ' DESC';
            $select->order($order);
            $rowset = $modelAccount->selectWith($select);
            if ($rowset) {
                $rowset = $rowset->current();
                return $rowset['count'];
            } else {
                return 0;
            }

        }

        $accountWhere = array('time_deleted' => 0);

        $where = Pi::db()->where();
        $where->add($accountWhere);

        $select = Pi::db()->select();
        $select->from(
            array('account' => $modelAccount->getTable())
        );

        $select->columns(array(
            'count' => Pi::db()->expression('COUNT(account.id)'),
        ));

        $select->join(
            array('data' => $modelData->getTable()),
            'data.uid=account.id',
            array()
        );

        $whereData = Pi::db()->where()->create(array(
            'data.name' => $sort
        ));
        $where->add($whereData);

        // Sort
        if ($sort == 'time_created') {
            $select->order('account.time_created DESC');
        }
        if ($sort == 'time_activated') {
            $select->order('account.time_activated DESC');
        }
        if ($sort == 'time_last_login') {
            $select->order('data.time DESC');
        }

        $select->where($where);
        $rowset = Pi::db()->query($select);

        if ($rowset) {
            $rowset = $rowset->current();
            return (int) $rowset['count'];
        } else {
            return 0;
        }
    }

    /**
     * Get user log
     *
     * @param $ids
     * @return array
     */
    protected function getUserLogs($uids)
    {
        $logs = array();

        if (!$uids) {
            return $logs;
        }


        // Get user log datat
        $select = Pi::model('user_data')
            ->select()
            ->where(array('uid' => $uids));
        $rowset = Pi::model('user_data')->selectWith($select);

        foreach ($rowset as $row) {
            $userData[$row->uid][$row->name] = $row->toArray();
        }

        foreach ($uids as $uid) {
            $profile = Pi::api('user', 'user')->get(
                $uid,
                array(
                    'identity',
                    'created_time',
                    'ip_register',
                    'time_activate',
                    'id',
                )
            );

            if (isset($userData[$uid]['time_last_login'])) {
                $data['time_last_login'] = $userData[$uid]['time_last_login']['time'];
            }
            if (isset($userData[$uid]['ip_login'])) {
                $data['ip_login'] = $userData[$uid]['ip_login']['value'];
            }
            if (isset($userData[$uid]['login_times'])) {
                $data['login_times'] = $userData[$uid]['login_times']['value_int'];
            }

            $logs[] = array_merge($profile, $data);
            unset($profile);
            unset($data);
        }

        return $logs;

    }

    /**
     * Get statics data
     *
     * @return array
     */
    protected function getStaticsData()
    {
        // Set time
        $today = mktime(
            0,0,0,
            date("m"),
            date("d"),
            date("Y")
        );

        $lastWeek = mktime(
            0,0,0,
            date("m"),
            date("d") - 7,
            date("Y")
        );

        $lastMonth = mktime(
            0,0,0,
            date("m") -1,
            date("d"),
            date("Y")
        );
        $history = 0;

        $getCount = function ($condition) {
            $model = Pi::model('user_account');
            $select = $model->select()->where($condition);
            $select->columns(array('count' => new Expression('count(*)')));
            $rowset = $model->selectWith($select);
            if ($rowset) {
                $rowset = $rowset->current();
                $count  = $rowset['count'];
            } else {
                $count = 0;
            }

            return $count;
        };


        // Get register count
        $register[] = $getCount(array('time_created > ?' => $today));
        $register[] = $getCount(array('time_created > ?' => $lastWeek));
        $register[] = $getCount(array('time_created > ?' => $lastMonth));
        $register[] = $getCount(array('time_created > ?' => $history));

        // Get activated count
        $activated[] = $getCount(array('time_activated > ?' => $today));
        $activated[] = $getCount(array('time_activated > ?' => $lastWeek));
        $activated[] = $getCount(array('time_activated > ?' => $lastMonth));
        $activated[] = $getCount(array('time_activated > ?' => $history));

        // Get pending count
        $pending[] = $getCount(array(
            'time_activated'   => 0,
            'time_created > ?' => $today,
        ));
        $pending[] = $getCount(array(
            'time_activated'   => 0,
            'time_created > ?' => $lastWeek,
        ));
        $pending[] = $getCount(array(
            'time_activated'   => 0,
            'time_created > ?' => $lastMonth,
        ));
        $pending[] = $getCount(array(
            'time_activated'   => 0,
            'time_created > ?' => $history,
        ));

        $ipStatic = function($time) {
            // Get top10 ip
            $modelAccount = Pi::model('user_account');
            $modelProfile = Pi::model('profile', 'user');

            $where = array(
                'account.time_deleted'     => 0,
                'account.time_created > ?' => 0
            );
            $whereAccount = Pi::db()->where()->create($where);
            $where = Pi::db()->where();
            $where->add($whereAccount);

            $select = Pi::db()->select();
            $select->from(
                array('account' => $modelAccount->getTable())
            );

            $select->join(
                array('profile' => $modelProfile->getTable()),
                'profile.uid=account.id',
                array('ip_register')
            );

            $select->columns(array(
                'count' => new Expression('count(profile.ip_register)'),
            ));
            $select->group('profile.ip_register');
            $select->order('count DESC');
            $select->limit(10);

            $select->where($where);
            $rowset = Pi::db()->query($select);
            $result = array();
            foreach ($rowset as $row) {
                $result[] = $row;
            }

            return $result;

        };

        // Get ip static
        $ipStatics[] = $ipStatic($today);
        $ipStatics[] = $ipStatic($lastWeek);
        $ipStatics[] = $ipStatic($lastMonth);

        return array($register, $activated, $pending, $ipStatics);

    }
}