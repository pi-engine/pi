<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Privacy controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class PrivacyController extends ActionController
{
    /**
     * Display user privacy
     * If not set, display default privacy options
     *
     * @return array|void
     */
    public function indexAction()
    {

        // Redirect login page if not logged in
        $uid = Pi::user()->getIdentity();
        if (!$uid) {
            $this->jump(
                'user',
                array('controller' => 'login', 'action' => 'index'),
                __('Need login'),
                2
            );
        }

        // Get user privacy setting

        $privacy = $this->getPrivacySetting($uid);

        return $privacy;
    }

    public function setPrivacyAction()
    {
        $uid   = _post('uid');
        $field = _post('field');
        $value = _post('value');

        $result = array(
            'status'  => 0,
            'message' => __('Set privacy failed'),
        );

        if (!$uid) {
           return $result;
        }
        if (!in_array($value, array('0', '1', '2', '4', '255'))) {
            return $result;
        }

        $row = $this->getModel('privacy_user')->find($uid, 'uid');
        if (!$row) {
            return $result;
        }

        // Update user privacy setting
        try {
            $this->getModel('privacy_user')->update(
                array(
                    'value' => $value,
                ),
                array(
                    'uid'       => $uid,
                    'field'     => $field,
                    'is_forced' => 1,
                )
            );
            $result['status']  = 1;
            $result['message'] = __('Set privacy successfully');
        } catch (\Exception $e) {
            return $result;
        }

        return $result;

    }

    protected function getPrivacySetting($uid)
    {
        if (!$uid) {
            return;
        }

        $result = array();
        $userPrivacyModel = $this->getModel('privacy_user');
        $privacyModel     = $this->getModel('privacy');

        // Check user setting
        $select = $userPrivacyModel->select()->where(array('uid' => $uid));
        $rowset = $userPrivacyModel->selectWith($select);

        foreach ($rowset as $row) {
            $result[] = $row->toArray();
        }

        if (!empty($result)) {
            return $result;
        }

        // Get default setting
        $select = $privacyModel->select()->where(array());
        $rowset = $privacyModel->selectWith($select);
        foreach ($rowset as $row) {
            $data = array(
                'uid'       => $uid,
                'field'     => $row['field'],
                'value'     => $row['value'],
                'is_forced' => $row['is_forced'],
            );

            $userPrivacyRow = $userPrivacyModel->createRow($data);

            try {
                $userPrivacyRow->save();
                $result[] = array(
                    'id'        => $userPrivacyRow['id'],
                    'uid'       => $userPrivacyRow['uid'],
                    'field'     => $userPrivacyRow['field'],
                    'value'     => $userPrivacyRow['value'],
                    'is_forced' => $userPrivacyRow['is_forced'],
                );
            } catch (\Exception $e) {
                return array();
            }
        }

        return $result;

    }
}