<?php
    /**
     * Pi Engine (http://pialog.org)
     *
     * @link            http://code.pialog.org for the Pi Engine source repository
     * @copyright       Copyright (c) Pi Engine http://pialog.org
     * @license         http://pialog.org/license.txt BSD 3-Clause License
     */

namespace Module\User\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/**
 * Privacy APIs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class Privacy extends AbstractApi
{
    /** @var array Privacy level map */
    protected $map = array(
        'everyone'  => 0,
        'member'    => 1,
        'follower'  => 2,
        'following' => 4,
        'owner'     => 255,
    );

    /**
     * Transform a privacy value from value to name, or from name to value
     *
     * @param string|int $privacy
     * @param bool $toName
     *
     * @return int|string
     */
    public function transform($privacy, $toName = false)
    {
        $result = null;
        if ($toName) {
            if (is_string($privacy)) {
                if (isset($this->map[$privacy])) {
                    $result = $privacy;
                }
            } else {
                $map = array_flip($this->map);
                if (isset($map[$privacy])) {
                    $result = $map[$privacy];
                }
            }
        } else {
            if (is_string($privacy)) {
                if (isset($this->map[$privacy])) {
                    $result = $this->map[$privacy];
                }
            } else {
                $map = array_flip($this->map);
                if (isset($map[$privacy])) {
                    $result = $privacy;
                }
            }
        }

        return $result;
    }

    /**
     * Get level map
     *
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Get level list of specified levels
     *
     * @param string[] $levels
     * @param bool $indexByValue
     *
     * @return array
     */
    public function getList(array $levels = array(), $indexByValue = false)
    {
        //@FIXME: temporary solution
        $levels = $levels ?: array('everyone', 'member', 'owner');

        $list = array(
            'everyone'  => __('Everyone'),
            'member'    => __('Logged-in user'),
            'follower'  => __('Follower'),
            'following' => __('Followed'),
            'owner'     => __('Owner'),
        );

        $result = array();
        if (!$levels) {
            $result = $list;
        } else {
            foreach ($levels as $level) {
                if (isset($list[$level])) {
                    $result[$level] = $list[$level];
                }
            }
        }
        if ($indexByValue) {
            $map = $this->getMap();
            $tmp = $result;
            $result = array();
            array_walk($tmp, function ($value, $key) use ($map, &$result) {
                $result[$map[$key]] = $value;
            });
        }

        return $result;
    }

    /**
     * Get privacy level
     *
     * @TODO Following/Follower/Friend relationships
     *
     * @param int $targetUid
     * @param int|null $requesterUid
     *
     * @return string
     */
    public function getLevel($targetUid, $requesterUid = null)
    {
        $result = 'everyone';

        if (null === $requesterUid) {
            $requesterUid = Pi::service('user')->getId();
        }

        if (!$requesterUid) {
            $result = 'everyone';
        } elseif ($requesterUid == $targetUid) {
            $result = 'owner';
        } elseif ($requesterUid) {
            $result = 'member';
        }

        return $result;
    }

    /**
     * Filter display information according to privacy setting
     *
     * @param int $uid
     * @param string $level
     * @param array $rawData
     * @param string $type
     *
     * @return array
     */
    public function filterProfile($uid, $level, $rawData, $type)
    {
        $result  = array();
        $privacy = (int) $this->transform($level, true);

        // Get user setting
        $userSetting = $this->getUserPrivacy($uid);
        if ($type == 'group') {
            foreach ($rawData as $group) {
                if ($group['compound']) {
                    if (!isset($userSetting[$group['compound']])
                        || $privacy >= $userSetting[$group['compound']]
                    ) {
                        $result[] = $group;
                    }
                } else {
                    $data = $group;
                    foreach (array_keys($group['fields'][0]) as $field) {
                        if (isset($userSetting[$field])
                            && $privacy < $userSetting[$field]
                        ) {
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
                // Allowed
                if (!isset($userSetting[$key])
                    || $privacy >= $userSetting[$key]
                ) {
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
        $rowset = Pi::model('privacy_user', $this->module)
            ->select(array('uid' => $uid));
        foreach ($rowset as $row) {
            $result[$row['field']] = $row['value'];
            $userPrivacyFields[] = $row['field'];
        }

        // System default privacy setting
        $systemPrivacy = Pi::registry('privacy', $this->module)->read();
        foreach ($systemPrivacy as $field => $data) {
            if ($data['is_forced'] || !isset($result[$field])) {
                $result[$field] = $data['value'];
            }
        }

        return $result;
    }
}