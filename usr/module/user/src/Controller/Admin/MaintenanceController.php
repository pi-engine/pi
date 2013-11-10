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
        $this->view()->setTemplate('maintenance');
    }

    /**
     * User statistics
     *
     * @return array
     */
    public function statsAction()
    {
        $data = $this->getStatsData();
        return $data;
    }

    /**
     * User log
     */
    public function logAction()
    {
        $uid = _get('uid');

        // Check user exist
        $isExist = Pi::api('user', 'user')->getUser($uid)->id;
        if (!$isExist) {
            return $this->jumpTo404(_a('User was not found.'));
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

        // Time to string
        $user['time_disabled']  = _date($user['time_disabled']);
        $user['time_activated'] = _date($user['time_activated']);
        $user['time_created']   = _date($user['time_created']);

        // Get user data
        $user['time_last_login'] = Pi::user()->data()->get($uid, 'time_last_login');
        $user['ip_login']        = Pi::user()->data()->get($uid, 'ip_login');
        $user['login_times']     = Pi::user()->data()->get($uid, 'login_times');

        return $user;
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
        $limit  = Pi::service('module')->config('list_limit', 'user');
        $offset = (int) ($page -1) * $limit;

        $uids   = $this->getUids($sort, $limit, $offset);
        $count  = $this->getCount($sort);

        $logs   = $this->getUserLogs($uids);

        $paginator = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
        );

        $data = array(
            'users'      => $logs,
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
        $limit  = Pi::service('module')->config('list_limit', 'user');
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
            $users[] = array(
                'identity'       => $row->identity,
                'name'           => $row->name,
                'email'          => $row->email,
                'time_activated' => _date($row->time_activated),
                'time_created'   => _date($row->time_created),
                'time_deleted'   => _date($row->time_deleted),
            );
        }

        // Get count
        $select = $model->select()->where(array('time_deleted > ?' => 0));
        $select->columns(array('count' => new Expression('count(*)')));
        $select->order('time_deleted DESC');
        $rowset = $model->selectWith($select);
        if ($rowset) {
            $rowset = $rowset->current();
            $count  = $rowset['count'];
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
     * Clear deleted user
     *
     * @return array
     */
    public function clearAction()
    {
        $type   = _post('type') ?: '';
        $uids   = _post('uids');
        $result = array(
            'status' => 0,
            'message' => _a('Clear failed.')
        );

        $model = Pi::model('user_account');

        if ($type == 'all') {
            // Clear all
            try {
                $model->delete(array('time_deleted > ?' => 0));
                $result['status'] = 1;
                $result['message'] = _a('Clear all deleted user successfully.');

            } catch (\Exception $e) {
                return $result;
            }
        }

        if ($uids) {
            // Clear special user
            $uids = array_filter(array_unique(explode(',', $uids)));
            foreach ($uids as $uid) {
                try {
                    $model->delete(array(
                        'id' => $uid,
                        'time_deleted > ?' => 0,
                    ));
                    $result['status'] = 1;
                    $result['message'] = _a('Clear all deleted user successfully.');
                } catch (\Exception $e) {
                    return $result;
                }
            }
        }

        return $result;

    }

    /**
     * Get user ids
     *
     * @param string $sort
     * @param int $limit
     * @param int $offset
     *
     * @return array
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
     * @param array $uids
     *
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
                    'time_created',
                    'ip_register',
                    'time_activated',
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
            if ($data) {
                $logs[] = array_merge($profile, $data);
            } else {
                $logs[] = $profile;
            }

            unset($profile);
            unset($data);
        }

        return $logs;

    }

    /**
     * Get stats data
     *
     * @return array
     */
    protected function getStatsData()
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
            date("m") - 1,
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
        $userStats['register']['today'] = $getCount(
            array('time_created > ?' => $today)
        );
        $userStats['register']['last_week'] = $getCount(
            array('time_created > ?' => $lastWeek)
        );
        $userStats['register']['last_month'] = $getCount(
            array('time_created > ?' => $lastMonth)
        );
        $userStats['register']['history'] = $getCount(
            array('time_created > ?' => $history)
        );

        // Get activated count
        $userStats['activated']['today'] = $getCount(
            array('time_activated > ?' => $today)
        );
        $userStats['activated']['last_week'] = $getCount(
            array('time_activated > ?' => $lastWeek)
        );
        $userStats['activated']['last_month'] = $getCount(
            array('time_activated > ?' => $lastMonth)
        );
        $userStats['activated']['history'] = $getCount(
            array('time_activated > ?' => $history)
        );

        // Get pending count
        $userStats['pending']['today'] = $getCount(array(
            'time_activated'   => 0,
            'time_created > ?' => $today,
        ));
        $userStats['pending']['last_week'] = $getCount(array(
            'time_activated'   => 0,
            'time_created > ?' => $lastWeek,
        ));
        $userStats['pending']['last_month'] = $getCount(array(
            'time_activated'   => 0,
            'time_created > ?' => $lastMonth,
        ));
        $userStats['pending']['history'] = $getCount(array(
            'time_activated'   => 0,
            'time_created > ?' => $history,
        ));

        $ipStats = function($time) {
            // Get top10 ip
            $modelAccount = Pi::model('user_account');
            $modelProfile = Pi::model('profile', 'user');

            $where = array(
                'account.time_deleted'     => 0,
                'account.time_created > ?' => $time,
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

        // Get ip statistics
        $ipStatistics = array();
        $ipStatistics['today']      = $ipStats($today);
        $ipStatistics['last_week']  = $ipStats($lastWeek);
        $ipStatistics['last_month'] = $ipStats($lastMonth);
        $ipStatistics['history']    = $ipStats($history);

        $userStats['ip'] = $ipStatistics;

        return $userStats;

    }
}