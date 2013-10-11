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
        $this->updateUser();
        $this->addUser();
        $this->addTimeline();
        $this->addQuickLink();
        $this->addPrivacy();
        $this->addGroup();
        $this->addActivity();

        return $this->jump(array('controller' => 'index', 'action' => 'index'));
    }

    /**
     * Update system user
     */
    protected function updateUser()
    {
        $prefix         = 'pi';
        $genderMap      = array('male', 'female', 'unknown');
        $languageMap    = array('en', 'fa', 'fr', 'zh-cn');
        $countryMap     = array('China', 'England', 'France', 'Iran');
        $degreeMap      = array('Ph.D', 'Master', 'Bachelor', 'College',
            'High school', 'Middle school',
            'Preliminary school');

        for ($i = 1; $i <= 6; $i++) {
            $data = array(
                'fullname'      => ucfirst($prefix) . ' User ' . $i,
                'location'      => 'From ' . $i,
                'signature'     => 'Signature of user ' . $i,
                'bio'           => 'User bio: ' . $i,

                'language'      => $languageMap[$i % 4],
                'demo_sample'   => 'Demo Sample: ' . $i,
                'ip_register'   => sprintf('%s.%s.%s.%s', rand(1,255), rand(1,255), rand(1,255), rand(1,255)),
                'address'       => array(
                    array(
                        'country'   => $countryMap[$i % 4],
                        'province'  => 'Province ' . $i,
                        'city'      => 'City ' . $i,
                        'street'    => 'Street ' . $i,
                        'room'      => 'Room ' . $i,
                        'postcode'  => 'Code ' . $i,
                    ),
                    array(
                        'country'   => $countryMap[$i % 4],
                        'province'  => 'Province ' . $i,
                        'city'      => 'City ' . $i,
                        'street'    => 'Street ' . $i,
                        'room'      => 'Room ' . $i,
                        'postcode'  => 'Code ' . $i,
                    ),
                ),

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
                        'school'    => 'School 1 ' . $i,
                        'major'     => 'Major 1 ' . $i,
                        'degree'    => $degreeMap[$i % 7],
                        'class'     => 'Class 1 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),
                    array(
                        'school'    => 'School 2 ' . $i,
                        'major'     => 'Major 2  ' . $i,
                        'degree'    => $degreeMap[$i % 7],
                        'class'     => 'Class 2 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),
                    array(
                        'school'    => 'School 3 ' . $i,
                        'major'     => 'Major 3 ' . $i,
                        'degree'    => $degreeMap[$i % 7],
                        'class'     => 'Class 3 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),

                ),

                'work'  => array(
                    array(
                        'company'       => 'Company 1 ' . $i,
                        'department'    => 'Dept 1  ' . $i,
                        'title'         => 'Title 1 ' . $i,
                        'description'   => 'Desc 1 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),
                    array(
                        'company'       => 'Company 2 ' . $i,
                        'department'    => 'Dept 2  ' . $i,
                        'title'         => 'Title 2 ' . $i,
                        'description'   => 'Desc 2 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),
                ),
            );
            $account = array(
                'gender'        => $genderMap[$i % 3],
                'birthdate'     => (1900 + $i % 100) . '-'
                    . ($i % 12 + 1) . '-' . ($i % 30 + 1),
            );

            $uid = $i;
            Pi::api('user', 'user')->addProfile($uid, $data);
            Pi::api('user', 'user')->addCompound($uid, $data);
            Pi::api('user', 'user')->updateUser($uid, $account);

            // Add user time log
            $this->addTimelineLog($uid, 50);

            // Add user date
            Pi::user()->data()->set(
                $uid,
                $name   = 'ip_login',
                $value  = sprintf('%s.%s.%s.%s', rand(1,255), rand(1,255), rand(1,255), rand(1,255)),
                $module = 'user',
                $time   = null
            );

            Pi::user()->data()->set(
                $uid,
                $name   = 'time_last_login',
                $value  = '',
                $module = 'user',
                $time   = time() - 3600 * $uid
            );

            Pi::user()->data()->set(
                $uid,
                $name   = 'login_times',
                $value  = rand(0, 400),
                $module = 'user',
                $time   = null
            );

            Pi::user()->data()->set(
                $uid,
                'profile-complete',
                1,
                $this->getModule()
            );
        }
    }

    /**
     * Add user
     * Include account, profile, role timeline
     */
    protected function addUser()
    {
        $users  = array();
        $prefix = 'pi';
        $count  = 500;

        $genderMap      = array('male', 'female', 'unknown');
        $languageMap    = array('en', 'fa', 'fr', 'zh-cn');
        $countryMap     = array('China', 'England', 'France', 'Iran');
        $roleMap        = array(
            array('member'),
            array('member', 'webmaster'),
            array('member', 'webmaster', 'guest'),
            array('member', 'admin'),
            array('member', 'webmaster', 'admin'),
            array('member', 'webmaster', 'guest', 'admin'),
        );
        $degreeMap      = array('Ph.D', 'Master', 'Bachelor', 'College',
            'High school', 'Middle school',
            'Preliminary school');

        $start = 1;
        $end  = $count + $start;
        for ($i = $start; $i <= $end; $i++) {
            $user = array(
                'identity'      => $prefix . '_' . $i,
                'credential'    => $prefix . '_' . $i,
                'name'          => ucfirst($prefix) . ' ' . $i,
                'email'         => $prefix . '_' . $i . '@pialog.org',

                'fullname'      => ucfirst($prefix) . ' User ' . $i,
                'gender'        => $genderMap[$i % 3],
                'birthdate'     => (1900 + $i % 100) . '-'
                    . ($i % 12 + 1) . '-' . ($i % 30 + 1),
                'location'      => 'From ' . $i,
                'signature'     => 'Signature of user ' . $i,
                'bio'           => 'User bio: ' . $i,

                'language'      => $languageMap[$i % 4],
                'demo_sample'   => 'Demo Sample: ' . $i,
                'ip_register'   => sprintf('%s.%s.%s.%s', rand(1,255), rand(1,255), rand(1,255), rand(1,255)),

                'address'       => array(
                    array(
                        'country'   => $countryMap[$i % 4],
                        'province'  => 'Province ' . $i,
                        'city'      => 'City ' . $i,
                        'street'    => 'Street ' . $i,
                        'room'      => 'Room ' . $i,
                        'postcode'  => 'Code ' . $i,
                    ),
                    array(
                        'country'   => $countryMap[$i % 4],
                        'province'  => 'Province ' . $i,
                        'city'      => 'City ' . $i,
                        'street'    => 'Street ' . $i,
                        'room'      => 'Room ' . $i,
                        'postcode'  => 'Code ' . $i,
                    ),
                ),

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
                        'school'    => 'School 1 ' . $i,
                        'major'     => 'Major 1 ' . $i,
                        'degree'    => $degreeMap[$i % 7],
                        'class'     => 'Class 1 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),
                    array(
                        'school'    => 'School 2 ' . $i,
                        'major'     => 'Major 2  ' . $i,
                        'degree'    => $degreeMap[$i % 7],
                        'class'     => 'Class 2 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),
                    array(
                        'school'    => 'School 3 ' . $i,
                        'major'     => 'Major 3 ' . $i,
                        'degree'    => $degreeMap[$i % 7],
                        'class'     => 'Class 3 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),

                ),

                'work'  => array(
                    array(
                        'company'       => 'Company 1 ' . $i,
                        'department'    => 'Dept 1  ' . $i,
                        'title'         => 'Title 1 ' . $i,
                        'description'   => 'Desc 1 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),
                    array(
                        'company'       => 'Company 2 ' . $i,
                        'department'    => 'Dept 2  ' . $i,
                        'title'         => 'Title 2 ' . $i,
                        'description'   => 'Desc 2 ' . $i,
                        'start'     => rand(1900, 2013),
                        'end'       => rand(1900, 2013),
                    ),
                ),
            );

            $uid = Pi::api('user', 'user')->addUser($user);
            if ($i > 200 && $i < 300) {
                Pi::api('user', 'user')->activateUser($uid);
            }

            // Disable user
            if ($i > 300 && $i <= 400) {
                Pi::api('user', 'user')->disableUser($uid);
            }

            // Add user role
            Pi::api('user', 'user')->setRole($uid, $roleMap[$i % 6]);

            // Add user time log
            $this->addTimelineLog($uid, 50);

            // Add user date
            Pi::user()->data()->set(
                $uid,
                $name   = 'ip_login',
                $value  = sprintf('%s.%s.%s.%s', rand(1,255), rand(1,255), rand(1,255), rand(1,255)),
                $module = 'user',
                $time   = null
            );

            Pi::user()->data()->set(
                $uid,
                $name   = 'time_last_login',
                $value  = '',
                $module = 'user',
                $time   = time() - 3600 * $uid
            );

            Pi::user()->data()->set(
                $uid,
                $name   = 'login_times',
                $value  = rand(0, 400),
                $module = 'user',
                $time   = null
            );

            Pi::user()->data()->set(
                $uid,
                'profile-complete',
                1,
                $this->getModule()
            );

            // Delete user
            if ($i > 400 && $i < 500) {
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
            Pi::api('user', 'timeline')->add($log);
        }
    }

    /**
     * Add time meta
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

    /**
     * Add privacy
     */
    protected function addPrivacy()
    {
        $model = $this->getModel('field');
        $select = $model->select()->where(array('is_display' => 1));
        $rowset = $model->selectWith($select);
        $privacyMap = array(0, 1, 2, 4, 255);
        $privacyModel = $this->getModel('privacy');

        foreach ($rowset as $row) {
            $index = rand(0, 3);
            $fields = array(
                'field'     => $row->name,
                'value'     => $privacyMap[$index],
                'is_forced' => rand(0, 1)
            );

            $privacyRow = $privacyModel->createRow($fields);
            $privacyRow->save();
        }
    }

    /**
     * Add group
     */
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

    /**
     * Add activity
     */
    protected function addActivity()
    {
        $model = $this->getModel('activity');
        for ($i = 1; $i < 8; $i++) {
            $data = array(
                'name'     => 'user_activity' . $i,
                'title'    => 'Activity' . $i,
                'module'   => 'user',
                'link'     => 'www.google.com',
                'active'   => 1,
                'display'  => $i,
                'callback' => 'Module\\User\\ActivityTest',
            );

            $model->createRow($data)->save();
        }
    }

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
        Pi::model('activity', 'user')->delete(array());

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

        // Flush profile
        Pi::model('profile', 'user')->delete(array());

        // Flush quicklink
        Pi::model('quicklink', 'user')->delete(array());

        // Flush timeline
        Pi::model('timeline', 'user')->delete(array());

        // Flush timelog
        Pi::model('timeline_log', 'user')->delete(array());
    }
}