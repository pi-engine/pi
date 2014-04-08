<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */
namespace Module\User\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Build user data
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class BuildController extends ActionController
{
    public function indexAction()
    {
        $this->flush();
        //$this->addPrivacy();
        $this->updateUser();
        $this->addUser();
        $this->addTimeline();
        //$this->addQuickLink();
        //$this->addGroup();
        //$this->addActivity();
        //$this->setFields();

        return $this->jump(array(
            'controller'    => 'index',
            'action'        => 'index'
        ));
    }

    protected function rand($name)
    {
        if ('ip' == $name) {
            $result = long2ip(rand(
                1,
                3221234342
            ));
        } elseif ('year' == $name) {
            $result = rand(1900, 2013);
        } elseif ('telephone' == $name) {
            $result = rand(138, 158) . rand(1000, 5000) . rand(1000, 5000);
        } elseif ('postcode' == $name) {
            $result = rand(100081, 200081);
        }
        else {
            switch ($name) {
                case 'gender':
                    $map    = array('male', 'female', 'unknown');
                    break;

                case 'language':
                    $map    = array('en', 'fa', 'fr', 'zh-cn');
                    break;

                case 'country':
                    $map    = array('China', 'England', 'France', 'Iran');
                    break;

                case 'degree':
                    $map    = array('Ph.D', 'Master', 'Bachelor', 'College',
                        'High school', 'Middle school',
                        'Preliminary school');
                    break;

                case 'role':
                    $map    = array(
                        array('admin'),
                        array('webmaster'),
                        array('webmaster', 'admin'),
                        array('webmaster', 'staff', 'admin'),
                    );
                    break;

                case 'privacy':
                    $map    = array(0, 1, 2, 4, 255);
                    break;

                default:
                    $map = array();
                    break;
            }

            $result = $map[rand(0, count($map) - 1)];
        }

        return $result;
    }

    /**
     * Update system user
     */
    protected function updateUser()
    {
        $prefix         = 'pi';

        for ($i = 1; $i <= 6; $i++) {
            $data = array(
                'fullname'      => ucfirst($prefix) . ' User ' . $i,
                'location'      => 'From ' . $i,
                'signature'     => 'Signature of user ' . $i,
                'bio'           => 'User bio: ' . $i,

                'language'      => $this->rand('language'),
                'demo_sample'   => 'Demo Sample: ' . $i,
                'ip_register'   => $this->rand('ip'),
                'telephone'     => $this->rand('telephone'),
                'address'       => 'Address ' . $i,
                'postcode'      => $this->rand('postcode'),
                'last_modified' => time() - $i * rand(1,100),

                'tool'          => array(
                    array(
                        'title'         => 'Google+',
                        'identifier'    => rand(),
                    ),
                    array(
                        'title'         => 'Twitter',
                        'identifier'    => 'twitter_' . $i,
                    ),
                    array(
                        'title'         => 'QQ',
                        'identifier'    => '88' . $i,
                    ),
                ),

                'education' => array(
                    array(
                        'school'      => 'School 1 ' . $i,
                        'major'       => 'Major 1 ' . $i,
                        'department'  => 'Department 1' . $i,
                        'degree'      => $this->rand('degree'),
                        'description' => 'Description 1' . $i,
                        'class'       => 'Class 1 ' . $i,
                        'start'       => $this->rand('year'),
                        'end'         => $this->rand('year'),
                    ),
                    array(
                        'school'      => 'School 2 ' . $i,
                        'major'       => 'Major 2 ' . $i,
                        'department'  => 'Department 2 ' . $i,
                        'degree'      => $this->rand('degree'),
                        'description' => 'Description 2' . $i,
                        'class'       => 'Class 2 ' . $i,
                        'start'       => $this->rand('year'),
                        'end'         => $this->rand('year'),
                    ),
                    array(
                        'school'      => 'School 3 ' . $i,
                        'major'       => 'Major 3 ' . $i,
                        'department'  => 'Department 3 ' . $i,
                        'degree'      => $this->rand('degree'),
                        'description' => 'Description 3 ' . $i,
                        'class'       => 'Class 3 ' . $i,
                        'start'       => $this->rand('year'),
                        'end'         => $this->rand('year'),
                    ),

                ),

                'work'  => array(
                    array(
                        'company'       => 'Company 1 ' . $i,
                        'department'    => 'Dept 1  ' . $i,
                        'industry'      => 'industry 1 ' . $i,
                        'sector'        => 'sector 1' . $i,
                        'title'         => 'Title 1 ' . $i,
                        'description'   => 'Desc 1 ' . $i,
                        'start'         => $this->rand('year'),
                        'end'           => $this->rand('year'),
                    ),
                    array(
                        'company'       => 'Company 2 ' . $i,
                        'department'    => 'Dept 2 ' . $i,
                        'industry'      => 'industry 2 ' . $i,
                        'sector'        => 'sector 2 ' . $i,
                        'title'         => 'Title 2 ' . $i,
                        'description'   => 'Desc 2 ' . $i,
                        'start'         => $this->rand('year'),
                        'end'           => $this->rand('year'),
                    ),
                    array(
                        'company'       => 'Company 3 ' . $i,
                        'department'    => 'Dept 3 ' . $i,
                        'industry'      => 'industry 3 ' . $i,
                        'sector'        => 'sector 3 ' . $i,
                        'title'         => 'Title 3 ' . $i,
                        'description'   => 'Desc 3 ' . $i,
                        'start'         => $this->rand('year'),
                        'end'           => $this->rand('year'),
                    ),
                ),
            );
            $account = array(
                'gender'        => $this->rand('gender'),
                'birthdate'     => $this->rand('year') . '-'
                    . ($i % 12 + 1) . '-' . ($i % 30 + 1),
            );

            $uid = $i;
            Pi::api('user', 'user')->addProfile($uid, $data);
            Pi::api('user', 'user')->addCompound($uid, $data);
            //Pi::api('user', 'user')->addCustom($uid, $data);
            Pi::api('user', 'user')->updateUser($uid, $account);

            // Add user time log
            $this->addTimelineLog($uid, 50);

            // Add user date
            Pi::user()->data()->set(
                $uid,
                'last_login_ip',
                $this->rand('ip'),
                'user'
            );

            Pi::user()->data()->set(
                $uid,
                'last_login',
                '',
                'user'
            );

            Pi::user()->data()->set(
                $uid,
                'count_login',
                rand(0, 400),
                'user'
            );
        }
    }

    /**
     * Add user
     * Include account, profile, role timeline
     */
    protected function addUser()
    {
        $prefix = 'pi';
        $count  = 50;

        for ($i = 1; $i <= $count; $i++) {
            $user = array(
                'identity'      => $prefix . '_' . $i,
                'credential'    => $prefix . '_' . $i,
                'name'          => ucfirst($prefix) . $i,
                'email'         => $prefix . '_' . $i . '@pialog.org',

                'fullname'      => ucfirst($prefix) . ' User ' . $i,
                'gender'        => $this->rand('gender'),
                'birthdate'     => $this->rand('year') . '-'
                    . ($i % 12 + 1) . '-' . ($i % 30 + 1),
                'location'      => 'From ' . $i,
                'signature'     => 'Signature of user ' . $i,
                'bio'           => 'User bio: ' . $i,

                'language'      => $this->rand('language'),
                'demo_sample'   => 'Demo Sample: ' . $i,
                'ip_register'   => $this->rand('ip'),

                'telephone'     => $this->rand('telephone'),
                'address'       => 'Address ' . $i,
                'postcode'      => $this->rand('postcode'),
                'last_modified' => time() - $i * rand(1,100),

                /*
                'address'       => array(
                    array(
                        'country'   => $this->rand('country'),
                        'province'  => 'Province ' . $i,
                        'city'      => 'City ' . $i,
                        'street'    => 'Street ' . $i,
                        'room'      => 'Room ' . $i,
                        'postcode'  => 'Code ' . $i,
                    ),
                    array(
                        'country'   => $this->rand('country'),
                        'province'  => 'Province ' . $i,
                        'city'      => 'City ' . $i,
                        'street'    => 'Street ' . $i,
                        'room'      => 'Room ' . $i,
                        'postcode'  => 'Code ' . $i,
                    ),
                ),
                */

                'tool'          => array(
                    array(
                        'title'         => 'Google+',
                        'identifier'    => rand(),
                    ),
                    array(
                        'title'         => 'Twitter',
                        'identifier'    => 'twitter_' . $i,
                    ),
                    array(
                        'title'         => 'QQ',
                        'identifier'    => '88' . $i,
                    ),
                ),

                'education' => array(
                    array(
                        'school'      => 'School 1 ' . $i,
                        'major'       => 'Major 1 ' . $i,
                        'department'  => 'Department 1' . $i,
                        'degree'      => $this->rand('degree'),
                        'description' => 'Description 1' . $i,
                        'class'       => 'Class 1 ' . $i,
                        'start'       => $this->rand('year'),
                        'end'         => $this->rand('year'),
                    ),
                    array(
                        'school'      => 'School 2 ' . $i,
                        'major'       => 'Major 2 ' . $i,
                        'department'  => 'Department 2 ' . $i,
                        'degree'      => $this->rand('degree'),
                        'description' => 'Description 2' . $i,
                        'class'       => 'Class 2 ' . $i,
                        'start'       => $this->rand('year'),
                        'end'         => $this->rand('year'),
                    ),
                    array(
                        'school'      => 'School 3 ' . $i,
                        'major'       => 'Major 3 ' . $i,
                        'department'  => 'Department 3 ' . $i,
                        'degree'      => $this->rand('degree'),
                        'description' => 'Description 3 ' . $i,
                        'class'       => 'Class 3 ' . $i,
                        'start'       => $this->rand('year'),
                        'end'         => $this->rand('year'),
                    ),

                ),

                'work'  => array(
                    array(
                        'company'       => 'Company 1 ' . $i,
                        'department'    => 'Dept 1  ' . $i,
                        'industry'      => 'industry 1 ' . $i,
                        'sector'        => 'sector 1' . $i,
                        'title'         => 'Title 1 ' . $i,
                        'description'   => 'Desc 1 ' . $i,
                        'start'         => $this->rand('year'),
                        'end'           => $this->rand('year'),
                    ),
                    array(
                        'company'       => 'Company 2 ' . $i,
                        'department'    => 'Dept 2 ' . $i,
                        'industry'      => 'industry 2 ' . $i,
                        'sector'        => 'sector 2 ' . $i,
                        'title'         => 'Title 2 ' . $i,
                        'description'   => 'Desc 2 ' . $i,
                        'start'         => $this->rand('year'),
                        'end'           => $this->rand('year'),
                    ),
                    array(
                        'company'       => 'Company 3 ' . $i,
                        'department'    => 'Dept 3 ' . $i,
                        'industry'      => 'industry 3 ' . $i,
                        'sector'        => 'sector 3 ' . $i,
                        'title'         => 'Title 3 ' . $i,
                        'description'   => 'Desc 3 ' . $i,
                        'start'         => $this->rand('year'),
                        'end'           => $this->rand('year'),
                    ),
                ),
            );

            $uid = Pi::api('user', 'user')->addUser($user);
            if (!is_int($uid)) {
                continue;
            }
            if ($i > ($count * 2) / 5 && $i < ($count * 3) / 5) {
                Pi::api('user', 'user')->activateUser($uid);
            }

            // Disable user
            if ($i > ($count * 3) / 5 && $i <= ($count * 4) / 5) {
                Pi::api('user', 'user')->disableUser($uid);
            }

            // Add user role
            Pi::api('user', 'user')->setRole($uid, $this->rand('role'));

            // Add user time log
            $this->addTimelineLog($uid, 50);

            // Add user date
            Pi::user()->data()->set(
                $uid,
                'last_login_ip',
                $this->rand('ip'),
                'user'
            );

            Pi::user()->data()->set(
                $uid,
                'last_login',
                '',
                'user'
            );

            Pi::user()->data()->set(
                $uid,
                'count_login',
                rand(0, 400),
                'user'
            );

            // Delete user
            if ($i > ($count * 4) / 5 && $i < ($count * 5) / 5) {
                Pi::api('user', 'user')->deleteUser($uid);
            }
        }
    }


    /**
     * Add user timeline log
     * @param $uid
     * @param $limit
     */

    protected function addTimelineLog($uid, $limit)
    {
        if (!$uid) {
            return;
        }

        $timelineMap = array('update_info', 'write_article',
            'write_blog', 'join_forum'
        );
        $messageMap = array(
            'Update user information',
            'Write a new article',
            'Write a new blog',
            'Join a new forum',
        );

        for ($i = 1; $i < $limit; $i++) {
            $log = array(
                'uid'      => $uid,
                'timeline' => $timelineMap[$i % 4],
                'message'  => $messageMap[$i % 4],
                'time'     => time() - rand(3600, 3600 * 24 * 30),
                'link'     => 'www.' . $timelineMap[$i % 4] . 'com',
            );
            Pi::api('timeline', 'user')->add($log);
        }
    }

    /**
     * Add time line meta
     */
    protected function addTimeline()
    {
        $timelines = array(
            array(
                'name'   => 'update_info',
                'title'  => 'Update user info' ,
                'module' => 'User',
                'icon'   => 'icon-user',
                'active' => 1,
            ),
            array(
                'name'   => 'write_article',
                'title'  => 'Write article' ,
                'module' => 'article',
                'icon'   => 'icon-article',
                'active' => 1,
            ),
            array(
                'name'   => 'write_blog',
                'title'  => 'Write bolg' ,
                'module' => 'blog',
                'icon'   => 'icon-blog',
                'active' => 1,
            ),
            array(
                'name'   => 'join_forum',
                'title'  => 'Join forum' ,
                'module' => 'forum',
                'icon'   => 'icon-forum',
                'active' => 1,
            ),
        );

        $model = $this->getModel('timeline');
        foreach ($timelines as $timeline) {
            $row = $model->createRow($timeline);
            $row->save();
        }
    }

    /**
     * Add quick link
     */
    /*
    protected function addQuickLink()
    {

        $quickLinks = array(
            array(
                'name'    => 'new_blog',
                'title'   => 'New blog',
                'module'  => 'blog',
                'link'    => 'www.blog.com/new',
                'active'  => 1,
                'display' => 1,

            ),
            array(
                'name'    => 'new_article',
                'title'   => 'New article',
                'module'  => 'article',
                'link'    => 'www.articl.com/new',
                'active'  => 1,
                'display' => 1,
            ),
            array(
                'name'    => 'demo_link',
                'title'   => 'Demo quick link',
                'module'  => 'demo',
                'link'    => 'www.demo.com/quicklink',
                'active'  => 1,
                'display' => 1,
            ),
            array(
                'name'    => 'my_message',
                'title'   => 'My message',
                'module'  => 'message',
                'link'    => 'www.message.com/my',
                'active'  => 1,
                'display' => 1,
            ),
            array(
                'name'    => 'google',
                'title'   => 'My message',
                'module'  => 'message',
                'link'    => 'www.google.com',
                'active'  => 1,
                'display' => 1,
            ),
        );

        foreach ($quickLinks as $quickLink) {
            $row = $this->getModel('quicklink')->createRow($quickLink);
            $row->save();
        }
    }
    */


    /**
     * Add privacy
     */
    /*
    protected function addPrivacy()
    {
        $model = $this->getModel('field');
        $select = $model->select()->where(array('active' => 1));
        $rowset = $model->selectWith($select);
        $privacyModel = $this->getModel('privacy');

        foreach ($rowset as $row) {
            if ($row['is_display'] || $row['type'] == 'compound') {
                $fields = array(
                    'field'     => $row->name,
                    //'value'     => $this->rand('privacy'),
                    //'is_forced' => rand(0, 1)
                    'value'     => 0,
                    'is_forced' => 1
                );

                $privacyRow = $privacyModel->createRow($fields);
                $privacyRow->save();
            }
        }
    }
    */

    /**
     * Add group
     */
    /*
    protected function addGroup()
    {
        $groups = array(
            // Base info
            array(
                'group'  => array(
                    'title'    => 'Basic info',
                    'order'    => '1',
                    'compound' => '',
                ),
                'fields' => array(
                    array(
                        'field' => 'fullname',
                        'order' => '1',
                    ),
                    array(
                        'field' => 'gender',
                        'order' => '2',
                    ),
                    array(
                        'field' => 'birthdate',
                        'order' => '3',
                    ),
                ),
            ),

            // Address
            array(
                'group'  => array(
                    'title'    => 'Address',
                    'order'    => '2',
                    'compound' => 'address',
                ),
                'fields' => array(
                    array(
                        'field' => 'country',
                        'order' => '1',
                    ),
                    array(
                        'field' => 'province',
                        'order' => '2',
                    ),
                    array(
                        'field' => 'city',
                        'order' => '3',
                    ),
                    array(
                        'field' => 'street',
                        'order' => '4',
                    ),
                    array(
                        'field' => 'room',
                        'order' => '5',
                    ),
                    array(
                        'field' => 'postcode',
                        'order' => '6',
                    ),
                ),
            ),

            // Work
            array(
                'group'  => array(
                    'title'    => 'Work',
                    'order'    => '3',
                    'compound' => 'work',
                ),
                'fields' => array(
                    array(
                        'field' => 'company',
                        'order' => '1',
                    ),
                    array(
                        'field' => 'department',
                        'order' => '2',
                    ),
                    array(
                        'field' => 'title',
                        'order' => '3',
                    ),
                    array(
                        'field' => 'description',
                        'order' => '4',
                    ),
                    array(
                        'field' => 'start',
                        'order' => '5',
                    ),
                    array(
                        'field' => 'end',
                        'order' => '6',
                    ),
                ),
            ),

            // Education
            array(
                'group'  => array(
                    'title'    => 'Education',
                    'order'    => '4',
                    'compound' => 'education',
                ),
                'fields' => array(
                    array(
                        'field' => 'school',
                        'order' => '1',
                    ),
                    array(
                        'field' => 'major',
                        'order' => '2',
                    ),
                    array(
                        'field' => 'degree',
                        'order' => '3',
                    ),
                    array(
                        'field' => 'class',
                        'order' => '4',
                    ),
                    array(
                        'field' => 'start',
                        'order' => '5',
                    ),
                    array(
                        'field' => 'end',
                        'order' => '6',
                    ),
                ),
            ),
        );

        $groupModel = $this->getModel('display_group');
        $fieldModel = $this->getModel('display_field');
        foreach ($groups as $group) {
            // Add display group
            $groupRow = $groupModel->createRow($group['group']);
            $groupRow->save();
            $groupId = (int) $groupRow['id'];

            // Add display field
            foreach ($group['fields'] as $field) {
                $data = array_merge($field, array('group' => $groupId));
                $fieldRow = $fieldModel->createRow($data);
                $fieldRow->save();
            }
        }
    }
    */

    /**
     * Add activity
     */
    /*
    protected function addActivity()
    {
        $model = $this->getModel('activity');
        for ($i = 1; $i < 10; $i++) {
            $data = array(
                'name'     => 'user_activity' . $i,
                'title'    => 'Activity' . $i,
                'module'   => 'user',
                'active'   => 1,
                'display'  => $i,
                'callback' => 'Module\\User\\ActivityTest',
            );

            $model->createRow($data)->save();
        }
    }
    */


    /**
     * Add default privacy setting for user
     *
     * @param $uid
     */
    /*
    protected function addUserPrivacy($uid)
    {
        $privacyModel     = $this->getModel('privacy');
        $privacyUserModel = $this->getModel('privacy_user');
        $defaultSettings  = $privacyModel->select(array());

        foreach ($defaultSettings as $setting) {
            $data = array(
                'uid'       => $uid,
                'field'     => $setting['field'],
                'value'     => $setting['value'],
                'is_forced' => $setting['is_forced'],
            );
            $row = $privacyUserModel->createRow($data);
            try {
                $row->save();
            } catch (\Exception $e) {
                return;
            }
        }
    }
    */

    /*
    protected function setFields()
    {
        $model = $this->getModel('field');
        $model->update(array('is_edit' => 1, 'is_display' => 1), array('type' => 'compound'));
    }
    */

    /**
     * Clear all user module data
     */
    protected function flush()
    {
        // Flush account
        Pi::model('account', 'user')->delete(array('id > ?' => 6));

        // Flush user profile
        Pi::model('profile', 'user')->delete(array());

        // Flush user compound
        Pi::model('compound', 'user')->delete(array());

        // Flush user data
        Pi::model('user_data')->delete(array('module'  => 'user'));

        // Flush user role
        Pi::model('user_role')->delete(array('uid > ?' => 6));

        // Flush user activity
        //Pi::model('activity', 'user')->delete(array());

        // Flush user compound
        Pi::model('compound', 'user')->delete(array());

        // Flush user display field
        Pi::model('display_field', 'user')->delete(array());

        // Flush user display group
        Pi::model('display_group', 'user')->delete(array());

        // Flush user log
        Pi::model('log', 'user')->delete(array());

        // Flush user privacy
        Pi::model('privacy', 'user')->delete(array());

        // Flush privacy user
        Pi::model('privacy_user', 'user')->delete(array());

        // Flush quicklink
        //Pi::model('quicklink', 'user')->delete(array());

        // Flush timeline
        Pi::model('timeline', 'user')->delete(array());

        // Flush timelog
        Pi::model('timeline_log', 'user')->delete(array());
    }
}