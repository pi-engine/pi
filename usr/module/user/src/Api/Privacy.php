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
            case 'owner':
                $privacy = 255;
                break;

        }

        // Get user setting
        $userSetting = $this->getUserPrivacy($uid);
        if ($type == 'group') {
            foreach ($rawData as $group) {
                if ($group['compound']) {
                    $allow = ($privacy >= $userSetting[$group['compound']])
                        ? 1
                        : 0;
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
            $result['id'] = $rawData['id'];
            unset($rawData['id']);
            foreach ($rawData as $key => $value) {
                $allow = $privacy >= $userSetting[$key] ? 1 : 0;
                if ($allow) {
                    $result[$key] = $value;
                }
            }
        }

        return $result;

    }

    /**
     * Get user privacy setting
     *
     * @param $uid
     *
     * @return array
     */
    public function getUserPrivacy($uid)
    {

        $result = array();
        if (!$uid) {
            return $result;
        }

        // User privacy setting
        $userPrivacyFields =  array();
        $rowset     = Pi::model('privacy_user', 'user')
            ->select(array('uid' => $uid));
        foreach ($rowset as $row) {
            $result[$row['field']] = $row['value'];
            $userPrivacyFields[] = $row['field'];
        }

        // Default privacy setting
        $rowset = Pi::model('privacy', 'user')->select(array());
        foreach ($rowset as $row) {
            if (!in_array($row['field'], $userPrivacyFields)) {
                $result[$row['field']] = $row['value'];
            }
        }

        return $result;

    }

    public function getUserPrivacyList($uid)
    {
        $result = array();
        if (!$uid) {
            return $result;
        }

        $this->updateUserPrivacyFields($uid);
        $fieldsMeta = $this->getFieldsMeta();
        $rowset     = Pi::model('privacy_user', 'user')
            ->select(array('uid' => $uid));
        foreach ($rowset as $row) {
            $result[] = array(
                'id'        => (int) $row['id'],
                'field'     => $row['field'],
                'title'     => $fieldsMeta[$row['field']]['title'],
                'value'     => (int) $row['value'],
                'is_forced' => (int) $row['is_forced']
            );
        }

        return $result;
    }


    /**
     * Get system privacy setting
     *
     * @return mixed
     */
    public function getPrivacy()
    {
        $this->updatePrivacyFields();
        // Get fields meta
        $fieldsMeta    = $this->getFieldsMeta();
        // Get current privacy fields
        $privacyModel = Pi::model('privacy', $this->getModule());
        //$userPrivacyModel = Pi::model('privacy_user', $this->getModule());
        $select       = $privacyModel->select()->where(array());
        $rowset       = $privacyModel->selectWith($select)->toArray();
        foreach ($rowset as $row) {
            $privacy[$row['id']] = array(
                'id'        => (int) $row['id'],
                'field'     => $row['field'],
                'title'     => $fieldsMeta[$row['field']]['title'],
                'value'     => (int) $row['value'],
                'is_forced' => (int) $row['is_forced'],
            );
        }

        return $privacy;
    }

    protected function getFieldsMeta()
    {
        $fieldsMeta = array();

        $rowset = Pi::model('field', $this->getModule())
            ->select(array(
                'is_display' => 1,
                'active'     => 1,
            ));
        foreach ($rowset as $row) {
            $fieldsMeta[$row['name']]['title'] = $row['title'];
        }

        return $fieldsMeta;

    }

    /**
     * Update privacy fields according to user system fields
     */
    protected function updatePrivacyFields()
    {
        // Get fields meta
        $fieldsMeta    = $this->getFieldsMeta();
        $currentFields = array_keys($fieldsMeta);

        // Get current privacy fields
        $privacyModel     = Pi::model('privacy', $this->getModule());
        $userPrivacyModel = Pi::model('privacy_user', $this->getModule());
        $select           = $privacyModel->select()->where(array());
        $rowset           = $privacyModel->selectWith($select)->toArray();

        // Update privacy fields
        // Delete invalid privacy fields and user privacy fields setting
        foreach ($rowset as $row) {
            if (!in_array($row['field'], $currentFields)) {
                $userPrivacyModel->delete(array('field' => $row['field']));
                $privacyModel->delete(array('field' => $row['field']));
            } else {
                $validPrivacyFields[] = $row['field'];
            }
        }
        // Insert new fields to privacy
        foreach ($currentFields as $field) {
            if (!in_array($field, $validPrivacyFields)) {
                $row = $privacyModel->createRow(array(
                    'field'    => $field,
                    'value'    => 0,
                    'is_forced' => 0,
                ));
                $row->save();
            }
        }
    }

    /**
     * Update user privacy setting list
     *
     * @param $uid
     */
    protected function updateUserPrivacyFields($uid)
    {
        // Get current user privacy fields
        $userPrivacyModel = Pi::model('privacy_user', $this->getModule());

        $defaultPrivacy = $this->getPrivacy();
        foreach ($defaultPrivacy as $row) {
            $privacyFields[] = $row['field'];
        }

        $curUserPrivacyFields = array();
        $select = $userPrivacyModel->select()->where(array('uid' => $uid));
        $rowset = $userPrivacyModel->selectWith($select)->toArray();
        foreach ($rowset as $row) {
            $curUserPrivacyFields[] = $row['field'];
        }

        foreach ($defaultPrivacy as $row) {
            if (isset($row['field'])
                && $row['field']
                && !in_array($row['field'], $curUserPrivacyFields)
            ) {
                // Insert default privacy field
                if ($row['is_forced']) {
                    $privacyRow = $userPrivacyModel->createRow(array(
                        'uid'       => $uid,
                        'field'     => $row['field'],
                        'value'     => $row['value'],
                        'is_forced' => $row['is_forced']
                    ));
                    $privacyRow->save();
                }
            }
        }
    }
}