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

        if ($type == 'group') {
            foreach ($rawData as $group) {
                if ($group['compound']) {
                    $allow = $this->checkPrivacy(
                        $uid,
                        $group['compound'],
                        $privacy
                    );
                    if ($allow) {
                        $result[] = $group;
                    }
                } else {
                    $data = $group;
                    foreach (array_keys($group['fields'][0]) as $field) {
                        $allow = $this->checkPrivacy(
                            $uid,
                            $field,
                            $privacy
                        );

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
            foreach ($rawData as $key => $value) {
                $allow = $this->checkPrivacy($uid, $key, $privacy);
                if ($allow) {
                    $result[$key] = $value;
                }
            }
            $result['id'] = $rawData['id'];
        }

        return $result;

    }

    /**
     * Check access field privacy
     *
     * @param $uid
     * @param $field
     * @param $privacy
     * @return int
     */
    protected function checkPrivacy($uid, $field, $privacy)
    {
        $model       = Pi::model('privacy_user', 'user');
        $systemModel = Pi::model('privacy', 'user');
        $select = $model->select()->where(
            array(
                'uid'        => $uid,
                'field'      => $field,
            )
        );

        $rowset = $model->selectWith($select)->current();
        if ($rowset) {
            return $rowset['value'] <= $privacy ? 1 : 0;
        } else {
            // System default privacy setting
            $row = $systemModel->find($field, 'field');
            if ($row) {
                return $row['value'] <= $privacy ? 1 : 0;
            } else {
                return 0;
            }
        }
    }
}