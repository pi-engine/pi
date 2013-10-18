<?php
    /**
     * Pi Engine (http://pialog.org)
     *
     * @link            http://code.pialog.org for the Pi Engine source repository
     * @copyright       Copyright (c) Pi Engine http://pialog.org
     * @license         http://pialog.org/license.txt New BSD License
     */

namespace Module\User\Api;

use Pi;
use Pi\Application\AbstractApi;

/**
 * Privacy APIs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class Privacy extends AbstractApi
{
    /**
     * Filter display information according to privacy setting
     *
     * @param int $uid
     * @param string $role
     * @param array $rawData
     * @param string $type
     *
     * @return array
     */
    public function filterProfile($uid, $role, $rawData, $type)
    {
        $privacy = 0;
        $result  = array();

        switch ($role) {
            case 'public':
                $privacy = 0;
                break;
            case 'member':
                $privacy = 1;
                break;
            case 'follower':
                $privacy = 2;
                break;
            case 'following':
                $privacy = 4;
                break;
        }

        // Get user setting
        $userSetting = $this->getUserPrivacy($uid);
        if ($type == 'group') {
            foreach ($rawData as $group) {
                if ($group['compound']) {
                    $allow = $privacy >= $userSetting[$group['compound']] ? 1 : 0;
                    if ($allow) {
                        $result[] = $group;
                    }
                } else {
                    $data = $group;
                    foreach (array_keys($group['fields'][0]) as $field) {
                        $allow = $privacy >= $userSetting[$field] ? 1 : 0;
                        if (!$allow) {
                            unset($data['fields'][0][$field]);
                        }
                    }
                    if (!empty($data['fields'][0])) {
                        $result[] = $data;
                    }
                    unset($data);
                }
            }
        } elseif ($type == 'user') {
            foreach ($rawData as $key => $value) {d($rawData);
                $allow = $privacy >= $userSetting[$key] ? 1 : 0;
                if ($allow) {
                    $result[$key] = $value;
                }
            }
            $result['id'] = $rawData['id'];
        }

        return $result;

    }

    /**
     * Get user privacy setting
     *
     * @param $uid
     * @param $type
     *
     * @return array
     */
    public function getUserPrivacy($uid, $type = '')
    {

        $result = array();
        if (!$uid) {
            return $result;
        }

        $rowset = Pi::model('privacy_user', 'user')
            ->select(array('uid' => $uid));

        if ($type == 'list') {
            foreach ($rowset as $row) {
                $result[] = array(
                    'id'        => (int) $row['id'],
                    'field'     => $row['field'],
                    'value'     => (int) $row['value'],
                    'is_forced' => (int) $row['is_forced']
                );
            }
        } else {
            foreach ($rowset as $row) {
                $result[$row['field']] = $row['value'];
            }
        }

        return $result;

    }
}